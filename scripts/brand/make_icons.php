<?php
/**
 * Icon set, derived from the supplied app-icon tile (background already keyed
 * out by _key_icon_bg.php).
 *
 * The tile is used as-is wherever the platform draws the artwork directly
 * (iOS icon, web favicons, apple-touch-icon, PWA icons). It is deliberately
 * NOT used for the Android adaptive foreground: Android applies its own mask
 * to the foreground layer, so feeding it a pre-rounded tile clips the corners
 * twice. There the same design is reproduced the way Android expects it —
 * a white background layer with the bare logo as the foreground.
 */

$tile = __DIR__ . '/public/brand/opescare-app-icon.png';
$logo = __DIR__ . '/public/brand/opescare-logo.png';

foreach ([$tile, $logo] as $f) {
    if (! is_file($f)) { fwrite(STDERR, "missing: $f\n"); exit(1); }
}

function load(string $path) {
    $im = imagecreatefrompng($path);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    return $im;
}

/** Resize the whole source onto a square canvas. $bg null = keep transparency. */
function square($src, int $size, ?array $bg, string $out, float $scale = 1.0): void
{
    $sw = imagesx($src); $sh = imagesy($src);
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagefill($canvas, 0, 0, $bg === null
        ? imagecolorallocatealpha($canvas, 255, 255, 255, 127)
        : imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]));
    imagealphablending($canvas, true);

    $box = (int) round($size * $scale);
    $ratio = $sw / $sh;
    [$dw, $dh] = $ratio >= 1 ? [$box, (int) round($box / $ratio)] : [(int) round($box * $ratio), $box];
    imagecopyresampled($canvas, $src, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), 0, 0, $dw, $dh, $sw, $sh);

    imagesavealpha($canvas, true);
    @mkdir(dirname($out), 0755, true);
    imagepng($canvas, $out, 9);
    imagedestroy($canvas);
    printf("  %-52s %dx%d%s\n", basename(dirname($out)) . '/' . basename($out), $size, $size, $bg ? ' (opaque)' : '');
}

$WHITE  = [255, 255, 255];
$web    = __DIR__ . '/public';
$mobile = dirname(__DIR__) . '/mobile-expo/assets';

$t = load($tile);
$l = load($logo);

echo "Web icons (from the app-icon tile):\n";
square($t, 512, null,   $web . '/brand/opescare-icon-512.png');
square($t, 192, null,   $web . '/brand/opescare-icon-192.png');
square($t, 180, $WHITE, $web . '/brand/apple-touch-icon.png');   // iOS will not honour alpha
square($t,  32, null,   $web . '/brand/favicon-32.png');
square($t,  16, null,   $web . '/brand/favicon-16.png');

echo "\nMobile icons:\n";
square($t, 1024, $WHITE, $mobile . '/icon.png');                 // iOS app icon must be opaque
square($t,   48, null,   $mobile . '/favicon.png');

echo "\nAndroid adaptive (masked by the OS — tile would be clipped twice):\n";
square($l, 1024, null,   $mobile . '/android-icon-foreground.png', 0.62);
$plate = imagecreatetruecolor(1024, 1024);
imagefill($plate, 0, 0, imagecolorallocate($plate, 255, 255, 255));
imagepng($plate, $mobile . '/android-icon-background.png', 9);
imagedestroy($plate);
echo "  assets/android-icon-background.png                   1024x1024 (flat #FFFFFF)\n";

imagedestroy($t);
imagedestroy($l);
echo "\ndone\n";
