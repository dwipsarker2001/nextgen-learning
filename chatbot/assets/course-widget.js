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

    const courseId = window._ngCourseId || null;

    /**
     * Convert Markdown text to safe HTML for chat bubble rendering.
     */
    function parseMarkdown(text) {
        if (!text) return '';

        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Code blocks
        html = html.replace(/```([\s\S]*?)```/g, function (m, code) {
            return '<pre><code>' + code.trim() + '</code></pre>';
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
            let unorderedMatch = line.match(/^\s*[\-\*]\s+(.*)/);
            let orderedMatch = line.match(/^\s*(\d+)\.\s+(.*)/);

            if (unorderedMatch) {
                if (!inList) {
                    result.push('<ul class="chat-markdown-list">');
                    inList = 'ul';
                }
                result.push('<li>' + unorderedMatch[1] + '</li>');
            } else if (orderedMatch) {
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

        html = html.replace(/(<\/h[2-4]>|<\/ul>|<\/ol>|<\/pre>)\n/gi, '$1');
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    /**
     * Append a chat message row (user or bot) to the widget and scroll to the bottom.
     */
    function addMessage(text, type) {
        const row = document.createElement('div');
        row.className = 'ng-chat-message ' + type;

        const bubble = document.createElement('div');
        bubble.className = 'ng-chat-bubble';
        if (type === 'bot') {
            bubble.innerHTML = parseMarkdown(text);
        } else {
            bubble.textContent = text;
        }

        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;

        return row;
    }

    /**
     * Enable or disable the form inputs during a request.
     */
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

        const loadingRow = addMessage('Typing...', 'bot');
        const bubble = loadingRow.querySelector('.ng-chat-bubble');
        if (bubble) {
            bubble.classList.add('loading-bubble');
        }

        try {
            const body = { message: message };
            if (courseId) {
                body.course_id = courseId;
            }

            const response = await fetch('chatbot/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
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
