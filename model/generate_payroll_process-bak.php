<?php
// generate_payroll_process.php - ไฟล์ประมวลผลการสร้างข้อมูลเงินเดือนอัตโนมัติ

// 1. การเชื่อมต่อฐานข้อมูล
include("../config/connect_db.php");

// 2. ตรวจสอบการรับค่า POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // กำหนด Header ให้เป็น JSON
    header('Content-Type: application/json');

    try {
        // ตั้งค่า PDO ให้ throw exception เมื่อเกิด error
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // รับค่าจาก Form
        $doc_date_raw = $_POST['doc_date'];
        $payroll_month = (int)$_POST['payroll_month'];
        $payroll_year = (int)$_POST['payroll_year'];

        // แปลง doc_date เป็น DD-MM-YYYY เพื่อบันทึกในฐานข้อมูล
        list($d, $m, $y) = explode('/', $doc_date_raw);
        $doc_date_db_format = $d . '-' . $m . '-' . $y;

        // แปลงเดือนให้เป็นรูปแบบ 2 หลัก (MM) สำหรับ Doc No.
        $month_str = str_pad($payroll_month, 2, '0', STR_PAD_LEFT);

        $inserted_count = 0;

        // ----------------------------------------------------------------------
        // 3. กำหนด Running Number สำหรับ doc_no (PAYYYYYMMXXXX)
        // ----------------------------------------------------------------------
        $doc_no_prefix = "PAY" . $payroll_year . $month_str;

        // ค้นหา Running Number ล่าสุด (XXXX)
        $sql_max_doc = "SELECT MAX(SUBSTRING(doc_no, 10)) AS max_running 
                        FROM ims_payroll 
                        WHERE doc_no LIKE ?";
        $stmt_max_doc = $conn->prepare($sql_max_doc);
        $stmt_max_doc->execute(["{$doc_no_prefix}%"]);
        $max_running_row = $stmt_max_doc->fetch(PDO::FETCH_ASSOC);

        $next_running = (int)$max_running_row['max_running'] + 1;


        // ----------------------------------------------------------------------
        // 4. ดึงข้อมูลพนักงานที่สถานะเป็น 'Y' (Active Employees)
        // ----------------------------------------------------------------------
        $sql_emp = "SELECT emp_id, salary_type, salary FROM memployee WHERE status = 'Y' ORDER BY emp_id ";
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->execute();
        $employees = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);

        // ----------------------------------------------------------------------
        // 5. วนลูปเพื่อตรวจสอบและ INSERT ข้อมูล
        // ----------------------------------------------------------------------
        $conn->beginTransaction();

        foreach ($employees as $emp) {
            $emp_id = $emp['emp_id'];
            $salary = (float)$emp['salary'];

            // 5.1 ตรวจสอบ Key ซ้ำ
            $sql_check = "SELECT COUNT(*) FROM ims_payroll 
                          WHERE emp_id = ? 
                          AND payroll_month = ? 
                          AND payroll_year = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([$emp_id, $payroll_month, $payroll_year]);
            $count = $stmt_check->fetchColumn();

            if ($count == 0) {
                // 5.2 INSERT
                $doc_no = $doc_no_prefix . str_pad($next_running, 4, '0', STR_PAD_LEFT);
                $total_amount = $salary;
                $work_day_month = 30.00;

                $sql_insert = "INSERT INTO ims_payroll 
                                (doc_no, doc_date, emp_id, payroll_month, payroll_year, work_day_month, total_amount, payment_method, bank_no, print_slip_status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_insert = $conn->prepare($sql_insert);
                $result = $stmt_insert->execute([
                    $doc_no,
                    $doc_date_db_format, // DD-MM-YYYY
                    $emp_id,
                    $payroll_month,
                    $payroll_year,
                    $work_day_month,
                    $total_amount,
                    '-',
                    '-',
                    'N'
                ]);

                if ($result) {
                    $inserted_count++;
                    $next_running++;
                }
            }
        }

        $conn->commit();

        // 6. ส่งผลลัพธ์กลับในรูปแบบ JSON
        if ($inserted_count > 0) {
            $response = [
                'status' => 'success',
                'message' => "สร้างข้อมูลเงินเดือนสำเร็จ {$inserted_count} รายการ สำหรับเดือน {$payroll_month} ปี {$payroll_year}!"
            ];
        } else {
            $response = [
                'status' => 'info',
                'message' => "ไม่มีรายการเงินเดือนที่ถูกสร้างใหม่ สำหรับเดือน {$payroll_month} ปี {$payroll_year} (อาจมีข้อมูลอยู่แล้ว)"
            ];
        }

    } catch (PDOException $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        $response = [
            'status' => 'error',
            'message' => "เกิดข้อผิดพลาดในการสร้างข้อมูลเงินเดือน: " . $e->getMessage()
        ];
    }

    // แสดงผลลัพธ์ JSON
    echo json_encode($response);

} else {
    // 7. หากเข้าถึงโดยตรง
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}