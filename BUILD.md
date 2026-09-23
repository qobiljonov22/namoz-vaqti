# Build qanday ishlaydi? (Vue bilan solishtirish)

## Vue da
```bash
npm run build
# → dist/ ichida index.html + assets
```

## Bu loyihada (xuddi shunday)

### Hozir (Node yo‘q bo‘lsa)
Desktop/Cursor terminalda:
```powershell
cd Desktop\ramazon-taqvim
.\build.ps1
```
yoki `build.bat` ni ikki marta bosing.

Natija:
- `dist/` — Netlifyga tayyor HTML sayt
- Desktop: `namoz-vaqti-netlify.zip`

### Node o‘rnatilgandan keyin (Vue kabi)
1. [Node.js LTS](https://nodejs.org) o‘rnating
2. Keyin:
```bash
npm run build        # faqat dist/
npm run build:zip    # dist/ + Desktop zip
```

## Nima farqi?
| | Vue | Bu loyiha |
|---|-----|-----------|
| Manba | `.vue` komponentlar | `netlify-site/` (HTML/JS) |
| Build | Vite compile | `dist/` ga copy + zip |
| Natija | static HTML | static HTML |
| Netlify | `dist` publish | `dist` yoki zip |

## WordPress
`npm run build` WordPressni o‘zgartirmaydi.
WP: OpenServer → `http://ramazon-taqvim.local`
Netlify: faqat `dist/` / zip (static)

## Netlifyga qo‘yish
1. `.\build.ps1` ishga tushiring
2. Netlify → Deploy manually
3. `namoz-vaqti-netlify.zip` ni tashlang
