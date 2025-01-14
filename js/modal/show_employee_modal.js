$(document).ready(function () {
    let formData = {action: "GET_EMPLOYEE", sub_action: "GET_SELECT", action_for: "DEPT_ID" };
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
            'url': 'model/get_employee_process.php',
            'data': formData
        },
        'columns': [
            {data: 'emp_id'},
            {data: 'full_name'},
            {data: 'nick_name'},
            {data: 'department_id'},
            {data: 'select'}
        ]
    });
});

$("#TableEmployeeList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#emp_id').val(data[0]);
    $('#full_name').val(data[1]);
    $('#SearchEmployeeModal').modal('hide');
});
