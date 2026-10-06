# Fleet Remarketing website — handover notes

Static site: no server, database or build step. Upload the folder to any host (GitHub Pages, Netlify, cPanel…). Note: the forms only send when the site is served from a real URL (http/https), not when `index.html` is opened as a local file.

## Settings (one place)
Open `index.html` and find `SITE SETTINGS` near the top:

```js
window.SITE={
  formEmail:'remarketingf@gmail.com',   // forms are delivered here
  gaId:''                               // Google Analytics 4 ID, e.g. 'G-XXXXXXXXXX'
};
```

## Forms (free, no server) — FormSubmit.co
- Both forms (hero "Get Started" and the Contact form) POST to `https://formsubmit.co/ajax/<formEmail>`.
- One-time activation: the first submission sends an "Activate form" email to `formEmail`. Click **Activate** once; after that every submission lands in the inbox with a table of the fields and Reply-To set to the visitor's email.
- Spam protection: hidden honeypot field (`_honey`), FormSubmit's own filtering. No visible captcha.
- To change the destination address: edit `formEmail`, submit the form once, and activate again from the new inbox.
- Optional privacy: FormSubmit emails you a random alias string after activation. Put that string in `formEmail` instead of the real address so scrapers can't read the email from the page source.

## Keeping form emails out of spam (do this once in Gmail)
1. Find the first FormSubmit email (check Spam if needed) and click **Report not spam**.
2. Gmail → Settings → Filters → Create filter → From: `formsubmit.co` → check **Never send it to Spam** and **Star it**.
3. Add `noreply@formsubmit.co` as a Google Contact.

## Website traffic — Google Analytics 4
1. Sign in to https://analytics.google.com with the Gmail above → Create property "Fleet Remarketing" → Web data stream with the site URL.
2. Copy the Measurement ID (`G-…`) into `gaId` in `index.html`. Traffic, sources, pages and form submissions (event `generate_lead`) appear in Reports within a day.
3. Optional: https://search.google.com/search-console with the same Gmail to see Google search traffic.
