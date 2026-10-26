# Branding

Visual rules for KISSJ pages, mails and PDFs, extracted from the Junák – český skaut graphic manual (v2.3).
The default event follows them strictly. Events with their own brand follow them loosely.

## Sources

- Graphic manual: `docs/SKAUT_graficky_manual.pdf` (not tracked, ask the maintainer for a copy).
- Official logos, fonts and templates: [brand Google Drive][drive].
  Logos sit in `1 | Logo a znak / 1 | Základní logo / Pro tisk a web`.
- Never redraw, trace or recolour a logo. Download the official file and only resize it.

## Default event: strict

The default event type is Junák's own registration surface.
Everything on it must look like an official Skaut product.

### Logo

- Use the basic logo with the SKAUT wordmark. In footers, rows and other wide slots use the horizontal variant.
- The ceremonial emblem (znak) is not a logo. Do not use it on registration pages.
- In KISSJ the default logo is `public/logo_skaut_white_bg_800.png`.
  It is the official horizontal logo on its white organic backing, scaled to 800 px wide.
  It works on light and dark backgrounds, in mails and in PDFs.
- `public/SKAUT_horizontalni_logo_250.png` has sharp white corners. Keep it only for existing PDF receipts.
- On a white background prefer the full-colour logo without backing.
  On dark, coloured or busy backgrounds always use a version with the white backing.
- Minimum on screen: horizontal logo 130 x 36 px, basic logo 40 px wide.
  Below that use the backing variant, which may shrink slightly further.
- Favicons and tiny icons use only the lily glyph from the SKAUT Bold font, at 16 x 16 or 32 x 32 px.
- Keep the protective zone around the logo free of text, images and other logos.
- Use raster files (PNG) for mails. Gmail and Outlook do not render SVG.

### Forbidden logo alterations

- Recolouring, gradients, shadows, outlines, glows or transparency.
- Stretching, rotating, cropping or changing the proportions.
- Removing the dog head, or making it lighter than the shield.
- Replacing the wordmark font, adding text or symbols, or merging the logo with another sign.
- Placing the logo on a background where it loses contrast without the white backing.

### Typography

- Titles: SKAUT Bold. It is a licensed webfont with uppercase glyphs only.
- Body text: TheMix, loaded from cdn.skauting.cz together with SKAUT Bold.
  Open Sans is the manual's fallback, not needed here.
- Do not set long text in capitals for emphasis. Emphasise at most a tenth of a text.

### Colours

Logo colours, used for the logo and for primary accents:

| Name | Hex |
|------|-----|
| Blue light | `#3979B5` |
| Blue | `#255C9E` |
| Blue dark | `#294885` |
| Yellow | `#F9B200` |

Palette 5 "Logo" extends them with `#FCC11E`, `#F49E00` and `#336CAA` for accents.

Base palettes. Pick one per surface and use at most three colours from it.

| Palette | Hex codes |
|---------|-----------|
| 1 Zklidňující (calm) | `#F6EBD8` `#ECDCAF` `#D3BFA0` `#657F6F` `#A4D2B2` `#87A1AE` `#BDD4DF` `#CDD3D6` |
| 2 Hřejivá (warm) | `#ECA038` `#A38456` `#EA614A` `#008836` `#20649B` `#6B96CA` `#A87A93` `#D19C95` |
| 3 Přírodní (natural) | `#D4B66D` `#B85637` `#A21F16` `#732813` `#5D4716` `#8D5F0F` `#48651D` `#5C748C` |

The vivid palette (Pestrá) is for signals and navigation only: alerts, status badges, error and success states.

| Palette | Hex codes |
|---------|-----------|
| Pestrá (vivid) | `#FFCC00` `#EE8027` `#E53434` `#A0067D` `#5E2281` `#172983` `#007BC2` `#89BA17` |

- White is the base surface. Avoid pure black, use a dark palette colour for text instead.
- The base theme uses the KISSJ tokens below.

### KISSJ tokens

The base theme sets these in `public/styles.css`. Branded events override them in `public/eventSpecificCss/`.
The mail layout (`src/Templates/translatable/emails/_mail-layout.twig`) carries the same main and link tokens.

