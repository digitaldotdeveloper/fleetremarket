# Fleet Remarketing website — handover notes

Static site: no server, database or build step. Upload the folder to any host (GitHub Pages, Netlify, cPanel…). Note: the forms need PHP, so they work on the hosting (or a local PHP server), not when `index.html` is opened as a plain file.

## Upload to cPanel
1. cPanel → File Manager → `public_html` → **Upload** the zip, then **Extract** it there. `index.html` must end up directly inside `public_html`.
2. Point the domain `fleetremarket.com` at this hosting (your host does this when the domain and hosting are in the same account) and enable the free SSL certificate (cPanel → SSL/TLS Status → Run AutoSSL).
3. Create the sender mailbox `noreply@fleetremarket.com` (cPanel → Email Accounts). Any password; it is never read.
4. Test the Contact form on the live site. The email arrives at the Gmail inbox.
5. Google Analytics → Admin → Data streams → Website → edit the URL to `https://fleetremarket.com`. Submit the sitemap at https://search.google.com/search-console (same Gmail): `https://fleetremarket.com/sitemap.xml`.

## Files
- `sitemap.xml`, `robots.txt`, `assets/og.jpg` — search engines and the preview image shown when the link is shared on WhatsApp / Facebook / LinkedIn.
- `index.html` + `assets/` — the website.
- `send.php` — receives the two forms and emails them. `config.php` — its settings. `.htaccess` — keeps `config.php` private.
- The host must support PHP 8 (any cPanel plan does).

## Forms — delivered by email from your own server
Edit `config.php`:

```php
'to_email'   => 'remarketingf@gmail.com',      // where leads are delivered
'from_email' => 'noreply@fleetremarket.com',   // an address on the SITE'S OWN domain (change to your domain)
'transport'  => 'mail',                        // 'mail' on cPanel; 'smtp' for local testing or if mail lands in spam
```

- Both forms (hero "Get Started" and the Contact form) post to `send.php`. The email contains a table of the fields; Reply-To is the visitor, so replying from Gmail goes straight to the dealer.
- Spam protection: hidden honeypot field, header-injection guard, max 10 submissions per IP per hour, no visible captcha.
- `from_email` is already set to `noreply@fleetremarket.com`; it must be on the site's own domain so the host's SPF/DKIM signatures match. Create that mailbox in cPanel → Email Accounts (it never needs to be read; it only has to exist so the mail server accepts the sender).

### Test after upload
Submit the Contact form on the live site. The email should arrive within a minute. If it does not: cPanel → Email Deliverability must show SPF and DKIM as valid for the domain; or switch `transport` to `'smtp'` (below).

### Local overrides (optional)
If a file named `config.local.php` exists next to `config.php`, its values override `config.php`. Useful for testing with different settings without touching the main file. It is blocked from the web by `.htaccess` like `config.php`.

### SMTP transport (local testing, or fallback)
In the Gmail account: Google Account → Security → 2-Step Verification → App passwords → create one named "Website form". Put it in `config.php` → `smtp.pass` and set `transport` to `'smtp'`. Mail is then sent through Gmail itself from `remarketingf@gmail.com`, which Gmail never marks as spam. This also works on a local PHP server (`php -S localhost:8080` in the site folder).

## Keeping form emails out of spam (do this once in Gmail)
1. Open the first form email (check Spam if needed) and click **Report not spam**.
2. Gmail → Settings → Filters → Create filter → From: `noreply@<your domain>` → check **Never send it to Spam** and **Star it**.

## Website traffic — Google Analytics 4
1. Sign in to https://analytics.google.com with the Gmail above → Create property "Fleet Remarketing" → Web data stream with the site URL.
2. Copy the Measurement ID (`G-…`) into `gaId` in the `SITE SETTINGS` block at the top of `index.html`. Traffic, sources, pages and form submissions (event `generate_lead`) appear in Reports within a day.
3. Optional: https://search.google.com/search-console with the same Gmail to see Google search traffic.
