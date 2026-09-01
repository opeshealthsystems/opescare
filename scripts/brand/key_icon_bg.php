<?php
/**
 * The supplied app-icon PNG has an OPAQUE BLACK background, not a transparent
 * one. Used as-is, every generated icon carries black corners.
 *
 * This keys out only the black region that is connected to the image border,
 * so nothing inside the artwork is touched, and feathers the boundary using
 * luminance so the white tile does not end up with a hard dark fringe.
 */

$src = $argv[1] ?? null;
$dst = $argv[2] ?? null;
if (! $src || ! $dst) {
    fwrite(STDERR, "usage: php _key_icon_bg.php <in.png> <out.png>\n");
    exit(1);
}

$im = imagecreatefrompng($src);
imagealphablending($im, false);
imagesavealpha($im, true);
$w = imagesx($im);
$h = imagesy($im);

$lum = static function (int $c): float {
    return 0.2126 * (($c >> 16) & 0xFF) + 0.7152 * (($c >> 8) & 0xFF) + 0.0722 * ($c & 0xFF);
};

// Flood the background inward from the border, following dark pixels only.
$BG_MAX  = 45.0;    // luminance at or below this is definitely background
$EDGE_MAX = 165.0;  // luminance below this, adjacent to background, is the anti-aliased rim

$outside = array_fill(0, $w * $h, false);
$queue = new SplQueue();

for ($x = 0; $x < $w; $x++) {
    foreach ([0, $h - 1] as $y) {
        if ($lum(imagecolorat($im, $x, $y)) <= $BG_MAX) {
            $i = $y * $w + $x;
            if (! $outside[$i]) { $outside[$i] = true; $queue->enqueue([$x, $y]); }
        }
    }
}
for ($y = 0; $y < $h; $y++) {
    foreach ([0, $w - 1] as $x) {
        if ($lum(imagecolorat($im, $x, $y)) <= $BG_MAX) {
            $i = $y * $w + $x;
            if (! $outside[$i]) { $outside[$i] = true; $queue->enqueue([$x, $y]); }
        }
    }
}

while (! $queue->isEmpty()) {
    [$x, $y] = $queue->dequeue();
    foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
        $nx = $x + $dx; $ny = $y + $dy;
        if ($nx < 0 || $ny < 0 || $nx >= $w || $ny >= $h) continue;
        $i = $ny * $w + $nx;
        if ($outside[$i]) continue;
        if ($lum(imagecolorat($im, $nx, $ny)) <= $BG_MAX) {
            $outside[$i] = true;
            $queue->enqueue([$nx, $ny]);
        }
    }
}

$out = imagecreatetruecolor($w, $h);
imagealphablending($out, false);
imagesavealpha($out, true);

$cleared = 0;
$feathered = 0;
for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $c = imagecolorat($im, $x, $y);
        $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
        $i = $y * $w + $x;

        if ($outside[$i]) {
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, 255, 255, 255, 127));
            $cleared++;
            continue;
        }

        // Anti-aliased rim: dark pixel touching the keyed region -> partial alpha,
        // and lifted toward the tile colour so it reads as a soft edge, not soot.
        $l = $lum($c);
        if ($l < $EDGE_MAX) {
            $touching = false;
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                $nx = $x + $dx; $ny = $y + $dy;
                if ($nx < 0 || $ny < 0 || $nx >= $w || $ny >= $h) continue;
                if ($outside[$ny * $w + $nx]) { $touching = true; break; }
            }
            if ($touching) {
                $t = $l / $EDGE_MAX;                       // 0 = black, 1 = tile
                $alpha = (int) round(127 * (1 - $t));
                $r = (int) round($r + (255 - $r) * (1 - $t));
                $g = (int) round($g + (255 - $g) * (1 - $t));
                $b = (int) round($b + (255 - $b) * (1 - $t));
                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));
                $feathered++;
                continue;
            }
        }

        imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, 0));
    }
}

imagepng($out, $dst, 9);
printf("keyed %d px transparent (%.1f%%), feathered %d rim px -> %s\n",
    $cleared, 100 * $cleared / ($w * $h), $feathered, $dst);
