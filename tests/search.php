<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/>
</head>
<body>
<form id="form_data">
    <div class="d-flex justify-content-center align-items-center" style="height:100px;">
        <div>
            <input type="text" class="form-control" id="text_unit_id" placeholder="">
        </div>
        <div>
            <input type="text" class="form-control" id="text_unit_name" placeholder="">
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#SearchModal">Click
                หาข้อมูล
            </button>
        </div>
    </div>
</form>

<!-- Modal -->
<div class="modal fade" id="SearchModal" tabindex="-1" role="dialog" aria-labelledby="SearchModalTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="SearchModalTitle">ค้นหาข้อมูล</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="display"></div>
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="TableUnitList" width="100%">
                    <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>หน่วยนับ</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>รหัส</th>
                        <th>หน่วยนับ</th>
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="modal-footer">
                <input type="hidden" name="id" id="id"/>
                <!--button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button-->
            </div>

        </div>
    </div>
</div>
<!-- Modal -->


<script>
    $(document).ready(function () {
        let dataRecords = $('#TableUnitList').DataTable({
            'lengthMenu': [[5, 10, 20, 100], [5, 10, 20, 100]],
            'language': {
                search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ of _PAGES_',
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
                'url': '../model/get_unit_records.php'
            },
            'columns': [
                {data: 'unit_id'},
                {data: 'unit_name'},
                {data: 'select'}
            ]
        });
    });
</script>

<script>

    $("#TableUnitList").on('click', '.select', function () {

        let data = this.id.split('@');
        $('#text_unit_id').val(data[0]);
        $('#text_unit_name').val(data[1]);
        $('#SearchModal').modal('hide');

    });

</script>

</body>
</html>

