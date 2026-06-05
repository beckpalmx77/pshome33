<?php
/**
 * Cron Job script for Database Table Reindexing and Optimization for ALL Databases
 * To be run via CLI: php cronjob_dbs.php
 */

// Use absolute path for includes
$root_path = dirname(__FILE__);
require_once $root_path . '/config/connect_db.php';

// Start output buffering
ob_start();

// Set timeout to unlimited
set_time_limit(0);

echo "Starting Global Database Optimization - " . date('Y-m-d H:i:s') . "\n";
echo "------------------------------------------------------------\n";

try {
    // 1. Get all databases accessible by the current user
    $stmtDbs = $conn->query("SHOW DATABASES");
    $allDbs = $stmtDbs->fetchAll(PDO::FETCH_COLUMN);

    // List of system databases to skip
    $systemDbs = ['information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin'];
    
    // Filter out system DBs first to get a proper count
    $targetDbs = array_filter($allDbs, function($db) use ($systemDbs) {
        return !in_array(strtolower($db), $systemDbs);
    });
    $totalDbs = count($targetDbs);
    
    $totalSaved = 0;
    $dbIndex = 0;
    $totalTableCount = 0;

    foreach ($targetDbs as $db) {
        $dbIndex++;
        echo "\n[" . date('H:i:s') . "] (DB $dbIndex/$totalDbs) Processing Database: `$db` ...\n";
        
        // Force flush output to terminal
        if (ob_get_level() > 0) ob_flush();
        flush();

        // 2. Get all base tables for this database
        $stmtTables = $conn->prepare("
            SELECT TABLE_NAME, ENGINE 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = :db 
            AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ");
        $stmtTables->execute(['db' => $db]);
        $tables = $stmtTables->fetchAll(PDO::FETCH_ASSOC);
        $totalTablesInDb = count($tables);
        $tableIndex = 0;

        foreach ($tables as $info) {
            $tableIndex++;
            $table = $info['TABLE_NAME'];
            $engine = strtoupper($info['ENGINE'] ?? '');

            // Skip Engines that don't support OPTIMIZE
            if (!in_array($engine, ['INNODB', 'MYISAM', 'ARIA'])) {
                echo "  [$tableIndex/$totalTablesInDb] [SKIP] `$table` ($engine) does not support OPTIMIZE\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
                continue;
            }

            echo "  [$tableIndex/$totalTablesInDb] Optimizing `$table` [$engine]... ";
            if (ob_get_level() > 0) ob_flush();
            flush();

            // Measure size before
            $sizeQuery = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
                          FROM information_schema.TABLES 
                          WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table";
            $stmtSize = $conn->prepare($sizeQuery);
            $stmtSize->execute(['db' => $db, 'table' => $table]);
            $sizeBefore = (float)$stmtSize->fetchColumn();

            // ANALYZE and OPTIMIZE
            try {
                $conn->query("ANALYZE TABLE `$db`.`$table`")->execute();
                $conn->query("OPTIMIZE TABLE `$db`.`$table`")->execute();
                
                // Measure size after
                $stmtSize->execute(['db' => $db, 'table' => $table]);
                $sizeAfter = (float)$stmtSize->fetchColumn();

                $saved = max(0, $sizeBefore - $sizeAfter);
                $totalSaved += $saved;
                $totalTableCount++;

                echo "Done. [{$sizeBefore} MB -> {$sizeAfter} MB] Saved: " . round($saved, 2) . " MB\n";
            } catch (Exception $ex) {
                echo "FAILED: " . $ex->getMessage() . "\n";
            }
            
            if (ob_get_level() > 0) ob_flush();
            flush();
        }
        echo "------------------------------------------------------------\n";
    }

    echo "\nGlobal Optimization Completed - " . date('Y-m-d H:i:s') . "\n";
    echo "Total Databases Processed: $totalDbs\n";
    echo "Total Tables Processed: $totalTableCount\n";
    echo "Total Space Saved: " . round($totalSaved, 2) . " MB\n";

    // Capture the buffer content
    $output = ob_get_clean();

    // Write to a separate log file for this job
    file_put_contents($root_path . '/cronjob_dbs_log.txt', $output);

    // Also output to console
    echo $output;

} catch (Exception $e) {
    if (ob_get_level() > 0) {
        $output = ob_get_clean();
    } else {
        $output = "";
    }
    $output .= "\nCRITICAL ERROR: " . $e->getMessage() . "\n";
    file_put_contents($root_path . '/cronjob_dbs_log.txt', $output);
    echo $output;
    exit(1);
}
