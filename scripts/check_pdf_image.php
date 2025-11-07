<?php
if ($argc < 2) {
    echo "Usage: php check_pdf_image.php <pdf-path>\n";
    exit(2);
}
$path = $argv[1];
if (!file_exists($path)) {
    echo "File not found: $path\n";
    exit(3);
}
$contents = file_get_contents($path);
$found = [];
// PNG signature
if (strpos($contents, "\x89PNG\r\n\x1a\n") !== false) {
    $found[] = 'PNG';
}
// JPEG signatures (start of image)
if (strpos($contents, "\xFF\xD8\xFF") !== false) {
    $found[] = 'JPEG';
}
// PDF inline image object marker (common) '/Subtype /Image' or '/Filter /DCTDecode' for jpeg
if (strpos($contents, '/Subtype /Image') !== false || strpos($contents, '/Filter /DCTDecode') !== false) {
    $found[] = 'PDF-Image-Object';
}
if (empty($found)) {
    echo "No embedded PNG/JPEG signatures detected in $path\n";
    exit(0);
}
echo "Detected embedded image types: " . implode(', ', array_unique($found)) . " in $path\n";
exit(0);
