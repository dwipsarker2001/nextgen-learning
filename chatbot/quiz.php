<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/course_context.php';
require_once __DIR__ . '/deepseek_client.php';

try {
    $user_id = $_SESSION['user_id'] ?? 0;
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Login required.']);
        exit;
    }

    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true) ?: $_POST;

    $topic_id = (int)($payload['topic_id'] ?? 0);
    $course_id = (int)($payload['course_id'] ?? 0);

    if (!$topic_id || !$course_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'topic_id and course_id required.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT title FROM topics WHERE id = ? AND course_id = ?");
    $stmt->bind_param("ii", $topic_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $topic = $result->fetch_assoc();
    $stmt->close();

    if (!$topic) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Topic not found.']);
        exit;
    }

    $topic_title = $topic['title'];

    $rows = chatbot_fetch_relevant_course_rows($conn, $topic_title, 20, $course_id);
    $context = chatbot_build_context($rows, 4000);

    $quiz = chatbot_call_deepseek_quiz($chatbot_config, $topic_title, $context);

    echo json_encode([
        'success' => true,
        'topic' => $topic_title,
        'quiz' => $quiz,
    ]);
} catch (Throwable $e) {
    error_log('[Quiz Generator] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to generate quiz. Please try again.']);
}
