/*
 * course-widget.js
 * Chat widget variant for the course watch page.
 * - Posts messages as JSON to `chatbot/api.php` and expects a full JSON reply.
 * - Does not stream token-by-token; it shows the complete answer after the request.
 * - If present, `window._ngCourseId` is included in the request to scope answers.
 */
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

    const basePath = widget.dataset.base || '';
    const courseId = window._ngCourseId || null;

    /**
     * Convert Markdown text to safe HTML for chat bubble rendering.
     * Behavior mirrors `home-widget.js` parseMarkdown implementation so messages
     * render consistently across widget variants.
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
     * Append a chat message row (user or bot) to the widget and scroll to the bottom.
     * Same DOM structure as the floating widget so styles match.
     */
    function addMessage(text, type) {
        const row = document.createElement('div');
        row.className = 'ng-chat-message ' + type;

        const avatar = document.createElement('div');
        avatar.className = 'ng-chat-avatar';
        avatar.innerHTML = type === 'user' ? '<i class="bi bi-person-fill"></i>' : '<i class="bi bi-robot"></i>';

        const bubble = document.createElement('div');
        bubble.className = 'ng-chat-bubble';
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

    // On submit: send message (and optional course_id) to `chatbot/api.php`.
    // The server returns a JSON response with `success` and `answer` fields.
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

            const response = await fetch(basePath + 'chatbot/api.php', {
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
            console.error('[Chatbot error]', error);
            loadingRow.remove();
            addMessage('The chatbot could not connect to the server. Please try again.', 'bot');
        } finally {
            setLoading(false);
            input.focus();
        }
    });
})();
