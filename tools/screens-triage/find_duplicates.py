"""
Perceptual-hash near-duplicate clustering over the curated mobile-screen images.

Uses a simple 64-bit dHash (difference hash): resize to 9x8 grayscale, compare
each pixel to its right neighbor. Robust to minor compression/resize noise,
which is exactly the kind of near-duplicate the 173-image freeze package has
(many independent AI generations of "a clean mobile app screenshot ui mockup").

Non-portrait / non-screen assets are excluded via MANIFEST.csv dimensions
before hashing (see extract_palette.py's earlier pass — this script only
clusters what's still in the folder root, i.e. already-portrait screens).

Output: prints clusters of 2+ images whose hash Hamming distance <= THRESHOLD,
does NOT move/delete anything itself — that decision needs a human or a
follow-up vision pass to pick which of each cluster is "most advanced".
"""
import os
from PIL import Image

IMG_DIR = os.path.join(os.path.dirname(__file__), "..", "..", "Mobile app screens")
THRESHOLD = 6  # Hamming distance; lower = stricter match


def dhash(path, hash_size=8):
    img = Image.open(path).convert("L").resize((hash_size + 1, hash_size))
    pixels = list(img.getdata())
    bits = []
    for row in range(hash_size):
        row_pixels = pixels[row * (hash_size + 1):(row + 1) * (hash_size + 1)]
        for col in range(hash_size):
            bits.append(row_pixels[col] > row_pixels[col + 1])
    value = 0
    for bit in bits:
        value = (value << 1) | int(bit)
    return value


def hamming(a, b):
    return bin(a ^ b).count("1")


def main():
    files = sorted(f for f in os.listdir(IMG_DIR) if f.lower().endswith(".png"))
    hashes = {}
    for f in files:
        try:
            hashes[f] = dhash(os.path.join(IMG_DIR, f))
        except Exception as e:
            print(f"SKIP {f}: {e}")

    names = list(hashes.keys())
    visited = set()
    clusters = []
    for i, a in enumerate(names):
        if a in visited:
            continue
        group = [a]
        for b in names[i + 1:]:
            if b in visited:
                continue
            if hamming(hashes[a], hashes[b]) <= THRESHOLD:
                group.append(b)
                visited.add(b)
        if len(group) > 1:
            visited.add(a)
            clusters.append(group)

    clusters.sort(key=len, reverse=True)
    print(f"Scanned {len(names)} images. Found {len(clusters)} near-duplicate clusters "
          f"covering {sum(len(c) for c in clusters)} images.\n")
    for idx, group in enumerate(clusters, 1):
        print(f"--- cluster {idx} ({len(group)} images) ---")
        for g in group:
            print(f"  {g}")
        print()


if __name__ == "__main__":
    main()
