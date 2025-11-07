<?php
if ($argc < 2) {
    echo "Usage: php inspect_pdf_images.php <pdf-path>\n";
    exit(2);
}
$path = $argv[1];
if (!file_exists($path)) { echo "File not found: $path\n"; exit(3); }
$contents = file_get_contents($path);
$pos = 0;
$count = 0;
while (($pos = strpos($contents, '/Subtype /Image', $pos)) !== false) {
    $count++;
    $start = max(0, $pos - 200);
    $end = min(strlen($contents), $pos + 800);
    $chunk = substr($contents, $start, $end - $start);
    echo "--- Image #$count at offset $pos ---\n";
    // print chunk with non-printables escaped
    $print = preg_replace_callback('/[\x00-\x1F\x7F-\xFF]/', function($m){
        $c = ord($m[0]); return sprintf('\\x%02X', $c);
    }, $chunk);
    echo $print . "\n\n";
    // try to spot filter, width, height, color space
    if (preg_match('/\/Filter\s*\/([A-Za-z0-9]+)/', $chunk, $m)) {
        echo "Filter: " . $m[1] . "\n";
    }
    if (preg_match('/\/Width\s*(\d+)/', $chunk, $m)) echo "Width: " . $m[1] . "\n";
    if (preg_match('/\/Height\s*(\d+)/', $chunk, $m)) echo "Height: " . $m[1] . "\n";
    if (preg_match('/\/ColorSpace\s*\/([A-Za-z0-9]+)/', $chunk, $m)) echo "ColorSpace: " . $m[1] . "\n";
    if (preg_match('/\/BitsPerComponent\s*(\d+)/', $chunk, $m)) echo "BitsPerComponent: " . $m[1] . "\n";
    // find stream length
    if (preg_match('/stream\r?\n/', $chunk, $m, PREG_OFFSET_CAPTURE)) {
        // locate stream start absolute
        $streamPos = $start + $m[0][1] + strlen($m[0][0]);
        // find 'endstream' after that
        $endstreamPos = strpos($contents, 'endstream', $streamPos);
        if ($endstreamPos !== false) {
            $streamLen = $endstreamPos - $streamPos;
            echo "Stream length approx: $streamLen bytes\n";
            // sample first bytes of stream
            $sample = substr($contents, $streamPos, 16);
            // show hex
            $hex = implode(' ', str_split(bin2hex($sample), 2));
            echo "Stream sample (hex): $hex\n";
        }
    }
    echo "-----------------------------\n\n";
    $pos += 10;
}
if ($count === 0) echo "No image objects found.\n";
