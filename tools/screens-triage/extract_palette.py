import sys, os
from PIL import Image
from collections import Counter

IMG_DIR = os.path.join(os.path.dirname(__file__), "..", "..", "Mobile app screens")

TARGETS = [
    "a_clean_modern_mobile_app_onboarding_welcome_scre.png",
    "a_clean_mobile_app_login_screen_ui_iphone_like_t.png",
    "a_clean_mobile_app_home_dashboard_ui_screenshot.png",
    "a_clean_minimal_smartphone_splash_screen_design_v.png",
]

def dominant_colors(path, n=8, sample_size=(120, 260)):
    img = Image.open(path).convert("RGB")
    img = img.resize(sample_size)
    pixels = list(img.getdata())
    counts = Counter(pixels)
    total = len(pixels)
    return [(f"#{r:02X}{g:02X}{b:02X}", round(c / total * 100, 1)) for (r, g, b), c in counts.most_common(n)]

for fname in TARGETS:
    path = os.path.join(IMG_DIR, fname)
    if not os.path.exists(path):
        print(f"MISSING: {fname}")
        continue
    print(f"\n=== {fname} ===")
    for hexcode, pct in dominant_colors(path):
        print(f"  {hexcode}  {pct}%")
