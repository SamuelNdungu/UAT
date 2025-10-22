Async statement generation (GenerateStatementJob)

What was added:
- `app/Jobs/GenerateStatementJob.php` — a queued job which generates a statement PDF for a customer, attaches it to the customer record and optionally sends an email.
- `HasDataInterrogation::send_statement_of_account(..., async = true)` now dispatches the job and returns immediately with a queued status.

How to enable and test locally (PowerShell):
1. Configure queue driver in .env (database recommended for local):

   QUEUE_CONNECTION=database

2. Generate the queue table and migrate:

   php artisan queue:table
   php artisan migrate

3. Start a queue worker in a terminal:

   php artisan queue:work --tries=3

4. Use the AI UI to request a statement as usual. The assistant will reply that the generation is queued and the worker will process the job asynchronously.

Logs:
- The job logs status to `storage/logs/laravel.log` (start, success, failure).

Notes:
- You can change retry behavior, timeouts and queue prioritization via the job class and queue config.
- Consider adding a database-backed audit log for confirm actions and job outcomes if you need an audit trail.
