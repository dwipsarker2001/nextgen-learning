(function () {
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');

    function addMessage(text, type) {
        const row = document.createElement('div');
        row.className = 'chat-message ' + type;

        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.textContent = text;

        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;

        return row;
    }

    function setLoading(isLoading) {
        const button = form.querySelector('button');
        button.disabled = isLoading;
        input.disabled = isLoading;
    }

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
            const response = await fetch('api.php', {
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
