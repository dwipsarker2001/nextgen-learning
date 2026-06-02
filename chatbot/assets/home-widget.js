(function () {
    const toggle = document.getElementById('ngChatToggle');
    const close = document.getElementById('ngChatClose');
    const windowEl = document.getElementById('ngChatWindow');
    const form = document.getElementById('ngChatForm');
    const input = document.getElementById('ngChatInput');
    const messages = document.getElementById('ngChatMessages');

    if (!toggle || !close || !windowEl || !form || !input || !messages) {
        return;
    }

    function addMessage(text, type) {
        const row = document.createElement('div');
        row.className = 'ng-chat-message ' + type;

        const bubble = document.createElement('div');
        bubble.className = 'ng-chat-bubble';
        bubble.textContent = text;

        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;

        return row;
    }

    function setLoading(isLoading) {
        form.querySelector('button').disabled = isLoading;
        input.disabled = isLoading;
    }

    toggle.addEventListener('click', function () {
        windowEl.hidden = false;
        input.focus();
    });

    close.addEventListener('click', function () {
        windowEl.hidden = true;
        toggle.focus();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const message = input.value.trim();
        if (!message) {
            return;
        }

        addMessage(message, 'user');
        input.value = '';
        setLoading(true);

        const loadingRow = addMessage('Thinking...', 'bot');

        try {
            const response = await fetch('chatbot/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: message }),
            });

            const data = await response.json();
            loadingRow.remove();

            if (!response.ok || !data.success) {
                addMessage(data.message || 'The chatbot could not answer right now.', 'bot');
                return;
            }

            addMessage(data.answer, 'bot');
        } catch (error) {
            loadingRow.remove();
            addMessage('The chatbot could not connect to the server. Please try again.', 'bot');
        } finally {
            setLoading(false);
            input.focus();
        }
    });
})();
