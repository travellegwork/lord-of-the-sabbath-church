# Lord of the Sabbath Church

Initial website for **lordofthesabbath.church**.

## Local preview

Because the Bible reader loads local JSON data, serve the folder with a local web server rather than opening `index.html` directly.

```bash
python3 -m http.server 8080
```

Then open `http://localhost:8080`.

## Included

- Responsive church website and navigation
- Daily KJV verse and Joshua 1:8 motto
- SOAP study fields retained for the current browser session
- Complete 66-book KJV chapter reader using public-domain 1769 text
- Ten Commandments, Sabbath, Feasts of the LORD, salvation, devotional, About and Give pages

Email delivery, multilingual Bible packs, accounts, subscriptions and payment processing are intentionally not activated until their secure services are selected and connected.

## Bible data

The public-domain KJV data is sourced from the `kjv` npm package (`kjv@1.0.0`), whose included license declares the text public domain.
