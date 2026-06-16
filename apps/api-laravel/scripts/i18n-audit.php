<?php
/**
 * OpesCare i18n Parity Audit
 * Usage: php scripts/i18n-audit.php
 *
 * Checks every lang/en/*.php against lang/fr/*.php.
 * Reports missing keys and exits non-zero on any mismatch.
 */

$enDir = __DIR__ . '/../lang/en';
$frDir = __DIR__ . '/../lang/fr';
$errors = 0;
$totalKeys = 0;

/**
 * Flatten nested array to dot-notation keys.
 */
$flat = function (array $arr, string $prefix = '') use (&$flat): array {
    $result = [];
    foreach ($arr as $k => $v) {
        $key = $prefix ? "$prefix.$k" : (string)$k;
        if (is_array($v)) {
            $result = array_merge($result, $flat($v, $key));
        } else {
            $result[$key] = $v;
        }
    }
    return $result;
};

// Check EN files against FR
$enFiles = glob($enDir . '/*.php');
sort($enFiles);

foreach ($enFiles as $enFile) {
    $name   = basename($enFile);
    $frFile = $frDir . '/' . $name;

    if (!file_exists($frFile)) {
        echo "✗ MISSING FR FILE : $name\n";
        $errors++;
        continue;
    }

    $en = require $enFile;
    $fr = require $frFile;

    if (!is_array($en) || !is_array($fr)) {
        echo "✗ NOT AN ARRAY    : $name\n";
        $errors++;
        continue;
    }

    $enFlat = $flat($en);
    $frFlat = $flat($fr);

    $missingFr = array_diff_key($enFlat, $frFlat);
    $missingEn = array_diff_key($frFlat, $enFlat);

    if ($missingFr || $missingEn) {
        echo "\n✗ PARITY MISMATCH : $name (" . count($enFlat) . " EN / " . count($frFlat) . " FR)\n";
        foreach ($missingFr as $k => $_) {
            echo "    MISSING IN FR  : $k\n";
        }
        foreach ($missingEn as $k => $_) {
            echo "    MISSING IN EN  : $k\n";
        }
        $errors++;
    } else {
        $count = count($enFlat);
        $totalKeys += $count;
        echo "✓ OK              : $name ($count keys)\n";
    }
}

// Check FR files without EN counterpart
$frFiles = glob($frDir . '/*.php');
foreach ($frFiles as $frFile) {
    $name = basename($frFile);
    if (!file_exists($enDir . '/' . $name)) {
        echo "✗ MISSING EN FILE : $name\n";
        $errors++;
    }
}

echo "\n";
echo str_repeat('─', 50) . "\n";
echo "Total files : " . count($enFiles) . "\n";
echo "Total keys  : $totalKeys\n";

if ($errors === 0) {
    echo "Result      : ✓ ALL FILES HAVE PERFECT 1:1 PARITY\n";
} else {
    echo "Result      : ✗ $errors FILE(S) HAVE PARITY ISSUES\n";
}

exit($errors > 0 ? 1 : 0);
