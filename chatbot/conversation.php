<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

/**
 * Return the chat turns stored in the visitor's session, oldest first.
 * Each turn is ['role' => 'user'|'assistant', 'content' => string].
 */
function chatbot_get_history()
{
    return $_SESSION['chatbot_history'] ?? [];
}

/**
 * Append one turn to the session's chat history, trimming to the most
 * recent $maxMessages entries so the conversation can't grow unbounded.
 */
function chatbot_append_history($role, $content, $maxMessages = 12)
{
    if (!isset($_SESSION['chatbot_history']) || !is_array($_SESSION['chatbot_history'])) {
        $_SESSION['chatbot_history'] = [];
    }

    $_SESSION['chatbot_history'][] = ['role' => $role, 'content' => $content];

    if (count($_SESSION['chatbot_history']) > $maxMessages) {
        $_SESSION['chatbot_history'] = array_slice($_SESSION['chatbot_history'], -$maxMessages);
    }
}
