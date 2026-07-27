<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

/**
 * Sanitize and truncate a user question for safe use in queries.
 * Strips HTML tags, normalizes whitespace, and respects encoding for truncation.
 */
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

/**
 * Extract meaningful keywords from the question by filtering out stop words.
 * Returns up to 8 unique keywords for database matching.
 */
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

/**
 * Fetch a lightweight summary list of all active courses in the database.
 */
function chatbot_fetch_all_courses_summary($conn)
{
    $result = $conn->query("SELECT id, title, price, duration, status FROM courses ORDER BY id ASC");
    if (!$result) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Check if the user's question asks for general catalog/platform information.
 */
function chatbot_is_general_catalog_query($question)
{
    $q = strtolower($question);
    $patterns = [
        'how many',
        'all course',
        'all courses',
        'total course',
        'total courses',
        'list course',
        'list courses',
        'available course',
        'available courses',
        'courses available',
        'courses on website',
        'courses in website',
        'what courses',
        'which courses',
        'catalog',
        'how many course',
        'how many courses',
    ];

    foreach ($patterns as $pattern) {
        if (strpos($q, $pattern) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Fetch course rows matching the user's question keywords from the database.
 * Falls back to a general overview if no keyword matches are found.
 */
function chatbot_fetch_relevant_course_rows($conn, $question, $maxRows, $courseId = null)
{
    if ($courseId && chatbot_is_general_catalog_query($question)) {
        $courseId = null;
    }

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

    $rows = chatbot_run_course_query($conn, $sql, $types, $params);

    // Keywords like "list" or "details" don't match any column but still describe
    // a real request, so fall back to a general overview instead of an empty answer.
    if (empty($rows)) {
        return chatbot_fetch_course_overview_rows($conn, $maxRows, $courseId);
    }

    return $rows;
}

/**
 * Fetch a general course overview ordered by most recently updated.
 * Used as a fallback when no specific keyword matches are found.
 */
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

/**
 * Execute a prepared SELECT query with dynamic parameter binding.
 * Returns the result set as an associative array.
 */
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

/**
 * Fetch the logged-in student's recent enrollments/purchases (up to 10).
 * Returns empty array for guests or on query failure.
 */
function chatbot_fetch_student_enrollments($conn, $userId)
{
    if (!$userId) {
        return [];
    }

    $stmt = $conn->prepare("
        SELECT c.title AS course_title, e.status, e.created_at
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.user_id = ?
        ORDER BY e.created_at DESC
        LIMIT 10
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

/**
 * Build a plain-text context string from course rows and student data.
 * Includes student account status, enrollments, and structured course details
 * (title, lectures, topics) truncated to maxChars.
 */
function chatbot_build_context($rows, $maxChars, $studentEnrollments = [], $isLoggedIn = false, $allCoursesSummary = [])
{
    $lines = [];

    if (!empty($allCoursesSummary)) {
        $lines[] = 'NextGen Learning Platform Summary:';
        $lines[] = 'Total Courses Available on Website: ' . count($allCoursesSummary);
        $lines[] = 'List of All Available Courses on Website:';
        foreach ($allCoursesSummary as $ac) {
            $priceRaw = (string) ($ac['price'] ?? '');
            $priceStr = ($priceRaw === '0' || strtolower($priceRaw) === 'free' || trim($priceRaw) === '') ? 'Free' : $priceRaw . ' BDT (Tk)';
            $lines[] = '- ' . $ac['title'] . ' (Price: ' . $priceStr . ')';
        }
        $lines[] = '';
    }

    if ($isLoggedIn) {
        $lines[] = 'Student Account Status: Logged In';
        if (!empty($studentEnrollments)) {
            $lines[] = 'Student Enrolled / Purchased Courses:';
            foreach ($studentEnrollments as $enr) {
                $lines[] = '- ' . $enr['course_title'] . ' (Status: ' . $enr['status'] . ')';
            }
        } else {
            $lines[] = 'Student Enrolled / Purchased Courses: None (No active enrollments found).';
        }
        $lines[] = '';
    } else {
        $lines[] = 'Student Account Status: Not Logged In / Guest';
        $lines[] = '';
    }

    if (empty($rows)) {
        $context = trim(implode("\n", $lines));
        return function_exists('mb_substr') ? mb_substr($context, 0, $maxChars) : substr($context, 0, $maxChars);
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

    foreach ($courses as $course) {
        $priceRaw = (string) $course['price'];
        $priceDisplay = ($priceRaw === '0' || strtolower($priceRaw) === 'free' || trim($priceRaw) === '') ? 'Free' : $priceRaw . ' BDT (Tk)';

        $lines[] = 'Course: ' . $course['title'];
        $lines[] = 'Summary: ' . $course['short_desc'];
        $lines[] = 'Description: ' . $course['description'];
        $lines[] = 'Duration: ' . $course['duration'] . '; Language: ' . $course['language'] . '; Price: ' . $priceDisplay;
        $lines[] = 'Total lectures: ' . $course['total_lectures'] . '; Status: ' . $course['status'] . '; Availability: ' . $course['upcoming'];
        if ($course['instructor'] !== '') {
            $lines[] = 'Instructor: ' . $course['instructor'];
        }

        foreach ($course['lectures'] as $lecture) {
            $lines[] = 'Lecture: ' . $lecture['title'];
            foreach ($lecture['topics'] as $topic) {
                $tPrice = (string) $topic['price'];
                $tPriceDisplay = ($tPrice === 'free' || $tPrice === '0') ? 'Free' : $tPrice;
                $lines[] = 'Topic: ' . $topic['title'] . ' (' . $topic['duration'] . ', ' . $tPriceDisplay . ')';
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
