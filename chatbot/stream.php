<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smalltalk.php';
require_once __DIR__ . '/course_context.php';
require_once __DIR__ . '/openrouter_client.php';

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
ini_set('zlib.output_compression', '0');
ini_set('output_buffering', 'off');
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_implicit_flush(true);

/**
 * Send a Server-Sent Event to the client.
 * Encodes the payload as JSON and flushes the output buffer immediately.
 */
function chatbot_send_event($payload)
{
    echo 'data: ' . json_encode($payload) . "\n\n";
    flush();
}

/**
 * Stream static text (small talk, fallback messages) token-by-token at a readable pace
 * so it visually matches the real model stream output.
 */
function chatbot_stream_static_text($text)
{
    $pieces = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($pieces as $piece) {
        if ($piece === '') {
            continue;
        }

        chatbot_send_event(['delta' => $piece]);
        usleep(35000);
    }
}

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
        chatbot_send_event(['error' => 'Please enter a question.']);
        echo "data: [DONE]\n\n";
        flush();
        exit;
    }

    $smalltalkType = chatbot_detect_smalltalk($question);
    if ($smalltalkType !== null) {
        chatbot_stream_static_text(chatbot_smalltalk_reply($smalltalkType));
        echo "data: [DONE]\n\n";
        flush();
        exit;
    }

    $rows = chatbot_fetch_relevant_course_rows($conn, $question, (int) $chatbot_config['max_context_rows'], $courseId);
    $enrollments = chatbot_fetch_student_enrollments($conn, $userId);
    $context = chatbot_build_context($rows, (int) $chatbot_config['max_context_chars'], $enrollments, !empty($userId));

    if ($context === '') {
        chatbot_stream_static_text("I don't have any course information for that yet. Try asking about a course by name, topic, price, or duration.");
        echo "data: [DONE]\n\n";
        flush();
        exit;
    }

    chatbot_call_openrouter_stream($chatbot_config, $question, $context, function ($delta) {
        chatbot_send_event(['delta' => $delta]);
    });

    echo "data: [DONE]\n\n";
    flush();
} catch (Throwable $e) {
    error_log('[NextGen Course Chatbot] ' . $e->getMessage());
    chatbot_send_event(['error' => 'The chatbot could not answer right now. Please check the chatbot setup and server logs.']);
    echo "data: [DONE]\n\n";
    flush();
}
