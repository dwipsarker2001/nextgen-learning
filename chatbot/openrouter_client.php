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
        'For questions about courses, pricing, lectures, enrollment, or the platform: answer only from the provided course context, and if that context does not contain the answer, say that the course database does not include that information. Never invent course facts.',
        'For ordinary conversation, such as the student\'s name, greetings, or something said earlier in this chat, freely use the conversation history above even though it is not part of the course context.',
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
 * Turn a caught exception's technical message into a short, student-friendly
 * reply. Full technical detail is still logged server-side by the caller.
 */
function chatbot_friendly_error_message($e)
{
    $message = strtolower($e->getMessage());

    if (strpos($message, 'rate limit') !== false) {
        return "I'm getting a lot of questions right now and have reached today's limit. Please try again in a little while.";
    }

    if (strpos($message, 'api key') !== false) {
        return "The chatbot isn't fully set up yet. Please let the site admin know.";
    }

    if (strpos($message, 'timed out') !== false || strpos($message, 'stall') !== false) {
        return "That's taking longer than expected. Please try again in a moment.";
    }

    return "Sorry, I couldn't answer that just now. Please try again in a moment.";
}

/**
 * Detect a bare safety/moderation-classifier echo (e.g. "Safety: safe", "Response Safety: safe")
 * that some free OpenRouter models occasionally return instead of a real answer.
 */
function chatbot_is_safety_stub($text)
{
    return (bool) preg_match('/^\**\s*(?:response\s+|user\s+)?safety\s*:\s*safe\s*\**$/i', trim($text));
}

/**
 * Defense-in-depth for reasoning models (e.g. gpt-oss's Harmony format): the
 * "reasoning.exclude" request flag should already keep the model's chain-of-thought
 * out of the response, but if a provider leaks it anyway as raw
 * "<|channel|>analysis<|message|>..." text, keep only the "final" channel content
 * (or treat the answer as missing if the reply never reached a final channel).
 */
