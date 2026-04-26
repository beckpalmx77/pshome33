<?php
// Full rebuild of PDF
$c = file_get_contents('document/sticker_form.pdf');

// First decompress all streams and track positions
preg_match_all('/(\d+)\s+0\s+obj/', $c, $objMatches, PREG_OFFSET_CAPTURE);

// Get all streams with their positions
$streamPositions = [];
$offset = 0;
$content = $c;

// Decompress streams one by one
while (preg_match('/>>stream\r?\n(.{100,})\r?\nendstream/s', $content, $m, 0, $offset)) {
    $data = $m[1];
    $dec = @gzuncompress($data);
    if ($dec === false) $dec = @gzinflate($data);
    
    if ($dec !== false) {
        $pos = strpos($content, $m[0]);
        $streamPositions[] = ['pos' => $pos, 'oldLen' => strlen($data), 'newLen' => strlen($dec)];
        $content = str_replace($m[0], ">>stream\n" . $dec . "\nendstream", $content);
    }
    $offset = $pos + 10;
}

// Remove remaining filters
$content = preg_replace('/\/Filter\s*\/FlateDecode/', '', $content);

// But we can't easily rebuild xref - file may be corrupted
// Write anyway
file_put_contents('document/sticker_form_decompressed.pdf', $content);
echo "Saved: " . strlen($content) . " bytes\n";

// Let's check what's wrong
$pos = strpos($content, 'xref');
echo "xref at: $pos of " . strlen($content) . " bytes\n";