| Token | Light | Dark | Source |
|-------|-------|------|--------|
| `--color-background` | `#ffffff` | `#0b1426` | White base surface. Dark is derived navy. |
| `--color-card-background` | `#f9f9f9` | `#14203a` | Neutral. Dark is derived navy. |
| `--color-input-background` | `#ffffff` | `#1c2a47` | Neutral. Dark is derived navy. |
| `--color-main` | `#255C9E` | `#6B96CA` | Light: logo blue. Dark: palette 2. |
| `--color-accent` | `#B85637` | `#F9B200` | Light: palette 3. Dark: logo yellow. |
| `--color-fading` (progress track) | `#6B96CA` | `#294885` | Light: palette 2. Dark: logo dark blue. |
| `--color-light` | `#3979B5` | `#3979B5` | Logo light blue. |
| `--color-dark` | `#294885` | `#BDD4DF` | Light: logo dark blue. Dark: palette 1. |
| `--color-grey` | `#636363` | `#888888` | Neutral, not from a palette. |
| `--color-stripe` (table rows) | `#ebf2f5` | `#0b1426` | Light: 30 % palette 1 `#BDD4DF` over white. Dark: page navy. |
| `--color-link` | `#172983` | `#BDD4DF` | Light: Pestrá. Dark: palette 1. |
| `--color-link-visited` | `#5E2281` | `#B98ED9` | Light: Pestrá. Dark: derived, tint of Pestrá `#5E2281`. |
| alert text | `#172983` | `#e8eef5` | Light: Pestrá. Dark: derived off-white. |

Alerts keep centred text, have a 4 px top strip in the hue and no shadow.
The hue is a Pestrá colour. The light tint is 18 % hue over 82 % `#ffffff`.
The dark tint is 28 % hue over 72 % `#14203a`.
For example `#007BC2` gives `#d1e7f4` (light) and `#0e3960` (dark).

| Alert | Hue (strip) | Light tint | Dark tint |
|-------|-------------|------------|-----------|
| info | `#007BC2` | `#d1e7f4` | `#0e3960` |
| success | `#89BA17` | `#eaf3d5` | `#354b30` |
| warning | `#FFCC00` | `#fff6d1` | `#56502a` |
| error | `#E53434` | `#fadada` | `#4f2638` |

The mobile browser bar (`theme-color`) is the main blue `#255C9E` in light and the page navy `#0b1426` in dark mode.

### Background

- Two tiles: `public/background_mozaika_light.svg` and `public/background_mozaika_dark.svg`.
- Each is a 400 px seamless square cut from the official coloured mozaika wallpaper.
  Fragments sit at 10 % strength on `#ffffff` (light) and `#0b1426` (dark).
- Source: `SKAUT_A3_mozaika_tapeta_barevna.svg` in the [brand wallpaper folder][tapeta]
  (`1wv8cYt5KdYutc9dJrTSrOd8XASB2sbWW`).
  Old Drive files need the `resourcekey` parameter for a direct download.
- The generator `bin/make-mozaika-tile.py` needs `python3` and `chromium` on the host.

Download the source to `/tmp/mozaika-src/barevna.svg` first, then run:

```
python3 -I bin/make-mozaika-tile.py /tmp/mozaika-src/barevna.svg public/background_mozaika_light.svg \
  --background '#ffffff' --strength 0.10 --x 160 --y 590 --size 400
python3 -I bin/make-mozaika-tile.py /tmp/mozaika-src/barevna.svg public/background_mozaika_dark.svg \
  --background '#0b1426' --strength 0.10 --x 160 --y 590 --size 400
```

- Regenerate instead of editing the SVGs by hand. Never recolour the fragments beyond the tint strength.

### Shapes

- Prefer rounded and organic shapes. Every layout has at least one rounded element.
- Prefer solid fills over outlines.

### Naming

- Informal text: "skaut", lower case.
- Formal text, legal notices and footers: "Junák – český skaut, z. s." with an en dash.

## Branded events: lax

Events with their own visual identity (Obrok, Navigamus, CEJ, Korbo and others) follow their own brand hub or manual.
Their rules win on their pages, mails and badges.

- The Skaut logo must still appear somewhere, far from the event logo. Never place the two logos as a pair.
  Event logos never replace the Skaut logo.
- PDF receipts already carry the Skaut logo. The web footer shows only the event logo, which is a known gap.
- The Skaut logo itself stays unaltered, with the same rules as on the default event.
- Event-specific CSS lives in `public/eventSpecificCss/`. Keep base styles neutral so they do not fight event brands.

## Exceptions

Breaking a rule needs a strong reason you can defend in front of the brand owners.
Merchandise is the only area the manual exempts.

## Checklist before changing a visual

1. Does the change affect the default event? Then every strict rule above applies.
2. Is every logo an official file from the brand Drive, unaltered and above its minimum size?
3. Is every colour from one palette, at most three per surface, with the vivid palette only for signals?
4. Is text in TheMix, with no all-caps emphasis?
5. On a branded event, is the Skaut logo still present and far from the event logo?

[drive]: https://drive.google.com/drive/folders/1-ZdKULLTFY7Cb4SXBFsZ9FmAuEA4srr1
[tapeta]: https://drive.google.com/drive/folders/1wv8cYt5KdYutc9dJrTSrOd8XASB2sbWW
