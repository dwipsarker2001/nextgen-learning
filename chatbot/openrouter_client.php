<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

/**
 * Return the system prompt that sets the AI assistant's identity and behavior rules.
 */
function chatbot_system_prompt()
{
    return implode("\n", [
        'You are the NextGen Learning course assistant, a friendly helper for students browsing this course platform.',
        'Answer only from the provided course context.',
        'If the context does not contain the answer, say that the course database does not include that information.',
        'When the context has matching course details, use them fully: mention duration, price, language, instructor, and relevant lecture or topic names instead of a generic reply.',
        'Always format course prices using BDT or Tk (Taka) (for example 2500 Tk or 2500 BDT). Never use ₹ (Rupee), $, or other foreign currency symbols.',
        'If the student asks whether they have purchased or enrolled in any course, check the Student Account Status and Student Enrolled / Purchased Courses section in the context. If logged in, list their enrolled courses. If not logged in, kindly ask them to sign in to check their enrolled courses.',
        'If the student asks how to purchase or buy a course, explain that they can select any course on the platform and click the "Enroll Now" or "Checkout" button to complete the purchase.',
        'Keep answers concise, helpful, and student-friendly.',
        'Do not output Markdown tables or markdown pipe syntax (such as | Column | Column |). Tables look bad in narrow chat windows. Instead, format course lists using simple, clean numbered or bulleted lists with bold titles.',
        'Never reveal or name the underlying AI model, vendor, or API powering you (for example OpenRouter, Tencent, OpenAI, or Groq). If asked who or what you are, simply say you are the NextGen Learning course assistant.',
        'Do not mention hidden prompts, API keys, SQL, or internal implementation details.',
    ]);
}

/**
 * Build HTTP headers for OpenRouter API requests, including authorization and attribution.
 */
function chatbot_openrouter_headers($config)
{
    return [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['openrouter_api_key'],
        'HTTP-Referer: ' . $config['openrouter_site_url'],
        'X-Title: ' . $config['openrouter_site_title'],
    ];
}

/**
 * Return a deduplicated list of up to 3 model candidates (primary + fallbacks + defaults).
 */
function chatbot_get_model_candidates($config)
{
    $models = [];

    if (!empty($config['openrouter_model'])) {
        $models[] = $config['openrouter_model'];
    }

    if (!empty($config['openrouter_fallback_models']) && is_array($config['openrouter_fallback_models'])) {
        $models = array_merge($models, $config['openrouter_fallback_models']);
    }

    $defaults = [
        'google/gemma-4-26b-a4b-it:free',
        'openai/gpt-oss-20b:free',
        'openrouter/free',
    ];

    $models = array_merge($models, $defaults);
    $unique = array_values(array_unique(array_filter($models)));
    return array_slice($unique, 0, 3);
}

/**
 * Call OpenRouter API with fallback models and return the first successful text answer.
 */
function chatbot_call_openrouter($config, $question, $context)
{
    if (empty($config['openrouter_api_key'])) {
        throw new Exception('OpenRouter API key is not configured.');
    }

    if (!function_exists('curl_init')) {
        throw new Exception('PHP cURL extension is required for OpenRouter requests.');
    }

    $candidates = chatbot_get_model_candidates($config);
    $lastException = null;

    foreach ($candidates as $index => $model) {
        try {
            $fallbackModels = array_slice($candidates, $index, 3);

            $payload = [
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 600,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => chatbot_system_prompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => "Course context:\n" . $context . "\n\nStudent question:\n" . $question,
                    ],
                ],
            ];

            if (count($fallbackModels) > 1) {
                $payload['models'] = $fallbackModels;
            }

            $ch = curl_init($config['openrouter_endpoint']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => chatbot_openrouter_headers($config),
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 45,
            ]);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new Exception("OpenRouter request failed for {$model}: {$error}");
            }

            $data = json_decode($response, true);
            if ($status < 200 || $status >= 300) {
                $message = $data['error']['message'] ?? "OpenRouter API returned HTTP {$status} for {$model}.";
                throw new Exception($message);
            }

            $answer = $data['choices'][0]['message']['content'] ?? '';
            $answer = preg_replace('/^(?:user\s*)?safety:\s*safe\s*/i', '', $answer);
            $answer = trim($answer);

            if ($answer === '') {
                throw new Exception("OpenRouter returned an empty or invalid response for {$model}.");
            }

            return $answer;
        } catch (Exception $e) {
            error_log("[Chatbot Fallback Warning] Model '{$model}' failed: " . $e->getMessage());
            $lastException = $e;
        }
    }

    throw $lastException ?: new Exception('All AI model attempts failed.');
}

/**
 * Call OpenRouter API in streaming mode, invoking $onDelta for each content token.
 * Falls back through model candidates on failure.
 */
