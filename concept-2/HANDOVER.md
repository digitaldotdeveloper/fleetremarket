# Fleet Remarketing website — handover notes

Static site: no server, database or build step. Upload the folder to any host (GitHub Pages, Netlify, cPanel…). Note: the forms need PHP, so they work on the hosting (or a local PHP server), not when `index.html` is opened as a plain file.

## Upload to cPanel
1. The domain currently serves the old website. In cPanel → File Manager → `public_html`, select everything and **Compress** it to e.g. `old-site-backup.zip`, move that zip out of `public_html` (or download it), then delete the old files. If the old site was WordPress, also drop its database later from cPanel → MySQL Databases (optional).
2. Still in `public_html`: **Upload** the new zip, then **Extract** it. `index.html` must end up directly inside `public_html`.
3. Point the domain `fleetremarket.com` at this hosting (your host does this when the domain and hosting are in the same account) and enable the free SSL certificate (cPanel → SSL/TLS Status → Run AutoSSL).
4. Create the sender mailbox `noreply@fleetremarket.com` (cPanel → Email Accounts). Any password; it is never read.
5. Test the Contact form on the live site. The email arrives at the Gmail inbox.
6. Google Analytics → Admin → Data streams → Website → edit the URL to `https://fleetremarket.com`. Submit the sitemap at https://search.google.com/search-console (same Gmail): `https://fleetremarket.com/sitemap.xml`.

## Files
- `sitemap.xml`, `robots.txt`, `assets/og.jpg` — search engines and the preview image shown when the link is shared on WhatsApp / Facebook / LinkedIn.
- `index.html` + `assets/` — the website. `careers.html` — the Careers page (linked from the footer, opens in a new tab).
- Dealer app store links: paste them into `appStoreUrl` / `playStoreUrl` in the `SITE SETTINGS` block at the top of `index.html`. Until then the badges are inactive.
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

## Website traffic — Google Analytics 4 (already installed)
1. The tracking code is already in `index.html` and `careers.html` (`gaId: 'G-J0QYY2QFH0'` in the `SITE SETTINGS` block). Nothing to install; it starts counting as soon as the site is live.
2. To see the traffic: sign in to https://analytics.google.com with the Google account that owns this property → Reports. **Realtime** shows visitors within seconds; full reports (sources, pages, devices, cities) fill in within 24 hours. Each form submission is recorded as the event `generate_lead`.
3. To give someone else access: Admin → Property access management → **+** → their Gmail → role Viewer (or Administrator).
4. Once the domain is live: Admin → Data streams → Website → set the URL to `https://fleetremarket.com` (cosmetic; tracking works without it).
5. Optional: https://search.google.com/search-console with the same Gmail to see Google search traffic.
