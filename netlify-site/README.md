# Netlify / Vercel (static)

Bu papka **Netlify** va **Vercel** uchun static sayt.

## Deploy
- **Netlify:** `netlify-site` papkasini yoki `namoz-vaqti-netlify.zip` ni yuklang
- **Vercel:** Root Directory = `netlify-site` (yoki shu papkani import)

## Sahifalar
- `/` — Ramazon Taqvim
- `/namoz.html` — Namoz vaqti
- `/admin.html` yoki `/wp-admin` — Admin panel (static)

## Admin
Login: `admin` / `admin123`

WordPress (`wp-admin`) Netlify/Vercelda **ishlamaydi** (PHP/MySQL yo‘q).
Shu sabab `/wp-admin` → static `/admin.html` ga yo‘naltiriladi.

Local WordPress alohida:
`http://ramazon-taqvim.local/wp-admin`
