<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

function chatbot_sanitize_question($question, $maxLength)
{
    $question = is_string($question) ? $question : '';
    $question = strip_tags($question);
    $question = preg_replace('/\s+/', ' ', $question);
    $question = trim($question);

    if (function_exists('mb_substr')) {
        return mb_substr($question, 0, $maxLength);
    }

    return substr($question, 0, $maxLength);
}

function chatbot_extract_keywords($question)
{
    $question = strtolower($question);
    $question = preg_replace('/[^a-z0-9]+/i', ' ', $question);
    $parts = preg_split('/\s+/', trim($question));
    $stopWords = [
        'the', 'and', 'for', 'are', 'can', 'you', 'what', 'when', 'where', 'which',
        'with', 'this', 'that', 'from', 'about', 'course', 'courses', 'lesson',
        'lessons', 'lecture', 'lectures', 'topic', 'topics', 'please', 'tell',
        'learn', 'does', 'have', 'has', 'will', 'how', 'why', 'into', 'your',
    ];

    $keywords = [];
    foreach ($parts as $part) {
        if (strlen($part) < 3 || in_array($part, $stopWords, true)) {
            continue;
        }

        $keywords[$part] = true;
        if (count($keywords) >= 8) {
            break;
        }
    }

    return array_keys($keywords);
}

function chatbot_fetch_relevant_course_rows($conn, $question, $maxRows, $courseId = null)
{
    $keywords = chatbot_extract_keywords($question);

    if (empty($keywords)) {
        return chatbot_fetch_course_overview_rows($conn, $maxRows, $courseId);
    }

    if (in_array('free', $keywords, true)) {
        $keywords[] = '0';
    }

    $columns = [
        'c.title',
        'c.short_desc',
        'c.description',
        'c.duration',
        'c.price',
        'c.language',
        'c.status',
        'c.upcoming',
        'u.first_name',
        'u.last_name',
        'l.title',
        't.title',
        't.duration',
        't.price',
    ];

    $clauses = [];
    $params = [];

    foreach ($keywords as $keyword) {
        foreach ($columns as $column) {
            $clauses[] = $column . ' LIKE ?';
            $params[] = '%' . $keyword . '%';
        }
    }

    $sql = "
        SELECT
            c.id AS course_id,
            c.title AS course_title,
            c.short_desc,
            c.description,
            c.duration,
            c.price,
            c.total_lectures,
            c.language,
            c.status,
            c.upcoming,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) AS instructor_name,
            l.id AS lecture_id,
            l.title AS lecture_title,
            t.id AS topic_id,
            t.title AS topic_title,
            t.duration AS topic_duration,
            t.price AS topic_price
        FROM courses c
        LEFT JOIN users u ON c.instructor_id = u.id
        LEFT JOIN lectures l ON l.course_id = c.id
        LEFT JOIN topics t ON t.lecture_id = l.id
        WHERE (" . implode(' OR ', $clauses) . ")
    ";

    if ($courseId) {
        $sql .= " AND c.id = ?";
        $params[] = $courseId;
    }

    $sql .= " ORDER BY c.updated_at DESC, c.id DESC, l.id ASC, t.id ASC LIMIT ?";

    $params[] = $maxRows;
    $types = str_repeat('s', count($params) - 1 - ($courseId ? 1 : 0)) . ($courseId ? 'i' : '') . 'i';

    return chatbot_run_course_query($conn, $sql, $types, $params);
}

function chatbot_fetch_course_overview_rows($conn, $maxRows, $courseId = null)
{
    $sql = "
        SELECT
            c.id AS course_id,
            c.title AS course_title,
            c.short_desc,
            c.description,
            c.duration,
            c.price,
            c.total_lectures,
            c.language,
            c.status,
            c.upcoming,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) AS instructor_name,
            l.id AS lecture_id,
            l.title AS lecture_title,
            t.id AS topic_id,
            t.title AS topic_title,
            t.duration AS topic_duration,
            t.price AS topic_price
        FROM courses c
        LEFT JOIN users u ON c.instructor_id = u.id
        LEFT JOIN lectures l ON l.course_id = c.id
        LEFT JOIN topics t ON t.lecture_id = l.id
    ";

    $params = [];

    if ($courseId) {
        $sql .= " WHERE c.id = ?";
        $params[] = $courseId;
    }

    $sql .= " ORDER BY c.updated_at DESC, c.id DESC, l.id ASC, t.id ASC LIMIT ?";
    $params[] = $maxRows;

    $types = ($courseId ? 'i' : '') . 'i';

    return chatbot_run_course_query($conn, $sql, $types, $params);
}

function chatbot_run_course_query($conn, $sql, $types, $params)
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Could not prepare course content query.');
    }

    $bindValues = [$types];
    foreach ($params as $index => $value) {
        $bindValues[] = &$params[$index];
    }

    call_user_func_array([$stmt, 'bind_param'], $bindValues);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

function chatbot_build_context($rows, $maxChars)
{
    if (empty($rows)) {
        return '';
    }

    $courses = [];

    foreach ($rows as $row) {
        $courseId = (int) $row['course_id'];
        if (!isset($courses[$courseId])) {
            $courses[$courseId] = [
                'title' => $row['course_title'],
                'short_desc' => $row['short_desc'],
                'description' => $row['description'],
                'duration' => $row['duration'],
                'price' => $row['price'],
                'total_lectures' => $row['total_lectures'],
                'language' => $row['language'],
                'status' => $row['status'],
                'upcoming' => $row['upcoming'],
                'instructor' => trim($row['instructor_name']),
                'lectures' => [],
            ];
        }

        if (!empty($row['lecture_id'])) {
            $lectureId = (int) $row['lecture_id'];
            if (!isset($courses[$courseId]['lectures'][$lectureId])) {
                $courses[$courseId]['lectures'][$lectureId] = [
                    'title' => $row['lecture_title'],
                    'topics' => [],
                ];
            }

            if (!empty($row['topic_id'])) {
                $courses[$courseId]['lectures'][$lectureId]['topics'][] = [
                    'title' => $row['topic_title'],
                    'duration' => $row['topic_duration'],
                    'price' => $row['topic_price'],
                ];
            }
        }
    }

    $lines = [];
    foreach ($courses as $course) {
        $lines[] = 'Course: ' . $course['title'];
        $lines[] = 'Summary: ' . $course['short_desc'];
        $lines[] = 'Description: ' . $course['description'];
        $lines[] = 'Duration: ' . $course['duration'] . '; Language: ' . $course['language'] . '; Price: ' . $course['price'];
        $lines[] = 'Total lectures: ' . $course['total_lectures'] . '; Status: ' . $course['status'] . '; Availability: ' . $course['upcoming'];
        if ($course['instructor'] !== '') {
            $lines[] = 'Instructor: ' . $course['instructor'];
        }

        foreach ($course['lectures'] as $lecture) {
            $lines[] = 'Lecture: ' . $lecture['title'];
            foreach ($lecture['topics'] as $topic) {
                $lines[] = 'Topic: ' . $topic['title'] . ' (' . $topic['duration'] . ', ' . $topic['price'] . ')';
            }
        }

        $lines[] = '';
    }

    $context = trim(implode("\n", $lines));
    if (function_exists('mb_substr')) {
        return mb_substr($context, 0, $maxChars);
    }

    return substr($context, 0, $maxChars);
}
