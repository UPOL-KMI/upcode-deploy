/**
 * Renders the brand lockup -- the iNF mark, the product's name and the department's -- to the PNG
 * the e-mail templates embed.
 *
 * **It is rendered by a real browser rather than drawn by hand**, because the lockup on the site is
 * an SVG next to two lines of IBM Plex Sans, and a hand-made copy of that would drift away from the
 * original the first time either changes. The paths below are copied from
 * `repos/web-next/components/brand/inf-logo.tsx` and the type is the same face the site loads from
 * Google Fonts, so what lands in the PNG is what a reader sees in the header of the application.
 *
 * Rasterised at twice the size it is displayed at, which is what makes it sharp on a phone: the
 * `<img>` in the template asks for 200 CSS pixels and this writes 400 real ones.
 *
 * Run from the compose repository:
 *
 *   node tools/brand-email-logo.mjs
 *
 * It needs the frontend's own dependencies (Playwright lives there), so it is invoked through
 * `repos/web-next`. Re-run it whenever the mark or the wording changes, and rebuild the `api`
 * image afterwards -- the PNG is baked into it.
 */

import { mkdir, writeFile } from "node:fs/promises";
import { createRequire } from "node:module";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = join(dirname(fileURLToPath(import.meta.url)), "..");

// Playwright is the frontend's dependency, not this repository's -- this repository has no
// package.json at all. Resolved against `repos/web-next` explicitly so the script runs from
// anywhere: an ESM `import` would look beside this file and find nothing.
//
// `@playwright/test` and not `playwright`: the former is what that repository actually depends on,
// and pnpm's strict layout links only declared dependencies. It re-exports the browser drivers.
const { chromium } = createRequire(join(ROOT, "repos", "web-next", "package.json"))(
  "@playwright/test",
);
const OUT = join(ROOT, "repos", "api", "www", "emails", "img", "upolnicek.png");

/** Displayed width in the e-mail, in CSS pixels. The file is written at twice this. */
const WIDTH = 200;
const SCALE = 2;

const BRAND = "#016BAB";
const INK = "#14181c";

const page = `<!doctype html>
<meta charset="utf-8">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@500;600&display=block" rel="stylesheet">
<style>
  html, body { margin: 0; background: #ffffff; }
  #lockup {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 2px;
    background: #ffffff;
    font-family: 'IBM Plex Sans', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
  }
  #lockup svg { height: 40px; width: auto; display: block; }
  .words { display: flex; flex-direction: column; line-height: 1.15; }
  .product { font-size: 20px; font-weight: 600; letter-spacing: -0.01em; color: ${INK}; }
  .department { font-size: 11px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: ${BRAND}; }
</style>
<div id="lockup">
  <svg viewBox="72 139 145 148" xmlns="http://www.w3.org/2000/svg">
    <path fill="${INK}" d="M206.53,197.18c4.42,0,8-3.58,8-8v-12.87h-31.99c-.09,0-.19.01-.28.02h-18.25c-4.42,0-8,3.58-8,8v44.7l27.1,55.28v-46.32h23.42c4.42,0,8-3.58,8-8v-12.87h-31.42v-19.94h23.42Z"/>
    <path fill="${INK}" d="M103.07,176.34h-27.1v99.93c0,4.42,3.58,8,8,8h11.1c4.42,0,8-3.58,8-8v-99.93Z"/>
    <circle fill="${BRAND}" cx="89.52" cy="155.96" r="15.46"/>
    <path fill="${BRAND}" d="M103.07,176.34h22.12c3.05,0,5.84,1.73,7.19,4.47l50.74,103.5h-22.12c-3.05,0-5.84-1.74-7.19-4.48l-50.74-103.49Z"/>
  </svg>
  <span class="words">
    <span class="product">UPolníček</span>
    <span class="department">Katedra informatiky</span>
  </span>
</div>`;

const browser = await chromium.launch();
const context = await browser.newContext({ deviceScaleFactor: SCALE });
const tab = await context.newPage();
await tab.setContent(page, { waitUntil: "networkidle" });
// `display: block` on the face means the browser waits for it rather than painting a fallback
// first, but the fonts API is the one thing here that can be slow -- so it is waited for by name.
await tab.evaluate(() => document.fonts.ready);

const lockup = tab.locator("#lockup");
const box = await lockup.boundingBox();
if (!box) throw new Error("the lockup did not render");

const png = await lockup.screenshot({ type: "png" });
await mkdir(dirname(OUT), { recursive: true });
await writeFile(OUT, png);
await browser.close();

const displayed = Math.round(box.width);
console.log(`Wrote ${OUT}`);
console.log(`  ${Math.round(box.width * SCALE)}x${Math.round(box.height * SCALE)} px, shown at ${displayed}x${Math.round(box.height)}`);
if (Math.abs(displayed - WIDTH) > 24) {
  console.log(`  note: the lockup is ${displayed}px wide, not the ${WIDTH}px the template assumes.`);
}
