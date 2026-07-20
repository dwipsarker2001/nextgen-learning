(function () {
    const widget = document.getElementById('ngChatWidget');
    const toggle = document.getElementById('ngChatToggle');
    const close = document.getElementById('ngChatClose');
    const windowEl = document.getElementById('ngChatWindow');
    const form = document.getElementById('ngChatForm');
    const input = document.getElementById('ngChatInput');
    const messages = document.getElementById('ngChatMessages');

    if (!widget || !toggle || !close || !windowEl || !form || !input || !messages) {
        return;
    }

    // Admin/student pages live one directory below the site root, so widget.php
    // stamps how far up to go here (see $ngChatBasePath).
    const basePath = widget.dataset.base || '';

    const loadingPhrases = ['Thinking...', 'Checking course details...', 'Looking that up...', 'Almost there...'];

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

    function startLoadingMessage() {
        const row = addMessage(loadingPhrases[0], 'bot');
        const bubble = row.querySelector('.ng-chat-bubble');
        bubble.classList.add('loading-bubble');

        let phraseIndex = 0;
        const timer = setInterval(function () {
            phraseIndex = (phraseIndex + 1) % loadingPhrases.length;
            bubble.textContent = loadingPhrases[phraseIndex];
            messages.scrollTop = messages.scrollHeight;
        }, 1500);

        return {
            row: row,
            stop: function () {
                clearInterval(timer);
            },
        };
    }

    // Reads the chatbot/stream.php SSE response and calls onDelta as each token chunk
    // arrives, so the answer can be typed into the page as the model generates it.
    async function streamAnswer(message, onDelta) {
        const response = await fetch(basePath + 'chatbot/stream.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
            },
            body: JSON.stringify({ message: message }),
        });

        if (!response.ok || !response.body) {
            throw new Error('stream_unavailable');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let errorMessage = null;

        while (true) {
            const { value, done } = await reader.read();
            if (done) {
                break;
            }

            buffer += decoder.decode(value, { stream: true });

            let boundary;
            while ((boundary = buffer.indexOf('\n\n')) !== -1) {
                const rawEvent = buffer.slice(0, boundary);
                buffer = buffer.slice(boundary + 2);

                if (!rawEvent.startsWith('data: ')) {
                    continue;
                }

                const raw = rawEvent.slice(6).trim();
                if (raw === '[DONE]') {
                    return errorMessage;
                }

                let parsed;
                try {
                    parsed = JSON.parse(raw);
                } catch (parseError) {
                    continue;
                }

                if (parsed.error) {
                    errorMessage = parsed.error;
                } else if (parsed.delta) {
                    onDelta(parsed.delta);
                }
            }
        }

        return errorMessage;
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

        const loading = startLoadingMessage();
        let answerBubble = null;

        try {
            const errorMessage = await streamAnswer(message, function (delta) {
                if (!answerBubble) {
                    loading.stop();
                    loading.row.remove();
                    answerBubble = addMessage('', 'bot').querySelector('.ng-chat-bubble');
                }

                answerBubble.textContent += delta;
                messages.scrollTop = messages.scrollHeight;
            });

            if (!answerBubble) {
                loading.stop();
                loading.row.remove();
                addMessage(errorMessage || 'The chatbot could not answer right now.', 'bot');
            } else if (errorMessage) {
                addMessage(errorMessage, 'bot');
            }
        } catch (error) {
            loading.stop();
            loading.row.remove();
            addMessage('The chatbot could not connect to the server. Please try again.', 'bot');
        } finally {
            setLoading(false);
            input.focus();
        }
    });
})();
