<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smalltalk.php';
require_once __DIR__ . '/course_context.php';
require_once __DIR__ . '/openrouter_client.php';

try {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $question = chatbot_sanitize_question($payload['message'] ?? '', $chatbot_config['max_question_length']);
    $courseId = isset($payload['course_id']) ? (int) $payload['course_id'] : null;

    if ($question === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please enter a question.']);
        exit;
    }

    // Greetings and chit-chat get an instant canned reply instead of a course-database lookup.
    $smalltalkType = chatbot_detect_smalltalk($question);
    if ($smalltalkType !== null) {
        echo json_encode([
            'success' => true,
            'answer' => chatbot_smalltalk_reply($smalltalkType),
            'sources_count' => 0,
        ]);
        exit;
    }

    $rows = chatbot_fetch_relevant_course_rows($conn, $question, (int) $chatbot_config['max_context_rows'], $courseId);
    $context = chatbot_build_context($rows, (int) $chatbot_config['max_context_chars']);

    if ($context === '') {
        echo json_encode([
            'success' => true,
            'answer' => "I don't have any course information for that yet. Try asking about a course by name, topic, price, or duration.",
            'sources_count' => 0,
        ]);
        exit;
    }

    $answer = chatbot_call_openrouter($chatbot_config, $question, $context);

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
        'message' => 'The chatbot could not answer right now. Please check the chatbot setup and server logs.',
    ]);
}
