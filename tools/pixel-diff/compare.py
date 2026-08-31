"""
Pixel-diff a live screenshot of a built Expo screen against its reference
design image. Used as the parity gate described in the design spec
(docs/superpowers/specs/2026-08-31-mobile-expo-app-design.md, section 5/7).

Usage:
    python compare.py <reference.png> <screenshot.png> [out_diff.png]

Prints a mismatch percentage and writes a red-highlighted diff overlay image
so a reviewer (human or the next Read-tool pass) can see exactly where the
built screen deviates from the reference, rather than trusting one number.

Both images are resized to the SAME canvas (matching the reference's aspect
ratio, letterboxing the screenshot if needed) before diffing, since a live
device/simulator screenshot rarely matches a reference PNG's exact pixel
dimensions.
"""
import sys
from PIL import Image, ImageChops


def normalize(img, target_size):
    img = img.convert("RGB")
    img.thumbnail(target_size, Image.LANCZOS)
    canvas = Image.new("RGB", target_size, (255, 255, 255))
    x = (target_size[0] - img.width) // 2
    y = (target_size[1] - img.height) // 2
    canvas.paste(img, (x, y))
    return canvas


def compare(reference_path, screenshot_path, out_path=None):
    reference = Image.open(reference_path)
    screenshot = Image.open(screenshot_path)

    target_size = reference.size
    ref_norm = normalize(reference, target_size)
    shot_norm = normalize(screenshot, target_size)

    diff = ImageChops.difference(ref_norm, shot_norm)
    diff_data = diff.getdata()
    total = len(diff_data)
    mismatched = sum(1 for r, g, b in diff_data if (r + g + b) > 45)  # small tolerance for AA noise
    mismatch_pct = round(mismatched / total * 100, 2)

    if out_path:
        highlight = Image.new("RGB", target_size, (0, 0, 0))
        highlight_px = highlight.load()
        diff_px = diff.load()
        for yy in range(target_size[1]):
            for xx in range(target_size[0]):
                r, g, b = diff_px[xx, yy]
                highlight_px[xx, yy] = (255, 0, 0) if (r + g + b) > 45 else (30, 30, 30)
        highlight.save(out_path)

    return mismatch_pct


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python compare.py <reference.png> <screenshot.png> [out_diff.png]")
        sys.exit(1)
    ref, shot = sys.argv[1], sys.argv[2]
    out = sys.argv[3] if len(sys.argv) > 3 else None
    pct = compare(ref, shot, out)
    print(f"Mismatch: {pct}%")
    if out:
        print(f"Diff heatmap written to {out}")
