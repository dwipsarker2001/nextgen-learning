<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

// Pages nested under admin/ or student/ set $ngChatBasePath = '../' before including this file.
$ngChatBasePath = $ngChatBasePath ?? '';
$ngChatCssVer = file_exists(__DIR__ . '/assets/home-widget.css') ? filemtime(__DIR__ . '/assets/home-widget.css') : time();

// Chat flow:
//   User submits message -> home-widget.js POSTs to stream.php (SSE)
//   -> stream.php sanitizes input, checks smalltalk.php for canned replies
//   -> Otherwise extracts keywords via course_context.php, queries DB for course data
//   -> Builds context string, calls deepseek_client.php which streams tokens
//      from DeepSeek API back through SSE to the browser in real time.
//   course-widget.js variant calls api.php (JSON, non-streaming) on the course page.
?>
<link rel="stylesheet" href="<?= htmlspecialchars($ngChatBasePath) ?>chatbot/assets/home-widget.css?v=<?= $ngChatCssVer ?>">

<div class="ng-chat-widget" id="ngChatWidget" data-base="<?= htmlspecialchars($ngChatBasePath) ?>">
    <button class="ng-chat-toggle" id="ngChatToggle" type="button" aria-label="Open course chatbot">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <section class="ng-chat-window" style="padding: 0;" id="ngChatWindow" aria-label="NextGen Course Chatbot" hidden>
        <header class="ng-chat-header">
            <div>
                <h2>Course Chatbot</h2>
                <p>Ask about NextGen courses</p>
            </div>
            <button class="ng-chat-close" id="ngChatClose" type="button" aria-label="Close chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="ng-chat-messages" id="ngChatMessages" aria-live="polite">
            <div class="ng-chat-message bot">
                <div class="ng-chat-avatar"><i class="bi bi-robot"></i></div>
                <div class="ng-chat-bubble">Hi. Ask me about course topics, duration, language, or pricing.</div>
            </div>
        </div>

        <form class="ng-chat-form" id="ngChatForm" autocomplete="off">
            <label class="ng-chat-sr-only" for="ngChatInput">Ask a course question</label>
            <textarea id="ngChatInput" name="message" rows="2" maxlength="1000" placeholder="Ask about a course..." required></textarea>
            <button type="submit" aria-label="Send message">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </section>
</div>

<script src="<?= htmlspecialchars($ngChatBasePath) ?>chatbot/assets/home-widget.js?v=<?= file_exists(__DIR__ . '/assets/home-widget.js') ? filemtime(__DIR__ . '/assets/home-widget.js') : time() ?>"></script>
