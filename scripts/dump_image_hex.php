<?php
if ($argc < 2) { echo "Usage: php dump_image_hex.php path/to/file\n"; exit(1); }
$f = $argv[1];
if (!file_exists($f)) { echo "File not found: $f\n"; exit(2); }
$b = file_get_contents($f);
echo basename($f) . " len: " . strlen($b) . "\n";
echo "first 64 bytes (hex):\n" . bin2hex(substr($b,0,64)) . "\n";
echo "sample ASCII (printable):\n" . preg_replace('/[^\x20-\x7E]/', '.', substr($b,0,64)) . "\n";
