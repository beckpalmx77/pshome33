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


<a data-toggle="modal" href="#myModal" class="btn btn-primary">Launch modal</a>

<div class="col-md-12 col-md-offset-2">
    <table id='TableRecordList' class='display dataTable'>
        <thead>
        <tr>
            <th>รหัสสินค้า/วัสดุ</th>
            <th>ชื่อสินค้า/วัสดุ</th>
            <th>ยอดคงเหลือ</th>
            <th>หน่วยนับ</th>
            <th>Status</th>
            <th>Action</th>
            <th>Action</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>รหัสสินค้า/วัสดุ</th>
            <th>ชื่อสินค้า/วัสดุ</th>
            <th>ยอดคงเหลือ</th>
            <th>หน่วยนับ</th>
            <th>Status</th>
            <th>Action</th>
            <th>Action</th>
        </tr>
        </tfoot>
    </table>

    <div id="result"></div>

</div>


<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modal title</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <div class="modal-body">
                    <div class="form-group"
                    <label for="product_id" class="control-label">รหัสสินค้า/วัสดุ</label>
                    <input type="product_id" class="form-control" id="product_id" name="product_id"
                           required="required"
                           placeholder="รหัสสินค้า/วัสดุ">
                </div>

                <div class="form-group">
                    <label for="name_t" class="control-label">ชื่อสินค้า/วัสดุ</label>
                    <input type="text" class="form-control" id="name_t" name="name_t"
                           required="required"
                           placeholder="ชื่อสินค้า/วัสดุ">
                </div>

                <div class="form-group row">
                    <div class="col-sm-4">
                        <label for="quantity" class="control-label">ยอดคงเหลือ</label>
                        <input type="text" class="form-control" id="quantity" name="quantity"
                               required="required"
                               placeholder="ยอดคงเหลือ">
                    </div>
                    <input type="hidden" class="form-control" id="unit_id" name="unit_id">
                    <div class="col-sm-6">
                        <label for="quantity" class="control-label">หน่วยนับ</label>
                        <input type="text" class="form-control" id="unit_name" name="unit_name"
                               required="required"
                               placeholder="หน่วยนับ">
                    </div>

                    <div class="col-sm-2">
                        <label for="quantity" class="control-label">เลือก</label>
                        <a data-toggle="modal" href="#SearchModal" class="btn btn-primary"> Click </a>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status" class="control-label"></label>
                    <select id="status" name="status"
                            class="form-control" data-live-search="true"
                            title="Please select">
                        <option>Active</option>
                        <option>InActive</option>
                    </select>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <a href="#" data-dismiss="modal" class="btn">Close</a>
        </div>
    </div>
</div>
</div>


<div class="modal fade" id="SearchModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modal title</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="container"></div>
            <div class="modal-body">

                <div class="modal-body">

                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="TableUnitList"
                           width="100%">
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

            </div>

        </div>
    </div>
</div>

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
        $('#unit_id').val(data[0]);
        $('#unit_name').val(data[1]);
        $('#SearchModal').modal('hide');
    });

</script>

</html>