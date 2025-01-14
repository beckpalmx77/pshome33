$(document).ready(function () {

    let formData = {action: "GET_PERMISSION", sub_action: "GET_SELECT"};
    let dataRecords = $('#TablePermissionList').DataTable({
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
            'url': 'model/manage_permission.php',
            'data': formData
        },
        'columns': [
            {data: 'permission_id'},
            {data: 'permission_detail'},
            {data: 'select'}
        ]
    });
});

$("#TablePermissionList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#permission_id').val(data[0]);
    $('#permission_detail').val(data[1]);

    //document.getElementById("myDIV").innerHTML = "";

    let permission_id = data[0];
    let formData = {action: "LOAD_PERMISSION", permission_id: permission_id};
    $.ajax({
        type: "POST",
        url: 'model/manage_permission.php',
        dataType: "json",
        data: formData,
        success: function (response) {
            let len = response.length;
            for (let i = 0; i < len; i++) {
                let main_menu = response[i].main_menu;
                let sub_menu = response[i].sub_menu;
                let dashboard_page = response[i].dashboard_page;

                $('#dashboard_page').val(dashboard_page);

                let main_menu_array = main_menu.split(",");
                let sub_menu_array = sub_menu.split(",");

                let main_list = document.getElementsByName("menu_main");
                let sub_list = document.getElementsByName("menu_sub");

                for (let ml = 0; ml < main_list.length; ml++) {
                    document.getElementsByName("menu_main")[ml].checked = false;
                }

                for (let sl = 0; sl < sub_list.length; sl++) {
                    document.getElementsByName("menu_sub")[sl].checked = false;
                }

                for (let m = 0; m < main_menu_array.length; m++) {
                    let m_main = main_menu_array[m];
                    if (m_main!=="") {
                    document.getElementById(m_main).checked = true;
                    }
                }

                for (let s = 0; s < sub_menu_array.length; s++) {
                    let m_sub = sub_menu_array[s];
                    if (m_sub!=="") {
                        document.getElementById(m_sub).checked = true;
                    }
                }

            }

        },
        error: function (response) {
            alertify.error("error : " + response);
        }
    });

    $('#SearchPermissionModal').modal('hide');
});
