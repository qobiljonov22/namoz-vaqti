#!/usr/bin/env node
/**
 * Vue'dagi `npm run build` o'xshashi:
 * 1) netlify-site → dist/ (static HTML)
 * 2) dist → Desktop zip (Netlifyga yuklash uchun)
 *
 * Ishlatish:
 *   npm run build
 *   npm run build:zip
 */

import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { execSync } from "node:child_process";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const src = path.join(root, "netlify-site");
const dist = path.join(root, "dist");
const wantZip = process.argv.includes("--zip") || process.env.ZIP === "1";

function rmDir(dir) {
  if (fs.existsSync(dir)) {
    fs.rmSync(dir, { recursive: true, force: true });
  }
}

function copyDir(from, to) {
  fs.mkdirSync(to, { recursive: true });
  for (const entry of fs.readdirSync(from, { withFileTypes: true })) {
    if (entry.name === "README.md") continue;
    const a = path.join(from, entry.name);
    const b = path.join(to, entry.name);
    if (entry.isDirectory()) copyDir(a, b);
    else fs.copyFileSync(a, b);
  }
}

function zipDist() {
  const desktop = path.join(process.env.USERPROFILE || root, "Desktop");
  const zipPath = path.join(desktop, "namoz-vaqti-netlify.zip");
  if (fs.existsSync(zipPath)) fs.unlinkSync(zipPath);

  if (process.platform === "win32") {
    const ps = `Compress-Archive -Path '${dist.replace(/'/g, "''")}\\*' -DestinationPath '${zipPath.replace(/'/g, "''")}' -Force`;
    execSync(`powershell -NoProfile -Command "${ps}"`, { stdio: "inherit" });
  } else {
    execSync(`cd "${dist}" && zip -r "${zipPath}" .`, { stdio: "inherit" });
  }
  return zipPath;
}

console.log("▶ Building static site (HTML)...");
if (!fs.existsSync(src)) {
  console.error("✗ netlify-site/ topilmadi");
  process.exit(1);
}

rmDir(dist);
copyDir(src, dist);

const files = [];
function walk(dir, base = "") {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const rel = path.join(base, entry.name);
    if (entry.isDirectory()) walk(path.join(dir, entry.name), rel);
    else files.push(rel.replace(/\\/g, "/"));
  }
}
walk(dist);

console.log(`✓ dist/ tayyor (${files.length} fayl)`);
files.forEach((f) => console.log(`  - ${f}`));

if (wantZip) {
  console.log("▶ Zip yaratilmoqda...");
  const zipPath = zipDist();
  const size = fs.statSync(zipPath).size;
  console.log(`✓ Zip: ${zipPath} (${Math.round(size / 1024)} KB)`);
}

console.log("\nNetlify: dist/ papkasini yoki zip ni yuklang.");
console.log("WordPress: OpenServer (ramazon-taqvim.local) o‘zgarishsiz qoladi.");
