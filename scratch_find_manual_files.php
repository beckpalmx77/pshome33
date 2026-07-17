<?php
$files = scandir('.');
foreach ($files as $file) {
    if (is_file($file)) {
        if (stripos($file, 'manual') !== false || stripos($file, 'howto') !== false || stripos($file, 'how_to') !== false) {
            echo "File: $file (Size: " . filesize($file) . " bytes)\n";
        }
    }
}
?>
