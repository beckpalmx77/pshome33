$(document).ready(function () {
    let formData = {action: "GET_MAIN_MENU", sub_action: "GET_SELECT"};
    let dataRecords = $('#TableMainMenuList').DataTable({
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
            'url': 'model/manage_menu_main_process.php',
            'data': formData
        },
        'columns': [
            {data: 'main_menu_id'},
            {data: 'label'},
            {data: 'select'}
        ]
    });
});

$("#TableMainMenuList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#main_menu_id').val(data[0]);
    $('#main_label').val(data[1]);
    $('#Search-MENU-Modal').modal('hide');
});

