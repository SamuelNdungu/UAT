<?php
// Extract image XObject streams from a PDF and save them to files for inspection.
// Usage: php scripts/extract_pdf_images.php path/to/file.pdf

if ($argc < 2) {
    echo "Usage: php scripts/extract_pdf_images.php path/to/file.pdf\n";
    exit(1);
}

$pdf = file_get_contents($argv[1]);
if ($pdf === false) {
    echo "Failed to read: {$argv[1]}\n";
    exit(1);
}

if (!is_dir(__DIR__ . '/../storage/app/public/extracted_images')) {
    mkdir(__DIR__ . '/../storage/app/public/extracted_images', 0755, true);
}

// Alternate approach: look for /Subtype /Image occurrences and extract the following stream
$pos = 0;
$count = 0;
$outDir = __DIR__ . '/../storage/app/public/extracted_images';
while (($pos = strpos($pdf, '/Subtype /Image', $pos)) !== false) {
    $count++;
    $start = max(0, $pos - 400);
    $end = min(strlen($pdf), $pos + 800);
    $chunk = substr($pdf, $start, $end - $start);

    // Find stream start after this global position
    $streamPos = strpos($pdf, "stream", $pos);
    if ($streamPos !== false) {
        // find newline after 'stream'
        if (preg_match('/\Gstream\r?\n/', substr($pdf, $streamPos), $m)) {
            $streamStart = $streamPos + strlen($m[0]);
        } else {
            // fallback: assume next 1-2 bytes are newline
            $streamStart = $streamPos + strlen('stream');
            // skip single newline characters
            if (substr($pdf, $streamStart, 2) === "\r\n") $streamStart += 2;
            elseif (substr($pdf, $streamStart, 1) === "\n" || substr($pdf, $streamStart, 1) === "\r") $streamStart += 1;
        }
        $endstreamPos = strpos($pdf, 'endstream', $streamStart);
        if ($endstreamPos === false) { $pos += 10; continue; }
        $streamLen = $endstreamPos - $streamStart;
        $raw = substr($pdf, $streamStart, $streamLen);

        // Extract dictionary: find '<<' before $pos and '>>' after
        $dictStart = strrpos(substr($pdf, 0, $pos), '<<');
        $dictEnd = strpos($pdf, '>>', $pos);
        $dict = '';
        if ($dictStart !== false && $dictEnd !== false) {
            $dict = substr($pdf, $dictStart, $dictEnd - $dictStart + 2);
        }
        // Extract properties
        $width = null; $height = null; $bpc = null; $cs = null; $filter = null;
        if (preg_match('/\/Width\s+(\d+)/', $dict, $mm)) $width = (int)$mm[1];
        if (preg_match('/\/Height\s+(\d+)/', $dict, $mm)) $height = (int)$mm[1];
        if (preg_match('/\/BitsPerComponent\s+(\d+)/', $dict, $mm)) $bpc = (int)$mm[1];
        if (preg_match('/\/ColorSpace\s+\/([A-Za-z0-9]+)/', $dict, $mm)) $cs = $mm[1];
        if (preg_match('/\/Filter\s+\/([A-Za-z0-9]+)/', $dict, $mm)) $filter = $mm[1];

        echo "Image #{$count}: width={$width} height={$height} bpc={$bpc} cs={$cs} filter={$filter} stream_len={$streamLen}\n";

        // Try decompressing if Flate
        $data = $raw;
        $decompressed = false;
        if ($filter === 'FlateDecode' || strpos($dict, '/FlateDecode') !== false) {
            $try = @gzdecode($raw);
            if ($try !== false) { $data = $try; $decompressed = true; echo " - Flate decoded, length=".strlen($data)."\n"; }
            else { $try2 = @gzuncompress($raw); if ($try2 !== false) { $data = $try2; $decompressed=true; echo " - gzuncompress OK, length=".strlen($data)."\n"; } else { echo " - Flate decode failed\n"; } }
        }

        $saved = [];
        // Heuristic: PNG signature inside data
        $pngSig = "\x89PNG\x0D\x0A\x1A\x0A";
        $posP = strpos($data, $pngSig);
        if ($posP !== false) {
            $png = substr($data, $posP);
            $fname = "$outDir/image_{$count}.png";
            file_put_contents($fname, $png);
            echo " - Saved PNG to: $fname\n";
            $saved[] = $fname;
        }
        if (substr($data,0,2) === "\xFF\xD8") {
            $fname = "$outDir/image_{$count}.jpg";
            file_put_contents($fname, $data);
            echo " - Saved JPEG to: $fname\n";
            $saved[] = $fname;
        }
        if ($decompressed && $width && $height && $bpc == 8) {
            // Try to detect PNG-style scanlines: PNG raw scanlines include a 1-byte filter per row
            // For grayscale (1 channel) expected size = height * (1 + width*1)
            // For RGB (3 channels) expected size = height * (1 + width*3)
            $channels = ($cs === 'DeviceGray') ? 1 : 3;
            $expected = $height * (1 + $width * $channels);
            if (strlen($data) == $expected) {
                // Reconstruct a minimal PNG from the raw scanlines.
                $colorType = ($channels === 1) ? 0 : 2; // 0 = grayscale, 2 = truecolor
                $ihdr = pack('NnnCCCCC', 13, $width, $height, $bpc, $colorType, 0, 0, 0);
                // Build IHDR chunk properly
                $ihdr_data = pack('N', $width) . pack('N', $height) . chr($bpc) . chr($colorType) . chr(0) . chr(0) . chr(0);
                $ihdr_chunk = "\x00\x00\x00\x0D" . "IHDR" . $ihdr_data . pack('N', crc32("IHDR" . $ihdr_data));

                // IDAT: compress the raw scanlines back into zlib format
                $zlib = gzcompress($data);
                $idat_len = strlen($zlib);
                $idat_chunk = pack('N', $idat_len) . "IDAT" . $zlib . pack('N', crc32("IDAT" . $zlib));

                $iend_chunk = pack('N', 0) . "IEND" . pack('N', crc32("IEND"));
                $png = "\x89PNG\r\n\x1A\n" . $ihdr_chunk . $idat_chunk . $iend_chunk;
                $fname = "$outDir/image_{$count}.png";
                file_put_contents($fname, $png);
                echo " - Reconstructed PNG to: $fname\n";
                $saved[] = $fname;
            } else {
                echo " - decompressed length=" . strlen($data) . " expected~$expected (not equal)\n";
            }
        }
        if (empty($saved)) {
            $fname = "$outDir/image_{$count}.raw";
            file_put_contents($fname, $raw);
            echo " - Saved raw stream to: $fname\n";
        }

        $pos = $endstreamPos + 9;
        continue;
    }

    $pos += 10;
}

if ($count === 0) echo "No image objects found.\n";
else echo "Done. Extracted files are in storage/app/public/extracted_images/\n";
