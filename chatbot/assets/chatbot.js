/*
 * chatbot.js
 * Full-page chatbot behavior for `chatbot/index.php`.
 * - Uses `stream.php` via Fetch + ReadableStream to display streaming answers.
 * - Uses different DOM IDs/classes from the widget variants (chatForm, chatMessages).
 */
(function () {
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');


    /**
     * Convert Markdown text to safe HTML for chat bubble rendering.
     * Same implementation as the widget variants to ensure consistent output.
     */
    function parseMarkdown(text) {
        if (!text) return '';

        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Code blocks
        html = html.replace(/```([\s\S]*?)```/g, function (m, code) {
            let cleanCode = code.trim().replace(/^[a-zA-Z0-9_\-\+]+\n/, '');
            return '<pre><code>' + cleanCode + '</code></pre>';
        });

        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Bold
        html = html.replace(/\*\*([\s\S]+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__([\s\S]+?)__/g, '<strong>$1</strong>');

        // Italic
        html = html.replace(/(?<!\*)\*([^\*\n]+?)\*(?!\*)/g, '<em>$1</em>');
        html = html.replace(/(?<!_)_([^_\n]+?)_(?!_)/g, '<em>$1</em>');

        // Links
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

        // Headings
        html = html.replace(/^### (.*$)/gim, '<h4>$1</h4>');
        html = html.replace(/^## (.*$)/gim, '<h3>$1</h3>');
        html = html.replace(/^# (.*$)/gim, '<h2>$1</h2>');

        // Lists
        const lines = html.split('\n');
        let inList = false;
        let result = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            let unorderedMatch = line.match(/^\s*[\-\*\•]\s+(.*)/);
            let orderedMatch = line.match(/^\s*(\d+)[\.\)]\s+(.*)/);

            if (unorderedMatch) {
                if (inList && inList !== 'ul') {
                    result.push('</' + inList + '>');
                    inList = false;
                }
                if (!inList) {
                    result.push('<ul class="chat-markdown-list">');
                    inList = 'ul';
                }
                result.push('<li>' + unorderedMatch[1] + '</li>');
            } else if (orderedMatch) {
                if (inList && inList !== 'ol') {
                    result.push('</' + inList + '>');
                    inList = false;
                }
                if (!inList) {
                    result.push('<ol class="chat-markdown-list">');
                    inList = 'ol';
                }
                result.push('<li>' + orderedMatch[2] + '</li>');
            } else {
                if (inList) {
                    result.push('</' + inList + '>');
                    inList = false;
                }
                result.push(line);
            }
        }
        if (inList) {
            result.push('</' + inList + '>');
        }
        html = result.join('\n');

        // Prevent extra <br> insertions around block-level HTML tags
        html = html.replace(/(<\/(?:h[1-6]|ul|ol|li|pre|p|blockquote)>)\n+/gi, '$1');
        html = html.replace(/\n+(<(?:h[1-6]|ul|ol|li|pre|p|blockquote)[^>]*>)/gi, '$1');
        html = html.replace(/<li[^>]*>\n+/gi, '<li>');
        html = html.replace(/\n+/g, '<br>');

        return html;
    }

    /**
     * Append a chat message row (user or bot) to the message container and scroll down.
     * Uses the full-page CSS classes `.chat-message` and `.bubble`.
     */
    function addMessage(text, type) {
        const row = document.createElement('div');
        row.className = 'chat-message ' + type;

        const avatar = document.createElement('div');
        avatar.className = 'chat-avatar';
        avatar.innerHTML = type === 'user' ? '<i class="bi bi-person-fill"></i>' : '<i class="bi bi-robot"></i>';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        if (type === 'bot') {
            bubble.innerHTML = parseMarkdown(text);
        } else {
            bubble.textContent = text;
        }

        if (type === 'user') {
            row.appendChild(bubble);
            row.appendChild(avatar);
        } else {
            row.appendChild(avatar);
            row.appendChild(bubble);
        }

        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;

        return row;
    }

    /**
     * Enable or disable the form inputs during a request.
     */
    function setLoading(isLoading) {
        const button = form.querySelector('button');
        button.disabled = isLoading;
        input.disabled = isLoading;
    }

    /**
     * Show a "Typing..." loading indicator in a bot message row.
     */
    function startLoadingMessage() {
        const row = addMessage('Typing...', 'bot');
        const bubble = row.querySelector('.bubble');
        bubble.classList.add('loading-bubble');

        return {
            row: row,
            stop: function () {},
        };
    }

    /**
     * Read the chatbot/stream.php SSE response and invoke `onDelta` with each token chunk.
     * Matches `home-widget.js` streaming behavior but uses relative paths appropriate
     * for the standalone page (calls `stream.php` in the same folder).
     */
    async function streamAnswer(message, onDelta) {
        const response = await fetch('stream.php', {
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
        let fullAnswer = '';

        try {
            const errorMessage = await streamAnswer(message, function (delta) {
                if (!answerBubble) {
                    loading.stop();
                    loading.row.remove();
                    answerBubble = addMessage('', 'bot').querySelector('.bubble');
                }

                fullAnswer += delta;
                answerBubble.innerHTML = parseMarkdown(fullAnswer);
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
