# Netlify / Vercel (static)

Bu papka **Netlify** va **Vercel** uchun static sayt.

## Deploy
- **Netlify:** `netlify-site` papkasini yoki `namoz-vaqti-netlify.zip` ni yuklang
- **Vercel:** Root Directory = `netlify-site` (yoki shu papkani import)

## Sahifalar
- `/` — Ramazon Taqvim
- `/namoz.html` — Namoz vaqti
- `/admin.html` yoki `/wp-admin` — Admin panel (static)

## Vercel (GitHub)
1. Import: `qobiljonov22/namoz-vaqti`
2. Framework Preset: **Other**
3. Root Directory: bo‘sh qoldiring (yoki `netlify-site`)
4. Build Command: avtomatik (`vercel.json` ichida)
5. Output Directory: `netlify-site`

Agar Root Directory = `netlify-site` bo‘lsa — Output Directory ni bo‘sh qoldiring.

## Admin
Login: `admin` / `admin123` — URL: `/admin` yoki `/wp-admin`

WordPress (`wp-admin` PHP) Netlify/Vercelda ishlamaydi.
Local: `http://ramazon-taqvim.local/wp-admin`
