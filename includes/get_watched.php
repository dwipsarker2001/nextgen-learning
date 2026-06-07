<?php

function log_topic_watched($conn, $user_id, $topic_id, $course_id)
{
    $stmt = $conn->prepare("SELECT id FROM watched_topics WHERE user_id = ? AND topic_id = ? AND course_id = ?");
    $stmt->bind_param("iii", $user_id, $topic_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO watched_topics (user_id, topic_id, course_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $topic_id, $course_id);
        $stmt->execute();
    }
    $stmt->close();
}

function get_recently_watched($conn, $user_id, $limit = 5)
{
    $sql = "SELECT DISTINCT w.course_id, w.topic_id, w.watched_at,
                   t.title AS topic_title, t.duration,
                   c.title AS course_title, c.thumbnail
            FROM watched_topics w
            JOIN topics t ON w.topic_id = t.id
            JOIN courses c ON w.course_id = c.id
            WHERE w.user_id = ?
            ORDER BY w.watched_at DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

function get_learning_streak($conn, $user_id)
{
    $sql = "SELECT DISTINCT DATE(watched_at) AS watch_date
            FROM watched_topics
            WHERE user_id = ?
            ORDER BY watch_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $dates = [];
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['watch_date'];
    }
    $stmt->close();

    if (empty($dates)) return 0;

    $streak = 0;
    $today = new DateTime('today');
    $check_date = clone $today;

    foreach ($dates as $date_str) {
        $date = new DateTime($date_str);
        if ($date->format('Y-m-d') === $check_date->format('Y-m-d')) {
            $streak++;
            $check_date->modify('-1 day');
        } elseif ($date->format('Y-m-d') === $check_date->modify('-1 day')->format('Y-m-d')) {
            $streak++;
        } else {
            break;
        }
    }

    return $streak;
}

function get_total_watched_topics($conn, $user_id)
{
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT topic_id) AS total FROM watched_topics WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int)$row['total'];
}
