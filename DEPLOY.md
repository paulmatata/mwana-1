# Deploying Mwana — GitHub + Render (Docker) + Aiven MySQL

## 1. Push to GitHub

```bash
cd mwana
git init
git add .
git commit -m "Initial deploy
git branch -M main
git remote add origin https://github.com/<your-username>/mwana.git
git push -u origin main
```

Make sure `.env` is **not** committed (it's in `.gitignore` by default in a fresh
Laravel install — double check before pushing, since it would contain real secrets
once you fill it in for production).

## 2. Set up the Aiven MySQL database

1. In the Aiven console, create a MySQL service (any plan — you can resize later).
2. Once it's running, go to the service's **Overview** tab and note: Host, Port,
   Database name (default is usually `defaultdb`), User, Password.
3. Still on the Overview tab, find **CA Certificate** and copy its full contents
   (starts with `-----BEGIN CERTIFICATE-----`). You'll paste this into Render as
   `DB_SSL_CA_CONTENT` in the next step — this is what lets Mwana connect over
   the SSL connection Aiven requires, with proper certificate verification.

## 3. Create the Render service

1. New → Web Service → connect your GitHub repo.
2. **Runtime: Docker** (Render will find the `Dockerfile` at the repo root automatically).
3. Instance type: whatever tier you're starting with — Starter is fine for a testing rollout.
4. Under **Environment**, add these variables:

   | Key | Value |
   |---|---|
   | `APP_NAME` | `Mwana` |
   | `APP_ENV` | `production` |
   | `APP_KEY` | *(see step 4 below)* |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | `https://<your-render-service>.onrender.com` |
   | `APP_TIMEZONE` | `Africa/Nairobi` |
   | `DB_CONNECTION` | `mysql` |
   | `DB_HOST` | *(from Aiven)* |
   | `DB_PORT` | *(from Aiven)* |
   | `DB_DATABASE` | *(from Aiven)* |
   | `DB_USERNAME` | *(from Aiven)* |
   | `DB_PASSWORD` | *(from Aiven)* |
   | `DB_SSL_CA_CONTENT` | *(the full CA certificate text from step 2.3)* |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `QUEUE_CONNECTION` | `database` |
   | `LOG_CHANNEL` | `stack` |
   | `LOG_LEVEL` | `error` |

   Render sets `PORT` itself — you don't need to add it.

## 4. Generate APP_KEY

Laravel's `APP_KEY` encrypts sessions and other sensitive data — it needs to be
stable across restarts, so generate it once and paste it into Render rather than
letting the container generate a fresh one on every boot (the entrypoint script
*will* auto-generate one if you skip this, but only as a fallback — sessions won't
survive a restart until you set this properly).

If you have PHP + Composer locally:
```bash
php artisan key:generate --show
```
This prints something like `base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=` —
paste the whole thing (including `base64:`) as the `APP_KEY` value in Render.

No local PHP available? Deploy once without `APP_KEY` set — the entrypoint's
fallback will generate one and print it to the Render logs. Copy it from there,
add it as `APP_KEY` in Render's environment variables, and redeploy so it's stable.

## 5. Deploy

Render builds the Docker image and starts the container automatically once you
create the service. On every boot, `docker/entrypoint.sh`:
1. Configures Apache to listen on Render's assigned port
2. Writes the Aiven CA cert from `DB_SSL_CA_CONTENT` (if you set it)
3. Runs `php artisan migrate --force` — your database schema comes up automatically
4. Runs `php artisan db:seed --force` — creates the Super Admin account if it
   doesn't already exist (safe to run on every boot, won't duplicate it)
5. Caches config/routes/views for performance, then starts Apache

## 6. First login

Once the deploy is live, visit your Render URL and log in with the seeded
Super Admin:
- Email: `admin@mwana.app`
- Password: `ChangeMe123!`

**Change this password immediately** — go to **Change Password** in the sidebar
once logged in (works for every role, including Super Admin).

## 7. Set up real email (required for Forgot Password to work)

By default, `MAIL_MAILER=log` just writes emails to Render's application logs —
nobody actually receives anything. Forgot Password won't be usable until you set
a real mail driver. Add these to Render's environment variables:

| Key | Example |
|---|---|
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | your provider's SMTP host |
| `MAIL_PORT` | `587` |
| `MAIL_USERNAME` | your SMTP username |
| `MAIL_PASSWORD` | your SMTP password / API key |
| `MAIL_ENCRYPTION` | `tls` |
| `MAIL_FROM_ADDRESS` | `no-reply@yourdomain.com` |
| `MAIL_FROM_NAME` | `Mwana` |

Fastest path for testing: a free-tier transactional email provider (Resend, Brevo,
Mailgun, SendGrid) — they issue SMTP credentials immediately, no custom domain
required to start. Gmail SMTP with an App Password also works for light testing.

## Troubleshooting: bulk import / spreadsheet errors ("firing errors everywhere")

Two likely causes, both worth checking:

1. **`composer.lock` out of sync with `composer.json`.** If `phpoffice/phpspreadsheet`
   was added to `composer.json` after the lock file was last generated, `composer install`
   (which strictly follows the lock file) silently skips it even though it's listed.
   Fix:
   ```bash
   composer require phpoffice/phpspreadsheet
   git add composer.json composer.lock
   git commit -m "Fix: re-lock phpoffice/phpspreadsheet"
   git push
   ```
2. **PHP upload/memory limits too small.** Fixed in `docker/php-overrides.ini`
   (bumped `upload_max_filesize`, `post_max_size`, and `memory_limit` — PHP's
   defaults are too small for Excel uploads and PhpSpreadsheet's parsing). Make
   sure this file is present and referenced in the Dockerfile before rebuilding.

If errors persist after both, check Render's build and runtime logs for the exact
error message — "Class ... not found" points to (1), anything mentioning
"allowed memory size" or "exceeds the maximum" points to (2) or a spreadsheet
that's still too large even with the raised limits.

## Iterating after today

Every push to your connected branch triggers a new Render build and deploy
automatically. Migrations run on every boot, so new migrations you add in future
phases will apply themselves — no manual migration step needed on your end.

## Known limitations for a same-day launch

- **Single container instance assumed.** If you scale to multiple Render
  instances later, running `migrate` on every boot of every instance could race.
  Fine for now; worth switching to a release-phase/pre-deploy hook instead once
  you scale beyond one instance.
- **No queue worker.** `QUEUE_CONNECTION=database` is set, but nothing in Mwana
  currently dispatches queued jobs, so this doesn't matter yet — only relevant if
  you add background jobs (e.g. sending SMS/email notifications) later.
- **No automated backups configured here.** Aiven offers managed backups —
  worth confirming they're enabled on your plan before real school data goes in.
