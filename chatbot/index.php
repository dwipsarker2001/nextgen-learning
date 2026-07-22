<?php
$pageTitle = 'NextGen Course Chatbot | nextgen-learning';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/chatbot.css?v=<?= file_exists(__DIR__ . '/assets/chatbot.css') ? filemtime(__DIR__ . '/assets/chatbot.css') : time() ?>">
</head>

<body>
    <main class="chatbot-shell">
        <section class="chatbot-panel" aria-label="NextGen Course Chatbot">
            <header class="chatbot-header">
                <a class="chatbot-back" href="../index.php" aria-label="Back to home">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1>NextGen Course Chatbot</h1>
                    <p>Ask questions about available course content.</p>
                </div>
            </header>

            <div id="chatMessages" class="chatbot-messages" aria-live="polite">
                <div class="chat-message bot">
                    <div class="chat-avatar"><i class="bi bi-robot"></i></div>
                    <div class="bubble">Hi. Ask me about course topics, duration, language, pricing, or what you can learn.</div>
                </div>
            </div>

            <form id="chatForm" class="chatbot-form" autocomplete="off">
                <label class="sr-only" for="chatInput">Ask a course question</label>
                <textarea id="chatInput" name="message" rows="2" maxlength="1000" placeholder="Ask about a course..." required></textarea>
                <button type="submit" aria-label="Send message">
                    <i class="bi bi-send"></i>
                </button>
            </form>
        </section>
    </main>

    <script src="assets/chatbot.js?v=<?= file_exists(__DIR__ . '/assets/chatbot.js') ? filemtime(__DIR__ . '/assets/chatbot.js') : time() ?>"></script>
</body>

</html>
