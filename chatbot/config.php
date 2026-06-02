<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

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
    'groq_api_key' => getenv('GROQ_API_KEY') ?: '',
    'groq_model' => getenv('GROQ_MODEL') ?: 'llama-3.1-8b-instant',
    'groq_endpoint' => getenv('GROQ_ENDPOINT') ?: 'https://api.groq.com/openai/v1/chat/completions',
    'max_question_length' => 1000,
    'max_context_chars' => 6000,
    'max_context_rows' => 40,
];
