<?php
require_once 'db.php';
require_once 'session.php';
require_once 'get_watched.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
$topic_id = (int)($_POST['topic_id'] ?? 0);
$course_id = (int)($_POST['course_id'] ?? 0);

if (!$user_id || !$topic_id || !$course_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

log_topic_watched($conn, $user_id, $topic_id, $course_id);
echo json_encode(['success' => true]);
