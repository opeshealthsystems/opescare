"""
Generate real OpesCare app-icon/splash assets to replace Expo's default
template icon (a generic blue "A" chevron) — matches the in-app Logo
component: a gold ring with a heartbeat glyph, on the cream brand background.

Run from apps/mobile-expo: python ../../tools/screens-triage/generate_icons.py
"""
import math
import os
from PIL import Image, ImageDraw

OUT_DIR = os.path.join(os.path.dirname(__file__), "..", "..", "apps", "mobile-expo", "assets")

CREAM = (253, 248, 240, 255)      # theme cream.100
GOLD_500 = (166, 114, 11, 255)    # theme gold.500
GOLD_600 = (139, 96, 10, 255)     # theme gold.600
GOLD_300 = (217, 167, 58, 255)    # theme gold.300
WHITE = (255, 255, 255, 255)


def heartbeat_points(cx, cy, w, h):
    """A simple EKG zigzag centered at (cx, cy), width w, height h."""
    return [
        (cx - w * 0.45, cy),
        (cx - w * 0.20, cy),
        (cx - w * 0.10, cy - h * 0.35),
        (cx - w * 0.02, cy + h * 0.45),
        (cx + w * 0.06, cy - h * 0.15),
        (cx + w * 0.16, cy),
        (cx + w * 0.45, cy),
    ]


def draw_ring_and_heartbeat(size, ring_color, inner_color, heart_color, transparent_bg=False, stroke_scale=1.0):
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0) if transparent_bg else CREAM)
    d = ImageDraw.Draw(img)
    cx = cy = size / 2
    outer_r = size * 0.34
    ring_w = size * 0.085 * stroke_scale
    inner_r = outer_r - ring_w

    d.ellipse([cx - outer_r, cy - outer_r, cx + outer_r, cy + outer_r], fill=ring_color)
    d.ellipse([cx - inner_r, cy - inner_r, cx + inner_r, cy + inner_r], fill=inner_color)

    pts = heartbeat_points(cx, cy, inner_r * 1.5, inner_r * 1.1)
    d.line(pts, fill=heart_color, width=max(2, int(size * 0.018)), joint="curve")
    return img


def main():
    os.makedirs(OUT_DIR, exist_ok=True)

    # Main icon (iOS + fallback): cream bg, gold ring, cream inner, gold heartbeat.
    icon = draw_ring_and_heartbeat(1024, GOLD_500, CREAM, GOLD_600)
    icon.convert("RGB").save(os.path.join(OUT_DIR, "icon.png"))

    # Splash icon: transparent bg (overlaid on app.json's backgroundColor by
    # the splash-screen plugin), same mark.
    splash = draw_ring_and_heartbeat(1024, GOLD_500, CREAM, GOLD_600, transparent_bg=True)
    splash.save(os.path.join(OUT_DIR, "splash-icon.png"))

    # Favicon (web tab icon), small.
    favicon = draw_ring_and_heartbeat(48, GOLD_500, CREAM, GOLD_600)
    favicon.convert("RGB").save(os.path.join(OUT_DIR, "favicon.png"))

    # Android adaptive icon: foreground (mark only, transparent, padded into
    # the ~66% safe zone), background (solid cream fill), monochrome
    # (single-color silhouette for Android 13+ themed icons).
    fg = draw_ring_and_heartbeat(1024, GOLD_500, CREAM, GOLD_600, transparent_bg=True, stroke_scale=1.3)
    fg.save(os.path.join(OUT_DIR, "android-icon-foreground.png"))

    bg = Image.new("RGBA", (1024, 1024), CREAM)
    bg.save(os.path.join(OUT_DIR, "android-icon-background.png"))

    mono = Image.new("RGBA", (1024, 1024), (0, 0, 0, 0))
    d = ImageDraw.Draw(mono)
    cx = cy = 512
    outer_r = 1024 * 0.34
    ring_w = 1024 * 0.085 * 1.3
    inner_r = outer_r - ring_w
    d.ellipse([cx - outer_r, cy - outer_r, cx + outer_r, cy + outer_r], fill=(255, 255, 255, 255))
    d.ellipse([cx - inner_r, cy - inner_r, cx + inner_r, cy + inner_r], fill=(0, 0, 0, 0))
    pts = heartbeat_points(cx, cy, inner_r * 1.5, inner_r * 1.1)
    d.line(pts, fill=(255, 255, 255, 255), width=max(2, int(1024 * 0.018)), joint="curve")
    mono.save(os.path.join(OUT_DIR, "android-icon-monochrome.png"))

    print("Generated: icon.png, splash-icon.png, favicon.png, android-icon-foreground.png, "
          "android-icon-background.png, android-icon-monochrome.png")


if __name__ == "__main__":
    main()
