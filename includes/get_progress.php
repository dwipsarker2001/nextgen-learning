<?php

function get_course_progress($conn, $user_id, $course_id)
{
    $total_topics = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM topics WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_topics = (int)$row['total'];
    $stmt->close();

    if ($total_topics === 0) return 0;

    $watched = 0;
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT topic_id) AS watched FROM watched_topics WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $watched = (int)$row['watched'];
    $stmt->close();

    return round(($watched / $total_topics) * 100);
}

function get_all_courses_progress($conn, $user_id)
{
    $sql = "SELECT e.course_id, c.title, c.thumbnail, c.duration
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.user_id = ? AND e.status = 'success'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $row['progress'] = get_course_progress($conn, $user_id, $row['course_id']);
        $courses[] = $row;
    }
    $stmt->close();
    return $courses;
}

function get_completed_courses_count($conn, $user_id)
{
    $courses = get_all_courses_progress($conn, $user_id);
    $count = 0;
    foreach ($courses as $c) {
        if ($c['progress'] >= 100) $count++;
    }
    return $count;
}

function get_in_progress_courses_count($conn, $user_id)
{
    $courses = get_all_courses_progress($conn, $user_id);
    $count = 0;
    foreach ($courses as $c) {
        if ($c['progress'] > 0 && $c['progress'] < 100) $count++;
    }
    return $count;
}
