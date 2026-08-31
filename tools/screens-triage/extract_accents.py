import os, colorsys
from PIL import Image
from collections import Counter

IMG_DIR = os.path.join(os.path.dirname(__file__), "..", "..", "Mobile app screens")

TARGETS = [
    "a_clean_modern_mobile_app_onboarding_welcome_scre.png",
    "a_clean_mobile_app_login_screen_ui_iphone_like_t.png",
    "a_clean_mobile_app_home_dashboard_ui_screenshot.png",
]

def classify(path):
    img = Image.open(path).convert("RGB")
    img.thumbnail((300, 650))
    gold, navy, dark = Counter(), Counter(), Counter()
    for r, g, b in img.getdata():
        h, s, v = colorsys.rgb_to_hsv(r / 255, g / 255, b / 255)
        hue = h * 360
        if 25 <= hue <= 50 and s > 0.35 and v > 0.35:
            gold[(r, g, b)] += 1
        elif 200 <= hue <= 240 and v < 0.45:
            navy[(r, g, b)] += 1
        elif v < 0.20 and s < 0.3:
            dark[(r, g, b)] += 1
    return gold, navy, dark

def top(counter, n=3):
    return [(f"#{r:02X}{g:02X}{b:02X}", c) for (r, g, b), c in counter.most_common(n)]

for fname in TARGETS:
    path = os.path.join(IMG_DIR, fname)
    gold, navy, dark = classify(path)
    print(f"\n=== {fname} ===")
    print("  gold candidates:", top(gold))
    print("  navy candidates:", top(navy))
    print("  dark/text candidates:", top(dark))
