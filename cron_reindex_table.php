<?php
/**
 * Cron Job script for Database Table Reindexing and Optimization
 * To be run via CLI: php cron_reindex_table.php
 */

// Use absolute path for includes to ensure it works from any directory when run via cron
$root_path = dirname(__FILE__);
require_once $root_path . '/config/connect_db.php';

// Start output buffering to capture all echo statements
ob_start();

// Set timeout to unlimited as this might take a while for large databases
set_time_limit(0);

echo "Starting Database Optimization - " . date('Y-m-d H:i:s') . "\n";
echo "------------------------------------------------------------\n";

try {
    // 1. Get all base tables (excluding views)
    $stmt = $conn->query(
        "SELECT TABLE_NAME, TABLE_TYPE, ENGINE 
         FROM information_schema.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         ORDER BY TABLE_NAME"
    );
    $allTables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalSaved = 0;
    $processedCount = 0;
    $skippedCount = 0;

    foreach ($allTables as $info) {
        $table = $info['TABLE_NAME'];
        
        // Skip VIEWS
        if ($info['TABLE_TYPE'] === 'VIEW') {
            echo "[SKIP] `$table` is a VIEW\n";
            $skippedCount++;
            continue;
        }

        $engine = strtoupper($info['ENGINE'] ?? '');

        // Skip Engines that don't support OPTIMIZE
        if (!in_array($engine, ['INNODB', 'MYISAM', 'ARIA'])) {
            echo "[SKIP] `$table` ($engine) does not support OPTIMIZE\n";
            $skippedCount++;
            continue;
        }

        echo "Optimizing `$table` [$engine]... ";

        // Measure size before
        $sizeQuery = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
                      FROM information_schema.TABLES 
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table";
        $stmtSize = $conn->prepare($sizeQuery);
        $stmtSize->execute(['table' => $table]);
        $sizeBefore = (float)$stmtSize->fetchColumn();

        // ANALYZE TABLE (updates index statistics)
        $conn->query("ANALYZE TABLE `$table`")->execute();

        // OPTIMIZE TABLE
        $conn->query("OPTIMIZE TABLE `$table`")->execute();

        // Measure size after
        $stmtSize->execute(['table' => $table]);
        $sizeAfter = (float)$stmtSize->fetchColumn();

        $saved = max(0, $sizeBefore - $sizeAfter);
        $totalSaved += $saved;
        $processedCount++;

        echo "Done. [{$sizeBefore} MB -> {$sizeAfter} MB] Saved: " . round($saved, 2) . " MB\n";
    }

    echo "------------------------------------------------------------\n";
    echo "Optimization Completed - " . date('Y-m-d H:i:s') . "\n";
    echo "Total Tables Processed: $processedCount\n";
    echo "Total Tables Skipped: $skippedCount\n";
    echo "Total Space Saved: " . round($totalSaved, 2) . " MB\n";

    // Capture the buffer content
    $output = ob_get_clean();

    // Write to file (Overwrite mode)
    file_put_contents($root_path . '/reindex_log.txt', $output);

    // Also output to console for manual run visibility
    echo $output;

} catch (Exception $e) {
    // If an error occurs, still try to log what we have
    $output = ob_get_clean();
    $output .= "\nCRITICAL ERROR: " . $e->getMessage() . "\n";
    file_put_contents($root_path . '/reindex_log.txt', $output);
    
    echo $output;
    exit(1);
}
