<?php
/**
 * Platform favicon, derived from the supplied transparent logo mark.
 *
 * Produces the PNG sizes browsers actually ask for plus a real multi-image
 * favicon.ico (16/32/48) so the tab icon is crisp instead of a browser
 * downscale of one large bitmap.
 */

$src = $argv[1] ?? null;
if (! $src || ! is_file($src)) {
    fwrite(STDERR, "usage: php _make_favicon.php <source.png>\n");
    exit(1);
}

$source = imagecreatefrompng($src);
imagealphablending($source, false);
imagesavealpha($source, true);
$sw = imagesx($source);
$sh = imagesy($source);

$public = __DIR__ . '/public';
@mkdir($public . '/brand', 0755, true);
copy($src, $public . '/brand/opescare-favicon.png');

function resize($source, int $sw, int $sh, int $size): \GdImage
{
    $c = imagecreatetruecolor($size, $size);
    imagealphablending($c, false);
    imagesavealpha($c, true);
    imagefill($c, 0, 0, imagecolorallocatealpha($c, 255, 255, 255, 127));
    imagealphablending($c, true);

    $ratio = $sw / $sh;
    [$dw, $dh] = $ratio >= 1 ? [$size, (int) round($size / $ratio)] : [(int) round($size * $ratio), $size];
    imagecopyresampled($c, $source, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), 0, 0, $dw, $dh, $sw, $sh);
    imagesavealpha($c, true);

    return $c;
}

$pngs = [];
foreach ([16, 32, 48, 64, 180, 192, 512] as $size) {
    $img = resize($source, $sw, $sh, $size);
    $path = $public . '/brand/favicon-' . $size . '.png';
    imagepng($img, $path, 9);
    if (in_array($size, [16, 32, 48], true)) {
        ob_start();
        imagepng($img, null, 9);
        $pngs[$size] = ob_get_clean();
    }
    imagedestroy($img);
    printf("  brand/favicon-%d.png\n", $size);
}

/*
 * favicon.ico — ICONDIR + one ICONDIRENTRY per size, each pointing at a whole
 * PNG payload. PNG-in-ICO is supported by every browser in use; it keeps the
 * file small and preserves the alpha channel exactly.
 */
$count  = count($pngs);
$ico    = pack('vvv', 0, 1, $count);
$offset = 6 + (16 * $count);
$body   = '';

foreach ($pngs as $size => $data) {
    $ico .= pack(
        'CCCCvvVV',
        $size >= 256 ? 0 : $size,   // width  (0 means 256)
        $size >= 256 ? 0 : $size,   // height
        0,                          // palette size — 0 for truecolour
        0,                          // reserved
        1,                          // colour planes
        32,                         // bits per pixel
        strlen($data),
        $offset
    );
    $offset += strlen($data);
    $body   .= $data;
}

file_put_contents($public . '/favicon.ico', $ico . $body);
printf("  favicon.ico (%d sizes, %d bytes)\n", $count, strlen($ico . $body));

imagedestroy($source);
echo "done\n";
