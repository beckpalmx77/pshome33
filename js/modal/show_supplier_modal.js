$(document).ready(function () {

    let formData = {action: "GET_SUPPLIER", sub_action: "GET_SELECT"};
    let dataRecords = $('#TableSupplierList').DataTable({
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
            'url': 'model/manage_supplier_process.php',
            'data': formData
        },
        'columns': [
            {data: 'supplier_id'},
            {data: 'supplier_name'},
            {data: 'select'}
        ]
    });
});

// *** ส่วนที่แก้ไข: ดึงค่า Address จาก data-attribute โดยตรง ***
$("#TableSupplierList").on('click', '.select', function () {
    // ใช้ .attr() หรือ .data() ดึงค่าที่ส่งมาจาก PHP
    let id = $(this).attr('data-id');
    let name = $(this).attr('data-name');
    let address = $(this).attr('data-address');

    // นำค่าไปใส่ใน Input Form
    $('#supplier_id').val(id);
    $('#supplier_name').val(name);
    $('#address').val(address);

    $('#SearchSupModal').modal('hide');
});