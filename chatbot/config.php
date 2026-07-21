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
    'openrouter_api_key' => getenv('OPENROUTER_API_KEY') ?: '',
    'openrouter_model' => getenv('OPENROUTER_MODEL') ?: 'google/gemma-4-26b-a4b-it:free',
    'openrouter_fallback_models' => [
        'google/gemma-4-26b-a4b-it:free',
        'openai/gpt-oss-20b:free',
        'openrouter/free',
    ],
    'openrouter_endpoint' => getenv('OPENROUTER_ENDPOINT') ?: 'https://openrouter.ai/api/v1/chat/completions',
    'openrouter_site_url' => getenv('OPENROUTER_SITE_URL') ?: 'http://localhost/nextgen-learning/',
    'openrouter_site_title' => getenv('OPENROUTER_SITE_TITLE') ?: 'NextGen Learning',
    'max_question_length' => 1000,
    'max_context_chars' => 6000,
    'max_context_rows' => 40,
];
