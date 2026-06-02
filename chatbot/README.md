# Groq AI Course Chatbot

Project name: nextgen-learning

## Purpose

The Groq AI Course Chatbot answers student questions using course-related content already stored in the nextgen-learning database. It retrieves relevant course, lecture, and topic records first, sends only that limited context to Groq, and returns a concise answer.

## Files And Folders Added

- `chatbot/config.php`
- `chatbot/course_context.php`
- `chatbot/groq_client.php`
- `chatbot/api.php`
- `chatbot/index.php`
- `chatbot/widget.php`
- `chatbot/.htaccess`
- `chatbot/assets/chatbot.css`
- `chatbot/assets/chatbot.js`
- `chatbot/assets/home-widget.css`
- `chatbot/assets/home-widget.js`
- `chatbot/README.md`

## Existing Files Modified

- `index.php`: includes `chatbot/widget.php` near the end of the homepage content so the chatbot appears as a floating launcher on the home page.

## Database Tables And Columns Used

- `courses`: `id`, `title`, `short_desc`, `description`, `duration`, `price`, `total_lectures`, `language`, `instructor_id`, `status`, `upcoming`, `updated_at`
- `lectures`: `id`, `course_id`, `title`
- `topics`: `id`, `lecture_id`, `course_id`, `title`, `duration`, `price`
- `users`: `id`, `first_name`, `last_name`

## Chatbot Flow

1. The student opens `chatbot/index.php`.
2. The browser sends the question to `chatbot/api.php`.
3. `api.php` sanitizes and limits the question text.
4. `course_context.php` extracts keywords and uses prepared SQL statements to find matching course, lecture, and topic content.
5. The matched database rows are compressed into a short course context.
6. `groq_client.php` sends the question and context to Groq's Chat Completions API.
7. The response is returned as JSON and displayed in the chat UI.

On the home page, `index.php` includes `chatbot/widget.php`, which renders a floating chat button and posts questions to the same `chatbot/api.php` endpoint.

If no relevant database content is found, the endpoint returns a safe fallback message instead of asking Groq to invent an answer.

## Groq API Key Configuration

The key is not hardcoded. `chatbot/config.php` reads:

- `GROQ_API_KEY`
- `GROQ_MODEL`
- `GROQ_ENDPOINT`

Recommended setup is an environment variable:

```bash
GROQ_API_KEY=your_groq_api_key_here
```

For local XAMPP testing, set the environment variable in your server or system environment before using the chatbot:

```bash
GROQ_API_KEY=your_groq_api_key_here
GROQ_MODEL=llama-3.1-8b-instant
```

`chatbot/config.php` can also read a local `.env` file during development, but server environment variables are safer because this project sits under the web root. Do not commit real API keys.

## Security Measures Added

- User input is stripped of HTML tags, whitespace-normalized, and length-limited.
- Database reads use prepared statements with bound parameters.
- The API key stays server-side and is never returned in JSON or rendered in HTML.
- `chatbot/.htaccess` blocks direct browser access to a local `.env` file on Apache.
- Direct browser access to helper files returns `404`.
- Groq errors are logged server-side while the browser receives a generic failure message.
- The chatbot prompt instructs Groq to answer only from provided course context.

## Setup Steps

1. Make sure the existing `nextgen` database is imported from `database/nextgen.sql`.
2. Confirm `includes/db.php` can connect to MySQL.
3. Configure `GROQ_API_KEY` as a server environment variable or in `chatbot/.env`.
4. Optional: set `GROQ_MODEL` if you want a different Groq chat model.
5. Open `http://localhost/nextgen-learning/` to use the floating home-page chatbot.
6. Optional: open `http://localhost/nextgen-learning/chatbot/` to use the standalone chatbot page.

## Testing Steps

1. Ask: `What can I learn in the Python course?`
2. Ask: `Is there an HTML lesson?`
3. Ask: `Which courses are free?`
4. Ask an unrelated question and confirm the chatbot says the course database does not include that information.
5. Temporarily remove the API key and confirm the UI shows a generic setup/error message without exposing secrets.

## Notes For Future Developers

- The chatbot is intentionally isolated in `chatbot/` to avoid changing legacy code.
- To embed it site-wide, add links or includes to existing layout files only after approval.
- The current home-page widget is included only from `index.php`; it is not site-wide.
- Course relevance is currently keyword-based for raw PHP compatibility. A future improvement could add full-text indexes or embeddings.
- Keep prompt context small. Do not send full database dumps to Groq.
- Keep using prepared statements for any new database filters.
