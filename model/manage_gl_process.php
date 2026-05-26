<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

function convertThaiDate($date) {
    if (strpos($date, '/') !== false) {
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            $year = (int)$parts[2];
            if ($year > 2400) $year -= 543;
            return $year . '-' . $parts[1] . '-' . $parts[0];
        }
    }
    return $date;
}

if ($_POST["action"] === 'GET_GL_DATA') {
    $gl_id = $_POST["gl_id"];
    
    // Get Header
    $stmtH = $conn->prepare("SELECT * FROM ims_gl_header WHERE gl_id = :id");
    $stmtH->execute([':id' => $gl_id]);
    $header = $stmtH->fetch(PDO::FETCH_ASSOC);
    
    // Get Details
    $stmtD = $conn->prepare("SELECT * FROM ims_gl_details WHERE gl_id = :id");
    $stmtD->execute([':id' => $gl_id]);
    $details = $stmtD->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['header' => $header, 'details' => $details]);
    exit;
}

if ($_POST["action"] === 'GET_GL_DATA_BY_DOC') {
    $doc_no = $_POST["doc_no"];
    
    // Get Header
    $stmtH = $conn->prepare("SELECT * FROM ims_gl_header WHERE doc_no = :doc");
    $stmtH->execute([':doc' => $doc_no]);
    $header = $stmtH->fetch(PDO::FETCH_ASSOC);
    
    if($header) {
        // Get Details
        $stmtD = $conn->prepare("SELECT * FROM ims_gl_details WHERE gl_id = :id");
        $stmtD->execute([':id' => $header['gl_id']]);
        $details = $stmtD->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['header' => $header, 'details' => $details]);
    } else {
        echo json_encode(['header' => null, 'details' => []]);
    }
    exit;
}

if ($_POST["action"] === 'UPDATE_GL_ENTRY') {
    $gl_id = $_POST["gl_id"];
    $gl_date = convertThaiDate($_POST["gl_date"]);
    $description = $_POST["description"];
    $details = $_POST["details"]; // Array of {acc_code, dr, cr}

    try {
        $conn->beginTransaction();

        // 1. Update Header
        $stmtH = $conn->prepare("UPDATE ims_gl_header SET gl_date = :gl_date, description = :description WHERE gl_id = :gl_id");
        $stmtH->execute([
            ':gl_date' => $gl_date,
            ':description' => $description,
            ':gl_id' => $gl_id
        ]);

        // 2. Delete old details
        $stmtDel = $conn->prepare("DELETE FROM ims_gl_details WHERE gl_id = :gl_id");
        $stmtDel->execute([':gl_id' => $gl_id]);

        // 3. Insert new details
        $stmtIns = $conn->prepare("INSERT INTO ims_gl_details (gl_id, acc_code, dr_amount, cr_amount) VALUES (:gl_id, :acc, :dr, :cr)");
        foreach ($details as $d) {
            $stmtIns->execute([
                ':gl_id' => $gl_id,
                ':acc' => $d['acc_code'],
                ':dr' => $d['dr'],
                ':cr' => $d['cr']
            ]);
        }

        $conn->commit();
        echo "แก้ไขรายการบัญชีสำเร็จ";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
    exit;
}
