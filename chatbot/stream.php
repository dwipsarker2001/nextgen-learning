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
require_once __DIR__ . '/deepseek_client.php';
require_once __DIR__ . '/conversation.php';

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

    $history = chatbot_get_history();

    $smalltalkType = chatbot_detect_smalltalk($question);
    if ($smalltalkType !== null) {
        $reply = chatbot_smalltalk_reply($smalltalkType);
        chatbot_stream_static_text($reply);
        chatbot_append_history('user', $question, (int) $chatbot_config['max_history_messages']);
        chatbot_append_history('assistant', $reply, (int) $chatbot_config['max_history_messages']);
        echo "data: [DONE]\n\n";
        flush();
        exit;
    }

    $allCoursesSummary = chatbot_fetch_all_courses_summary($conn);
    $rows = chatbot_fetch_relevant_course_rows($conn, $question, (int) $chatbot_config['max_context_rows'], $courseId);
    $enrollments = chatbot_fetch_student_enrollments($conn, $userId);
    $context = chatbot_build_context($rows, (int) $chatbot_config['max_context_chars'], $enrollments, !empty($userId), $allCoursesSummary, $courseId);

    if ($context === '') {
        $reply = "I don't have any course information for that yet. Try asking about a course by name, topic, price, or duration.";
        chatbot_stream_static_text($reply);
        chatbot_append_history('user', $question, (int) $chatbot_config['max_history_messages']);
        chatbot_append_history('assistant', $reply, (int) $chatbot_config['max_history_messages']);
        echo "data: [DONE]\n\n";
        flush();
        exit;
    }

    $fullAnswer = '';
    chatbot_call_deepseek_stream($chatbot_config, $question, $context, $history, function ($delta) use (&$fullAnswer) {
        $fullAnswer .= $delta;
        chatbot_send_event(['delta' => $delta]);
    });

    // A stray safety-classifier echo (rare on free models) shouldn't poison later turns,
    // even though this turn already displayed it to the user.
    $historyAnswer = chatbot_is_safety_stub($fullAnswer) ? '' : $fullAnswer;
    chatbot_append_history('user', $question, (int) $chatbot_config['max_history_messages']);
    if ($historyAnswer !== '') {
        chatbot_append_history('assistant', $historyAnswer, (int) $chatbot_config['max_history_messages']);
    }

    echo "data: [DONE]\n\n";
    flush();
} catch (Throwable $e) {
    error_log('[NextGen Course Chatbot] ' . $e->getMessage());
    chatbot_send_event(['error' => chatbot_friendly_error_message($e)]);
    echo "data: [DONE]\n\n";
    flush();
}
