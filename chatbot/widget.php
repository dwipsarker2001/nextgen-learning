<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}
?>
<link rel="stylesheet" href="chatbot/assets/home-widget.css">

<div class="ng-chat-widget" id="ngChatWidget">
    <button class="ng-chat-toggle" id="ngChatToggle" type="button" aria-label="Open course chatbot">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <section class="ng-chat-window" id="ngChatWindow" aria-label="Groq AI Course Chatbot" hidden>
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
                <div class="ng-chat-bubble">
                    Hi. Ask me about course topics, duration, language, or pricing.
                </div>
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

<script src="chatbot/assets/home-widget.js"></script>