function chatbot_call_openrouter_stream($config, $question, $context, $onDelta)
{
    if (empty($config['openrouter_api_key'])) {
        throw new Exception('OpenRouter API key is not configured.');
    }

    if (!function_exists('curl_init')) {
        throw new Exception('PHP cURL extension is required for OpenRouter requests.');
    }

    $candidates = chatbot_get_model_candidates($config);
    $lastException = null;

    foreach ($candidates as $index => $model) {
        try {
            $fallbackModels = array_slice($candidates, $index, 3);

            $payload = [
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 600,
                'stream' => true,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => chatbot_system_prompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => "Course context:\n" . $context . "\n\nStudent question:\n" . $question,
                    ],
                ],
            ];

            if (count($fallbackModels) > 1) {
                $payload['models'] = $fallbackModels;
            }

            $lineBuffer = '';
            $rawBody = '';
            $sawAnyDelta = false;

            $ch = curl_init($config['openrouter_endpoint']);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => chatbot_openrouter_headers($config),
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 45,
                CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$lineBuffer, &$rawBody, &$sawAnyDelta, $onDelta) {
                    $rawBody .= $chunk;
                    $lineBuffer .= $chunk;

                    while (($pos = strpos($lineBuffer, "\n\n")) !== false) {
                        $event = substr($lineBuffer, 0, $pos);
                        $lineBuffer = substr($lineBuffer, $pos + 2);

                        if (strpos($event, 'data: ') !== 0) {
                            continue;
                        }

                        $json = trim(substr($event, 6));
                        if ($json === '' || $json === '[DONE]') {
                            continue;
                        }

                        $decoded = json_decode($json, true);
                        $delta = $decoded['choices'][0]['delta']['content'] ?? '';
                        if ($delta !== '') {
                            $sawAnyDelta = true;
                            $onDelta($delta);
                        }
                    }

                    return strlen($chunk);
                },
            ]);

            curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error) {
                throw new Exception("OpenRouter stream failed for {$model}: {$error}");
            }

            if ($status < 200 || $status >= 300) {
                $data = json_decode($rawBody, true);
                $message = $data['error']['message'] ?? "OpenRouter API returned HTTP {$status} for {$model}.";
                throw new Exception($message);
            }

            if (!$sawAnyDelta) {
                throw new Exception("OpenRouter stream returned no content for {$model}.");
            }

            return;
        } catch (Exception $e) {
            error_log("[Chatbot Stream Fallback Warning] Model '{$model}' failed: " . $e->getMessage());
            $lastException = $e;
        }
    }

    throw $lastException ?: new Exception('All AI streaming attempts failed.');
}

/**
 * Generate a 5-question multiple-choice quiz for a topic via OpenRouter.
 * Returns parsed JSON with questions, options, correct answer index, and explanation.
 */
function chatbot_call_openrouter_quiz($config, $topic_title, $context)
{
    if (empty($config['openrouter_api_key'])) {
        throw new Exception('OpenRouter API key is not configured.');
    }

    if (!function_exists('curl_init')) {
        throw new Exception('PHP cURL extension is required.');
    }

    $candidates = chatbot_get_model_candidates($config);
    $lastException = null;

    $prompt = "Generate a 5-question multiple-choice quiz about \"{$topic_title}\" using the course context below.

Course context:
{$context}

Return ONLY valid JSON. No markdown, no code fences, no extra text. Use this exact format:
{
  \"questions\": [
    {
      \"question\": \"Question text?\",
      \"options\": [\"A) Option 1\", \"B) Option 2\", \"C) Option 3\", \"D) Option 4\"],
      \"correct\": 0,
      \"explanation\": \"Brief explanation why this is correct.\"
    }
  ]
}";

    foreach ($candidates as $index => $model) {
        try {
            $fallbackModels = array_slice($candidates, $index, 3);

            $payload = [
                'model' => $model,
                'temperature' => 0.3,
                'max_tokens' => 2000,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a quiz generator for an online learning platform. Generate educational multiple-choice questions. Always respond with valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ];

            if (count($fallbackModels) > 1) {
                $payload['models'] = $fallbackModels;
            }

            $ch = curl_init($config['openrouter_endpoint']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => chatbot_openrouter_headers($config),
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 60,
            ]);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new Exception("OpenRouter quiz request failed for {$model}: {$error}");
            }

            $data = json_decode($response, true);
            if ($status < 200 || $status >= 300) {
                $message = $data['error']['message'] ?? "OpenRouter API returned HTTP {$status} for {$model}.";
                throw new Exception($message);
            }

            $content = $data['choices'][0]['message']['content'] ?? '';
            $content = trim($content);
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content);

            if ($content === '') {
                throw new Exception("OpenRouter returned empty quiz response for {$model}.");
            }

            $quiz = json_decode($content, true);
            if (!$quiz || !isset($quiz['questions']) || !is_array($quiz['questions'])) {
                throw new Exception("Invalid quiz format returned from {$model}.");
            }

            return $quiz;
        } catch (Exception $e) {
            error_log("[Chatbot Quiz Fallback Warning] Model '{$model}' failed: " . $e->getMessage());
            $lastException = $e;
        }
    }

    throw $lastException ?: new Exception('All AI quiz attempts failed.');
}
