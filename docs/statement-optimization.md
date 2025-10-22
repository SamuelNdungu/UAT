Statement generation optimization

What I changed:
- Dompdf options: disabled remote fetching, enabled font subsetting, set DPI to 96, set default font to DejaVu Sans.
- StatementService will attempt to download any remote company logo once into `storage/app/public/logos/` and pass a `file://` path to the view. Dompdf can use local file URLs which avoids remote HTTP fetches.
- The Blade view prefers a `company_logo_local` file URL when present.

Recommendations to get best results:
1. Ensure `storage/app/public/logos` exists and is writable by the web user.
2. Cache Blade views for production: `php artisan view:cache`.
3. Use the database or admin page to store company logos as `storage/logos/your-logo.png` and avoid remote URLs if possible.
4. If generation is still slow for large batches, consider implementing the async queued job (GenerateStatementJob) to perform generation in background.

Quick test commands (PowerShell):

# Ensure storage link exists
php artisan storage:link

# Cache views in production
php artisan view:cache

# Run a quick generation test script (provided in scripts/)
php scripts/generate_statement.php

# If you need an async job later, create job table and worker
php artisan queue:table
php artisan migrate
php artisan queue:work --tries=3
