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

$("#TableSupplierList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#supplier_id').val(data[0]);
    $('#supplier_name').val(data[1]);
    $('#SearchSupModal').modal('hide');
});
