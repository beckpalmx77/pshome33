$(document).ready(function () {

    let formData = {action: "GET_EMPLOYEE_PAYROLL", sub_action: "GET_SELECT"};
    let dataRecords = $('#TableEmployeeList').DataTable({
        'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
        'language': {
            search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'หน้าที่ _PAGE_ จาก _PAGES_',
            infoEmpty: 'ไม่มีข้อมูล',
            zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
            infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
            paginate: {
                previous: 'ก่อนหน้า',
                last: 'สุดท้าย',
                next: 'ต่อไป'
            }
        },
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': 'model/manage_employee_payroll_process.php',
            'data': formData
        },
        'columns': [
            {data: 'emp_id'},
            {data: 'employee_fullname'},
            {data: 'select'}
        ]
    });
});

$("#TableEmployeeList").on('click', '.select', function () {
    let data = this.id.split('@');
    console.log("Raw data from button ID:", this.id); // ดู id ทั้งหมด
    console.log("Splitted data array:", data);       // ดู array ที่ถูกแยกแล้ว

    $('#emp_id').val(data[0]);
    $('#employee_fullname').val(data[1]);

    let salary_type_raw = data[2]; // เก็บค่าดิบก่อนแปลง
    let salary_raw = data[3];     // เก็บค่าดิบก่อนแปลง

    let salary_type_display = salary_type_raw === 'D' ? "รายวัน" : salary_type_raw === 'M' ? "รายเดือน" : "";

    console.log("Parsed salary_type (display):", salary_type_display);
    console.log("Raw salary value:", salary_raw);

    $('#salary_type').val(salary_type_display);
    $('#salary').val(salary_raw); // ใส่ค่าดิบไปก่อน เพื่อดูว่ามีค่าอะไรอยู่

    //alertify.success(salary_type_display + " " + salary_raw);
    $('#SearchEmployeeModal').modal('hide');
});

