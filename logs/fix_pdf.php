<?php
// Copy and mark as decompressed
$input = 'document/sticker_form.pdf';
$output = 'document/sticker_form_decompressed.pdf';
$content = file_get_contents($input);

// Find xref stream position
$xrefPos = strpos($content, 'xref');
echo "xref at: $xrefPos\n";

// Check if compressed xref exists
if (preg_match('/(\d+)\s+0\s+obj.*?\/Type\s*\/XRef/s', $content, $m)) {
    echo "Has compressed xref stream\n";
}

// Just remove filters for now but keep streams compressed
$content = preg_replace('/\/Filter\s*\/FlateDecode/', '', $content);

file_put_contents($output, $content);
echo "Output: " . strlen($content) . " bytes\n";