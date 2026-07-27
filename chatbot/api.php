<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smalltalk.php';
require_once __DIR__ . '/course_context.php';
require_once __DIR__ . '/deepseek_client.php';
require_once __DIR__ . '/conversation.php';

try {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $question = chatbot_sanitize_question($payload['message'] ?? '', $chatbot_config['max_question_length']);
    $courseId = isset($payload['course_id']) ? (int) $payload['course_id'] : null;
    $userId = $_SESSION['user_id'] ?? 0;

    if ($question === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please enter a question.']);
        exit;
    }

    $history = chatbot_get_history();

    // Greetings and chit-chat get an instant canned reply instead of a course-database lookup.
    $smalltalkType = chatbot_detect_smalltalk($question);
    if ($smalltalkType !== null) {
        $reply = chatbot_smalltalk_reply($smalltalkType);
        chatbot_append_history('user', $question, (int) $chatbot_config['max_history_messages']);
        chatbot_append_history('assistant', $reply, (int) $chatbot_config['max_history_messages']);

        echo json_encode([
            'success' => true,
            'answer' => $reply,
            'sources_count' => 0,
        ]);
        exit;
    }

    $allCoursesSummary = chatbot_fetch_all_courses_summary($conn);
    $rows = chatbot_fetch_relevant_course_rows($conn, $question, (int) $chatbot_config['max_context_rows'], $courseId);
    $enrollments = chatbot_fetch_student_enrollments($conn, $userId);
    $context = chatbot_build_context($rows, (int) $chatbot_config['max_context_chars'], $enrollments, !empty($userId), $allCoursesSummary);

    if ($context === '') {
        $reply = "I don't have any course information for that yet. Try asking about a course by name, topic, price, or duration.";
        chatbot_append_history('user', $question, (int) $chatbot_config['max_history_messages']);
        chatbot_append_history('assistant', $reply, (int) $chatbot_config['max_history_messages']);

        echo json_encode([
            'success' => true,
            'answer' => $reply,
            'sources_count' => 0,
        ]);
        exit;
    }

    $answer = chatbot_call_deepseek($chatbot_config, $question, $context, $history);
    chatbot_append_history('user', $question, (int) $chatbot_config['max_history_messages']);
    chatbot_append_history('assistant', $answer, (int) $chatbot_config['max_history_messages']);

    echo json_encode([
        'success' => true,
        'answer' => $answer,
        'sources_count' => count($rows),
    ]);
} catch (Throwable $e) {
    error_log('[NextGen Course Chatbot] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => chatbot_friendly_error_message($e),
    ]);
}
