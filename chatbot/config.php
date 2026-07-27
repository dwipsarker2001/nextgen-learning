<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

/**
 * Load and set environment variables from a .env file if it exists and is readable.
 */
function chatbot_load_env_file($path)
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

chatbot_load_env_file(__DIR__ . '/.env');
chatbot_load_env_file(dirname(__DIR__) . '/.env');

$chatbot_config = [
    'deepseek_api_key' => getenv('DEEPSEEK_API_KEY') ?: 'sk-a1a9602882da412e9a57af53e11c3104',
    'deepseek_model' => getenv('DEEPSEEK_MODEL') ?: 'deepseek-v4-flash',
    'deepseek_endpoint' => getenv('DEEPSEEK_ENDPOINT') ?: 'https://api.deepseek.com/chat/completions',
    'max_question_length' => 1000,
    'max_context_chars' => 6000,
    'max_context_rows' => 40,
    'max_history_messages' => 12,
];
