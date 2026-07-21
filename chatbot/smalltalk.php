<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

/**
 * Normalize a small-talk question: lowercase, strip non-alphabetic chars, collapse whitespace.
 */
function chatbot_normalize_smalltalk($question)
{
    $question = strtolower($question);
    $question = preg_replace("/[^a-z' ]+/", ' ', $question);
    $question = preg_replace('/\s+/', ' ', $question);

    return trim($question);
}

/**
 * Detect whether the question is small talk (greeting, wellbeing, thanks, farewell, identity).
 * Returns the small-talk type string or null.
 */
function chatbot_detect_smalltalk($question)
{
    $normalized = chatbot_normalize_smalltalk($question);

    if ($normalized === '') {
        return null;
    }

    $patterns = [
        'greeting' => '/^(hi|hello|hey|hiya|yo|good morning|good afternoon|good evening)$/',
        'wellbeing' => "/^how(?:'?s| is| are) (?:you|it going|things)(?: doing)?$|^what'?s up$/",
        'thanks' => "/^(thanks|thank you|thx|ty|appreciate it)$/",
        'farewell' => '/^(bye|goodbye|good night|see you|see ya)$/',
        'identity' => "/^who are you$|^what are you$|^what(?:'s|s| is) your name$|^are you (?:an? )?(?:ai|bot|robot|human)$/",
    ];

    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $normalized)) {
            return $type;
        }
    }

    return null;
}

/**
 * Return a random canned reply for the given small-talk type, or null if unknown.
 */
function chatbot_smalltalk_reply($type)
{
    $replies = [
        'greeting' => [
            'Hi! How can I help you with NextGen Learning courses today?',
            'Hello! Ask me anything about our courses, lectures, or pricing.',
            'Hey there! What would you like to know about our courses?',
        ],
        'wellbeing' => [
            "I'm doing well, thanks for asking! How can I help you with a course?",
            "Doing great! Let me know if you'd like help finding the right course.",
        ],
        'thanks' => [
            "You're welcome! Let me know if you have more questions about our courses.",
            'Happy to help! Feel free to ask anything else about our courses.',
        ],
        'farewell' => [
            'Goodbye! Come back anytime you have questions about our courses.',
            'See you soon! Good luck with your learning.',
        ],
        'identity' => [
            "I'm the NextGen Learning course assistant. I can help you find courses, check pricing, or explore lecture topics.",
        ],
    ];

    $options = $replies[$type] ?? null;
    if (!$options) {
        return null;
    }

    return $options[array_rand($options)];
}
