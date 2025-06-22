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
    $('#emp_id').val(data[0]);
    $('#employee_fullname').val(data[1]);
    let salary_type = data[2] === 'D' ? "รายวัน" : data[2] === 'M' ? "รายเดือน" : "";
    $('#salary_type').val(salary_type);
    $('#salary').val(data[3]);
    $('#SearchEmployeeModal').modal('hide');
});

