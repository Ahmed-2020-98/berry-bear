# Berry Bear — Laravel

The Berry Bear site (originally a WordPress theme) rebuilt in Laravel, with a custom Arabic RTL admin panel at `/admin`.

## Structure

| Part | Location |
|---|---|
| Public site (same design as the WP theme) | `resources/views/site/*`, `resources/views/components/site/*`, assets in `public/theme/` |
| Admin panel (Blade + Tailwind, no packages) | `resources/views/admin/*`, `app/Http/Controllers/Admin/*`, `resources/{css,js}/admin.*` |
| Editable page sections + settings (schema) | `config/content.php` |
| Original theme copy (fallback defaults) | `config/content_defaults.php` |
| WordPress import | `app/Console/Commands/ImportWordPress.php` |

### Adding a new editable field
Add it to the page in `config/content.php` (`[name, label, type, extra]`), then read it in the view:
`{{ content('home', 'my_field') }}` or `{{ setting('my_setting') }}`. The admin form is generated automatically.

## Roles
- **مدير (admin):** everything, including users, site settings and the menu.
- **محرر (editor):** products, collections, blog, FAQs, testimonials, extra pages, page sections, messages.

## Local development
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # prints the admin password if ADMIN_PASSWORD is empty
php artisan storage:link
npm run build                       # or: npm run dev
php artisan serve                   # or: herd link berrybear  → http://berrybear.test
php artisan test
```

## Importing the WordPress content
Start the site in Local, then set the `WP_*` variables in `.env`
(`WP_DB_SOCKET` = Local's MySQL socket, found under
`~/Library/Application Support/Local/run/<id>/mysql/mysqld.sock`).
```bash
php artisan berry:import-wp            # safe to re-run (upserts by WordPress ID)
php artisan berry:import-wp --fresh    # wipe catalog/blog/menu first
```
It imports products (with draft status), blog posts and their categories, FAQs, testimonials, extra pages, every ACF page field and setting, the header menu, the logo, Flamingo messages and every image, which it copies to `storage/app/public/uploads/wp`.

## Media library

`/admin/media` (sidebar → مكتبة الوسائط) is the central image store: multi-file drag & drop upload, search, details (type, dimensions, size, date, alt text, where the image is used) and delete.

- Every image field in the panel (products, posts, page content, settings, repeater rows) and the rich-text editor's image button open the same picker modal (`resources/js/media-library.js`). It lets you choose an existing image or upload a new one there.
- A field stores the library file's path (`uploads/library/YYYY/MM/…`), so choosing an image never copies the file. Re-uploading identical bytes returns the existing record (sha1 `hash`).
- Accepted: JPG, PNG, WEBP, GIF up to 6 MB (`App\Support\Media::IMAGE_MIMES` / `MAX_KB`). SVG is refused on purpose.
- Video/PDF fields (e.g. the preloader video) still use a plain upload input.
- `php artisan berry:media-sync` registers images that are already under `storage/…/uploads` (WordPress import, older uploads). It is safe to re-run. The WordPress importer registers its files automatically.

## Deploying on Hostinger (shared hosting)
1. Create a MySQL database in hPanel.
2. Upload the project **outside** `public_html` (e.g. `~/berrybear`), including `vendor/` (or run `composer install --no-dev -o` over SSH) and `public/build/` (committed — Node isn't needed on the server).
3. Point the domain at the `public` folder. Either set the document root in hPanel, or replace `public_html` with a symlink: `ln -s ~/berrybear/public ~/public_html`.
4. Create `.env` with production values:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://berrybear.sa
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
   MAIL_MAILER=smtp  MAIL_HOST=smtp.hostinger.com  MAIL_PORT=465  MAIL_SCHEME=smtps
   MAIL_USERNAME=no-reply@berrybear.sa  MAIL_PASSWORD=...
   MAIL_FROM_ADDRESS=no-reply@berrybear.sa
   ```
5. Run:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. Move the content over. Either run the import against a copy of the WordPress database, or export the local Laravel database and import it, and upload `storage/app/public/uploads`.
