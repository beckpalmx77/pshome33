<?php

/**
 * Utility for Double-Entry Accounting (General Ledger)
 */

function PostToGL($conn, $gl_date, $doc_no, $description, $entries, $source_type = 'JV') {
    try {
        // Normalize date to YYYY-MM-DD
        if (strpos($gl_date, '-') !== false) {
            $parts = explode('-', $gl_date);
            if (count($parts) === 3 && strlen($parts[0]) === 2) {
                // Assume DD-MM-YYYY and convert
                $gl_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }

        // 1. Create GL Header
        $stmtHeader = $conn->prepare("INSERT INTO ims_gl_header (gl_date, doc_no, description, source_type) VALUES (?, ?, ?, ?)");
        $stmtHeader->execute([$gl_date, $doc_no, $description, $source_type]);
        $gl_id = $conn->lastInsertId();

        // 2. Create GL Details
        $stmtDetail = $conn->prepare("INSERT INTO ims_gl_details (gl_id, acc_code, dr_amount, cr_amount) VALUES (?, ?, ?, ?)");

        $total_dr = 0;
        $total_cr = 0;

        foreach ($entries as $entry) {
            $dr = (float)($entry['dr'] ?? 0);
            $cr = (float)($entry['cr'] ?? 0);
            
            $stmtDetail->execute([
                $gl_id,
                $entry['acc_code'],
                $dr,
                $cr
            ]);

            $total_dr += $dr;
            $total_cr += $cr;
        }

        // Standard accounting check: Debits must equal Credits
        if (abs($total_dr - $total_cr) > 0.001) {
            // In a real production system, you might throw an exception here
            // throw new Exception("GL Posting Error: Debits ($total_dr) do not equal Credits ($total_cr)");
        }

        return $gl_id;

    } catch (Exception $e) {
        throw new Exception("PostToGL Error: " . $e->getMessage());
    }
}

/**
 * Remove GL entries associated with a document (useful for UPDATES or DELETES)
 */
function RemoveGLByDocNo($conn, $doc_no) {
    // Note: Due to ON DELETE CASCADE on ims_gl_details, we only need to delete the header
    $stmt = $conn->prepare("DELETE FROM ims_gl_header WHERE doc_no = ?");
    $stmt->execute([$doc_no]);
}

/**
 * Get account code by category or product (Placeholder logic)
 * In a full system, this would be a lookup in the product/category table
 */
function GetAccountCodeMapping($conn, $id, $type = 'category') {
    // Default mappings for the prototype
    if ($type === 'payment') {
        return ($id === 'เงินสด') ? '1101' : '1102'; // Cash or Bank
    }
    
    // For expenses, we could try to find it in ims_category
    // For now, return a generic expense account if not found
    return '5101'; 
}
