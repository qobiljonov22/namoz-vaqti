# Netlify deploy (static)

Bu papka **Netlify** uchun tayyor static sayt.

## Deploy
1. `namoz-vaqti-netlify.zip` ni Netlifyga yuklang
   yoki shu `netlify-site` papkasini drag & drop qiling
2. Build command: kerak emas (yoki `echo ready`)
3. Publish directory: `.` (zip ichidagi root)

## Sahifalar
- `/` yoki `index.html` — Ramazon Taqvim
- `/namoz.html` — Namoz vaqti

## WordPress
WordPress OpenServerda alohida ishlaydi:
`http://ramazon-taqvim.local`

Netlify PHP/WordPressni ishga tushirmaydi — shuning uchun static + Aladhan API (brauzerdan).
