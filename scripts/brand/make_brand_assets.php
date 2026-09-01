<?php
/**
 * Derive every brand asset the web platform and the Expo app need from the
 * single source logo. Run once; the outputs are committed.
 *
 *   php _make_brand_assets.php <path-to-source-png>
 */

$src = $argv[1] ?? null;
if (! $src || ! is_file($src)) {
    fwrite(STDERR, "usage: php _make_brand_assets.php <source.png>\n");
    exit(1);
}

$source = imagecreatefrompng($src);
if (! $source) {
    fwrite(STDERR, "could not read source PNG\n");
    exit(1);
}
imagealphablending($source, false);
imagesavealpha($source, true);

$sw = imagesx($source);
$sh = imagesy($source);

/**
 * Trim fully-transparent margins so scaling is driven by the artwork, not by
 * whatever empty canvas the export happened to include.
 */
function trimAlpha($img, int $w, int $h): array
{
    $minX = $w; $minY = $h; $maxX = -1; $maxY = -1;
    $step = max(1, (int) floor(min($w, $h) / 400));   // sampling grid, keeps this fast
    for ($y = 0; $y < $h; $y += $step) {
        for ($x = 0; $x < $w; $x += $step) {
            $alpha = (imagecolorat($img, $x, $y) >> 24) & 0x7F;
            if ($alpha < 120) {                        // 127 = fully transparent
                if ($x < $minX) $minX = $x;
                if ($y < $minY) $minY = $y;
                if ($x > $maxX) $maxX = $x;
                if ($y > $maxY) $maxY = $y;
            }
        }
    }
    if ($maxX < 0) {
        return [0, 0, $w, $h];
    }
    $pad = $step + 1;
    $minX = max(0, $minX - $pad); $minY = max(0, $minY - $pad);
    $maxX = min($w - 1, $maxX + $pad); $maxY = min($h - 1, $maxY + $pad);

    return [$minX, $minY, $maxX - $minX + 1, $maxY - $minY + 1];
}

[$cx, $cy, $cw, $ch] = trimAlpha($source, $sw, $sh);
printf("source %dx%d -> artwork %dx%d at (%d,%d)\n", $sw, $sh, $cw, $ch, $cx, $cy);

/**
 * Render the artwork onto a square canvas.
 *
 * $size     final edge length
 * $scale    fraction of the canvas the artwork should occupy (adaptive icons
 *           need a safe zone, so they pass something well under 1.0)
 * $bg       null = transparent, or [r,g,b] for an opaque plate (iOS icons and
 *           social cards must not be transparent)
 */
function render($source, int $cx, int $cy, int $cw, int $ch, int $size, float $scale, ?array $bg, string $out, ?int $height = null): void
{
    $canvasH = $height ?? $size;
    $canvas  = imagecreatetruecolor($size, $canvasH);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);

    if ($bg === null) {
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
    } else {
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]));
    }
    imagealphablending($canvas, true);

    $box = (int) round(min($size, $canvasH) * $scale);
    $ratio = $cw / $ch;
    if ($ratio >= 1) {
        $dw = $box; $dh = (int) round($box / $ratio);
    } else {
        $dh = $box; $dw = (int) round($box * $ratio);
    }
    $dx = (int) round(($size - $dw) / 2);
    $dy = (int) round(($canvasH - $dh) / 2);

    imagecopyresampled($canvas, $source, $dx, $dy, $cx, $cy, $dw, $dh, $cw, $ch);

    imagesavealpha($canvas, true);
    @mkdir(dirname($out), 0755, true);
    imagepng($canvas, $out, 9);
    imagedestroy($canvas);
    printf("  %-58s %dx%d\n", str_replace('\\', '/', $out), $size, $canvasH);
}

/** Flatten to a solid-colour silhouette from the alpha channel (Android monochrome icon). */
function silhouette($source, int $cx, int $cy, int $cw, int $ch, int $size, float $scale, string $out): void
{
    $tmp = imagecreatetruecolor($size, $size);
    imagealphablending($tmp, false);
    imagesavealpha($tmp, true);
    imagefill($tmp, 0, 0, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
    imagealphablending($tmp, true);

    $box = (int) round($size * $scale);
    $ratio = $cw / $ch;
    [$dw, $dh] = $ratio >= 1 ? [$box, (int) round($box / $ratio)] : [(int) round($box * $ratio), $box];
    imagecopyresampled($tmp, $source, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), $cx, $cy, $dw, $dh, $cw, $ch);

    imagealphablending($tmp, false);
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $alpha = (imagecolorat($tmp, $x, $y) >> 24) & 0x7F;
            imagesetpixel($tmp, $x, $y, imagecolorallocatealpha($tmp, 0, 0, 0, $alpha));
        }
    }
    imagesavealpha($tmp, true);
    imagepng($tmp, $out, 9);
    imagedestroy($tmp);
    printf("  %-58s %dx%d (monochrome)\n", str_replace('\\', '/', $out), $size, $size);
}

$CREAM = [253, 248, 240];   // #FDF8F0 — the app surface colour already in app.json

$web    = __DIR__ . '/public';
$mobile = dirname(__DIR__) . '/mobile-expo/assets';

echo "\nWeb (apps/api-laravel/public):\n";
copy($src, $web . '/brand/opescare-logo.png');
printf("  %-58s %dx%d (source)\n", 'public/brand/opescare-logo.png', $sw, $sh);
render($source, $cx, $cy, $cw, $ch, 512,  1.0,  null,   $web . '/brand/opescare-logo-512.png');
render($source, $cx, $cy, $cw, $ch, 512,  0.92, null,   $web . '/brand/opescare-icon-512.png');
render($source, $cx, $cy, $cw, $ch, 192,  0.92, null,   $web . '/brand/opescare-icon-192.png');
render($source, $cx, $cy, $cw, $ch, 180,  0.86, $CREAM, $web . '/brand/apple-touch-icon.png');
render($source, $cx, $cy, $cw, $ch,  32,  0.98, null,   $web . '/brand/favicon-32.png');
render($source, $cx, $cy, $cw, $ch,  16,  0.98, null,   $web . '/brand/favicon-16.png');
// Social card: 1200x630, artwork on an opaque plate (transparent PNGs go black on most platforms).
render($source, $cx, $cy, $cw, $ch, 1200, 0.62, $CREAM, $web . '/brand/opescare-og.png', 630);

echo "\nMobile (apps/mobile-expo/assets):\n";
render($source, $cx, $cy, $cw, $ch, 1024, 0.82, $CREAM, $mobile . '/icon.png');            // iOS: must be opaque
render($source, $cx, $cy, $cw, $ch, 1024, 0.70, null,   $mobile . '/splash-icon.png');     // splash sits on backgroundColor
render($source, $cx, $cy, $cw, $ch, 1024, 0.62, null,   $mobile . '/android-icon-foreground.png');  // adaptive safe zone
render($source, $cx, $cy, $cw, $ch,   48, 0.98, null,   $mobile . '/favicon.png');
silhouette($source, $cx, $cy, $cw, $ch, 1024, 0.62, $mobile . '/android-icon-monochrome.png');

// Flat cream plate behind the adaptive foreground.
$bgPlate = imagecreatetruecolor(1024, 1024);
imagefill($bgPlate, 0, 0, imagecolorallocate($bgPlate, $CREAM[0], $CREAM[1], $CREAM[2]));
imagepng($bgPlate, $mobile . '/android-icon-background.png', 9);
imagedestroy($bgPlate);
echo "  android-icon-background.png                                 1024x1024 (flat #FDF8F0)\n";

imagedestroy($source);
echo "\ndone\n";
