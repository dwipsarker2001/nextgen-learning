<?php

function get_course_recommendations($conn, $user_id, $limit = 4)
{
    $enrolled_ids = [];
    $stmt = $conn->prepare("SELECT course_id FROM enrollments WHERE user_id = ? AND status = 'success'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $enrolled_ids[] = $row['course_id'];
    }
    $stmt->close();

    if (empty($enrolled_ids)) {
        $sql = "SELECT * FROM courses WHERE status = 'upcoming' ORDER BY created_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
    } else {
        $placeholders = implode(',', array_fill(0, count($enrolled_ids), '?'));

        $instructor_sql = "SELECT DISTINCT instructor_id FROM courses WHERE id IN ($placeholders) AND instructor_id IS NOT NULL";
        $stmt_i = $conn->prepare($instructor_sql);
        $types = str_repeat('i', count($enrolled_ids));
        $stmt_i->bind_param($types, ...$enrolled_ids);
        $stmt_i->execute();
        $inst_result = $stmt_i->get_result();
        $instructor_ids = [];
        while ($row = $inst_result->fetch_assoc()) {
            $instructor_ids[] = $row['instructor_id'];
        }
        $stmt_i->close();

        $exclude = array_merge($enrolled_ids, [0]);

        if (!empty($instructor_ids)) {
            $inst_placeholders = implode(',', array_fill(0, count($instructor_ids), '?'));
            $exclude_placeholders = implode(',', array_fill(0, count($exclude), '?'));

            $sql = "SELECT * FROM courses
                    WHERE instructor_id IN ($inst_placeholders)
                    AND id NOT IN ($exclude_placeholders)
                    AND status = 'upcoming'
                    ORDER BY created_at DESC
                    LIMIT ?";
            $stmt = $conn->prepare($sql);
            $all_params = array_merge($instructor_ids, $exclude, [$limit]);
            $types = str_repeat('i', count($all_params));
            $stmt->bind_param($types, ...$all_params);
        } else {
            $exclude_placeholders = implode(',', array_fill(0, count($exclude), '?'));
            $sql = "SELECT * FROM courses
                    WHERE id NOT IN ($exclude_placeholders)
                    AND status = 'upcoming'
                    ORDER BY created_at DESC
                    LIMIT ?";
            $stmt = $conn->prepare($sql);
            $all_params = array_merge($exclude, [$limit]);
            $types = str_repeat('i', count($all_params));
            $stmt->bind_param($types, ...$all_params);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $courses;
}
