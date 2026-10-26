import argparse
import json
import re
import subprocess
import tempfile
from pathlib import Path

PATH_PATTERN = re.compile(r'<path d="([^"]*)" style="fill: (#[0-9a-fA-F]{6}|none)"\s*/>')
OFFSET_STEPS = (-1, 0, 1)
GAP = 1.0


def measure_boxes(svg: str, chromium: str) -> list[list[float]]:
    script = (
        'document.getElementById("o").textContent = JSON.stringify('
        '[...document.querySelectorAll("path")].map(p => {'
        ' const b = p.getBBox(); return [b.x, b.y, b.x + b.width, b.y + b.height]; }));'
    )
    html = f'<!doctype html><html><body>{svg}<pre id="o"></pre><script>{script}</script></body></html>'
    with tempfile.TemporaryDirectory() as tmp:
        page = Path(tmp) / 'page.html'
        page.write_text(html, encoding='utf-8')
        result = subprocess.run(
            [chromium, '--headless', '--disable-gpu', '--dump-dom', f'file://{page}'],
            capture_output=True,
            text=True,
            check=True,
        )
    match = re.search(r'<pre id="o">(.*?)</pre>', result.stdout, re.DOTALL)
    if match is None:
        raise SystemExit('chromium did not return bounding boxes')
    return json.loads(match.group(1).replace('&quot;', '"'))


def overlaps(a: list[float], b: list[float], gap: float) -> bool:
    return a[0] < b[2] + gap and b[0] < a[2] + gap and a[1] < b[3] + gap and b[1] < a[3] + gap


def shifted(box: list[float], dx: float, dy: float) -> list[float]:
    return [box[0] + dx, box[1] + dy, box[2] + dx, box[3] + dy]


def tint(fill: str, base: str, strength: float) -> str:
    channels = []
    for index in (1, 3, 5):
        value = int(fill[index:index + 2], 16)
        background = int(base[index:index + 2], 16)
        mixed = round(background + (value - background) * strength)
        channels.append(f'{max(0, min(255, mixed)):02x}')
    return '#' + ''.join(channels)


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument('source')
    parser.add_argument('out')
    parser.add_argument('--background', required=True)
    parser.add_argument('--strength', type=float, required=True)
    parser.add_argument('--x', type=float, required=True)
    parser.add_argument('--y', type=float, required=True)
    parser.add_argument('--size', type=float, required=True)
    parser.add_argument('--chromium', default='chromium')
    args = parser.parse_args()

    svg = Path(args.source).read_text(encoding='utf-8')
    paths = PATH_PATTERN.findall(svg)
    boxes = measure_boxes(svg, args.chromium)
    if len(boxes) != len(paths):
        raise SystemExit(f'{len(boxes)} boxes for {len(paths)} paths')

    size = args.size
    fragments = []
    for (d, fill), box in zip(paths, boxes):
        if fill == 'none':
            continue
        centre_x = (box[0] + box[2]) / 2
        centre_y = (box[1] + box[3]) / 2
        if args.x <= centre_x < args.x + size and args.y <= centre_y < args.y + size:
            fragments.append((d, fill, shifted(box, -args.x, -args.y)))

    kept = []
    wraps = [(dx * size, dy * size) for dx in OFFSET_STEPS for dy in OFFSET_STEPS if (dx, dy) != (0, 0)]
    for d, fill, box in fragments:
        collides = any(
            overlaps(shifted(box, wx, wy), other[2], GAP) for wx, wy in wraps for other in kept
        )
        if not collides:
            kept.append((d, fill, box))

    lines = [
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {size:g} {size:g}" width="{size:g}" height="{size:g}">',
        f'<rect width="{size:g}" height="{size:g}" fill="{args.background}"/>',
    ]
    tile = [0.0, 0.0, size, size]
    for d, fill, box in kept:
        colour = tint(fill, args.background, args.strength)
        for dx in OFFSET_STEPS:
            for dy in OFFSET_STEPS:
                if overlaps(shifted(box, dx * size, dy * size), tile, 0.0):
                    x = dx * size - args.x
                    y = dy * size - args.y
                    lines.append(f'<path transform="translate({x:g} {y:g})" d="{d}" fill="{colour}"/>')
    lines.append('</svg>')

    output = '\n'.join(lines)
    Path(args.out).write_text(output, encoding='utf-8')
    print(f'{len(fragments)} fragments, {len(kept)} kept, {len(output.encode())} bytes')


main()