function chatbot_strip_harmony_channels($text)
{
    if (strpos($text, '<|channel|>final<|message|>') !== false) {
        $parts = explode('<|channel|>final<|message|>', $text);
        $final = end($parts);
        $final = preg_replace('/<\|(return|call|end|start)\|>.*$/s', '', $final);

        return trim($final);
    }

    if (preg_match('/<\|channel\|>\s*(analysis|commentary)\s*<\|message\|>/i', $text)) {
        return '';
    }

    return $text;
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
 * Build the messages array sent to OpenRouter: system prompt, then prior chat
 * turns (plain text, no course context) for continuity, then the current turn
 * with fresh course context appended.
 */
function chatbot_build_messages($question, $context, $history)
{
    $messages = [
        [
            'role' => 'system',
            'content' => chatbot_system_prompt(),
        ],
    ];

    foreach ($history as $turn) {
        if (($turn['role'] ?? '') === 'user' || ($turn['role'] ?? '') === 'assistant') {
            $messages[] = ['role' => $turn['role'], 'content' => (string) ($turn['content'] ?? '')];
        }
    }

    $messages[] = [
        'role' => 'user',
        'content' => "Course context:\n" . $context . "\n\nStudent question:\n" . $question,
    ];

    return $messages;
}

/**
 * Call OpenRouter API with fallback models and return the first successful text answer.
 */
function chatbot_call_openrouter($config, $question, $context, $history = [])
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
                'max_tokens' => 1200,
                // Some free/reasoning models (e.g. gpt-oss) think at length before answering;
                // this keeps that chain-of-thought out of the visible response.
                'reasoning' => ['exclude' => true],
                'messages' => chatbot_build_messages($question, $context, $history),
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
                CURLOPT_CONNECTTIMEOUT => 10,
                // Abort only on a true stall (near-zero throughput for 15s straight), not just
                // because a model is legitimately slow — a flat deadline would cut off good answers.
                CURLOPT_LOW_SPEED_LIMIT => 1,
                CURLOPT_LOW_SPEED_TIME => 15,
                CURLOPT_TIMEOUT => 40,
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
            $answer = chatbot_strip_harmony_channels($answer);
            $answer = preg_replace('/^\**\s*(?:response\s+|user\s+)?safety\s*:\s*safe\s*\**\s*/i', '', $answer);
            $answer = trim($answer);

            if ($answer === '' || chatbot_is_safety_stub($answer)) {
                throw new Exception("OpenRouter returned a safety-classifier stub or reasoning-only response instead of an answer for {$model}.");
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
function chatbot_call_openrouter_stream($config, $question, $context, $history, $onDelta)
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
                'max_tokens' => 1200,
                'stream' => true,
                // Some free/reasoning models (e.g. gpt-oss) think at length before answering;
                // this keeps that chain-of-thought out of the visible response.
                'reasoning' => ['exclude' => true],
                'messages' => chatbot_build_messages($question, $context, $history),
            ];

            if (count($fallbackModels) > 1) {
                $payload['models'] = $fallbackModels;
            }

            $lineBuffer = '';
            $rawBody = '';
            $sawAnyDelta = false;

            // Defense-in-depth in case a provider leaks raw Harmony-format reasoning text
            // (e.g. "<|channel|>analysis<|message|>...") despite reasoning.exclude above.
            // harmonyMode: null = still sniffing, 'suppressed' = swallowing an analysis/
            // commentary channel, 'final' = past the final-channel marker but still tail-
            // buffering in case a terminator token (e.g. "<|return|>") is split across
            // chunks, 'passthrough' = a normal (non-Harmony) model, forwarding raw, 'done'
            // = terminator already found, ignore anything further.
            $harmonyMode = null;
            $sniffBuffer = '';
            $sniffLimit = 40;
            $finalTail = '';
            $terminators = ['<|return|>', '<|call|>', '<|start|>', '<|end|>'];
            $maxTerminatorLen = 10;

            $flushFinalTail = function () use (&$finalTail, &$harmonyMode, $terminators, $maxTerminatorLen, $onDelta) {
                foreach ($terminators as $terminator) {
                    $pos = strpos($finalTail, $terminator);
                    if ($pos !== false) {
                        $safe = substr($finalTail, 0, $pos);
                        if ($safe !== '') {
                            $onDelta($safe);
                        }
                        $harmonyMode = 'done';
                        $finalTail = '';
                        return;
                    }
                }

                // No terminator yet; flush everything except a small tail that could be
                // the start of one, so a token split across chunks is never emitted early.
                $keep = $maxTerminatorLen - 1;
                if (mb_strlen($finalTail) > $keep) {
                    $flushLen = mb_strlen($finalTail) - $keep;
                    $onDelta(mb_substr($finalTail, 0, $flushLen));
                    $finalTail = mb_substr($finalTail, $flushLen);
                }
            };

            $ch = curl_init($config['openrouter_endpoint']);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => chatbot_openrouter_headers($config),
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_CONNECTTIMEOUT => 10,
                // Abort only on a true stall (near-zero throughput for 15s straight), not just
                // because a model is legitimately slow — a flat deadline would cut a stream mid-answer.
                CURLOPT_LOW_SPEED_LIMIT => 1,
                CURLOPT_LOW_SPEED_TIME => 15,
                CURLOPT_TIMEOUT => 40,
                CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$lineBuffer, &$rawBody, &$sawAnyDelta, &$harmonyMode, &$sniffBuffer, &$finalTail, $sniffLimit, $flushFinalTail, $onDelta) {
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
                        if ($delta === '') {
                            continue;
                        }

                        $sawAnyDelta = true;

                        if ($harmonyMode === 'passthrough') {
                            $onDelta($delta);
                            continue;
                        }

                        if ($harmonyMode === 'done') {
                            continue;
                        }

                        if ($harmonyMode === 'final') {
                            $finalTail .= $delta;
                            $flushFinalTail();
                            continue;
                        }

                        $sniffBuffer .= $delta;
                        $finalMarkerPos = strpos($sniffBuffer, '<|channel|>final<|message|>');

                        if ($finalMarkerPos !== false) {
                            $finalTail = substr($sniffBuffer, $finalMarkerPos + strlen('<|channel|>final<|message|>'));
                            $harmonyMode = 'final';
                            $sniffBuffer = '';
                            $flushFinalTail();
                            continue;
                        }

                        if (strpos($sniffBuffer, '<|channel|>analysis') !== false || strpos($sniffBuffer, '<|channel|>commentary') !== false) {
                            $harmonyMode = 'suppressed';
                            continue;
                        }

                        if ($harmonyMode === null && mb_strlen($sniffBuffer) >= $sniffLimit) {
                            // No Harmony markers within the sniff window; a normal model, flush it.
                            $harmonyMode = 'passthrough';
                            $onDelta($sniffBuffer);
                            $sniffBuffer = '';
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

            if ($harmonyMode === 'suppressed') {
                // The stream ended mid-analysis and never reached a final channel — no real answer.
                throw new Exception("OpenRouter stream never reached a final answer for {$model}.");
            }

            // Anything still held back (a short non-Harmony answer, or the tail end of a
            // final-channel answer that never hit a terminator token) is genuinely done now.
            if ($harmonyMode === null && $sniffBuffer !== '') {
                $onDelta($sniffBuffer);
            } elseif ($harmonyMode === 'final' && $finalTail !== '') {
                $onDelta($finalTail);
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
                'reasoning' => ['exclude' => true],
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
            $content = chatbot_strip_harmony_channels($content);
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
