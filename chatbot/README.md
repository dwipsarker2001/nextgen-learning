# NextGen Course Chatbot

Project name: nextgen-learning

## Purpose

The NextGen Course Chatbot answers student questions using course-related content already stored in the nextgen-learning database. It retrieves relevant course, lecture, and topic records first, sends only that limited context to an OpenRouter model, and returns a concise answer.

## Files And Folders Added

- `chatbot/config.php`
- `chatbot/course_context.php`
- `chatbot/smalltalk.php`
- `chatbot/openrouter_client.php`
- `chatbot/api.php`
- `chatbot/stream.php`
- `chatbot/index.php`
- `chatbot/widget.php`
- `chatbot/.htaccess`
- `chatbot/assets/chatbot.css`
- `chatbot/assets/chatbot.js`
- `chatbot/assets/home-widget.css`
- `chatbot/assets/home-widget.js`
- `chatbot/README.md`

## Existing Files Modified

- `layouts/website.php`, `layouts/admin.php`, `layouts/student.php`: each includes `chatbot/widget.php` near the end of `<body>`, so the chatbot appears as a floating launcher on every public, student, and admin page.

## Database Tables And Columns Used

- `courses`: `id`, `title`, `short_desc`, `description`, `duration`, `price`, `total_lectures`, `language`, `instructor_id`, `status`, `upcoming`, `updated_at`
- `lectures`: `id`, `course_id`, `title`
- `topics`: `id`, `lecture_id`, `course_id`, `title`, `duration`, `price`
- `users`: `id`, `first_name`, `last_name`

## Chatbot Flow

1. The student opens the chatbot widget (floating launcher on every page) or `chatbot/index.php`.
2. The browser streams the question to `chatbot/stream.php` (a plain JSON endpoint, `chatbot/api.php`, is also available for non-streaming use).
3. The endpoint sanitizes and limits the question text.
4. Greetings and small talk (`chatbot/smalltalk.php`) get an instant canned reply without touching the database or the model.
5. Otherwise, `course_context.php` extracts keywords and uses prepared SQL statements to find matching course, lecture, and topic content, falling back to a general course overview when nothing matches.
6. The matched database rows are compressed into a short course context.
7. `openrouter_client.php` sends the question and context to OpenRouter's Chat Completions API (OpenAI-compatible) and streams the answer back token by token.
8. The response is typed into the chat UI as it arrives.

If no relevant database content is found, the endpoint returns a safe fallback message instead of asking the model to invent an answer.

## OpenRouter API Key Configuration

The key is not hardcoded. `chatbot/config.php` reads:

- `OPENROUTER_API_KEY`
- `OPENROUTER_MODEL`
- `OPENROUTER_ENDPOINT`
- `OPENROUTER_SITE_URL` (optional, sent as the `HTTP-Referer` header for OpenRouter's rankings)
- `OPENROUTER_SITE_TITLE` (optional, sent as the `X-Title` header)

Recommended setup is an environment variable:

```bash
OPENROUTER_API_KEY=your_openrouter_api_key_here
```

For local XAMPP testing, set the environment variable in your server or system environment before using the chatbot:

```bash
OPENROUTER_API_KEY=your_openrouter_api_key_here
OPENROUTER_MODEL=tencent/hy3:free
```

`chatbot/config.php` can also read a local `.env` file during development, but server environment variables are safer because this project sits under the web root. Do not commit real API keys.

## Security Measures Added

- User input is stripped of HTML tags, whitespace-normalized, and length-limited.
- Database reads use prepared statements with bound parameters.
- The API key stays server-side and is never returned in JSON or rendered in HTML.
- `chatbot/.htaccess` blocks direct browser access to a local `.env` file on Apache.
- Direct browser access to helper files returns `404`.
- OpenRouter errors are logged server-side while the browser receives a generic failure message.
- The chatbot prompt instructs the model to answer only from provided course context, and never to reveal the underlying AI vendor or model name.

## Setup Steps

1. Make sure the existing `nextgen` database is imported from `database/nextgen.sql`.
2. Confirm `includes/db.php` can connect to MySQL.
3. Configure `OPENROUTER_API_KEY` as a server environment variable or in `chatbot/.env`.
4. Optional: set `OPENROUTER_MODEL` if you want a different OpenRouter chat model.
5. Open `http://localhost/nextgen-learning/` to use the floating chatbot, available on every page.
6. Optional: open `http://localhost/nextgen-learning/chatbot/` to use the standalone chatbot page.

## Testing Steps

1. Ask: `hi` / `how are you` and confirm it replies with small talk instead of course data.
2. Ask: `What can I learn in the Python course?`
3. Ask: `Is there an HTML lesson?`
4. Ask: `Which courses are free?`
5. Ask an unrelated question and confirm the chatbot says the course database does not include that information.
6. Temporarily remove the API key and confirm the UI shows a generic setup/error message without exposing secrets.

## Notes For Future Developers

- The chatbot is intentionally isolated in `chatbot/` to avoid changing legacy code.
- The current widget is included from `layouts/website.php`, `layouts/admin.php`, and `layouts/student.php`, so any new page under those layouts gets it automatically. Admin/student layouts set `$ngChatBasePath = '../'` before including `chatbot/widget.php` since those pages live one directory below the site root.
- Course relevance is currently keyword-based for raw PHP compatibility. A future improvement could add full-text indexes or embeddings.
- Keep prompt context small. Do not send full database dumps to the model.
- Keep using prepared statements for any new database filters.
- OpenRouter can be swapped to a different model by changing `OPENROUTER_MODEL`; the client code (`openrouter_client.php`) is OpenAI-compatible and does not need to change for most models.
