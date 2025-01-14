<div class="modal fade" id="recordModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modal title</h4>
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">×
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-body">
                    <form method="post" id="recordForm">
                        <div class="form-group">
                            <label for="product_id" class="control-label">รหัสสินค้า/วัสดุ</label>
                            <input type="product_id" class="form-control"
                                   id="product_id" name="product_id"
                                   required="required"
                                   placeholder="รหัสสินค้า/วัสดุ">
                        </div>

                        <div class="form-group">
                            <label for="name_t"
                                   class="control-label">ชื่อสินค้า/วัสดุ</label>
                            <input type="text" class="form-control" id="name_t"
                                   name="name_t"
                                   required="required"
                                   placeholder="ชื่อสินค้า/วัสดุ">
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="quantity"
                                       class="control-label">ยอดคงเหลือ</label>
                                <input type="text" class="form-control"
                                       id="quantity"
                                       name="quantity"
                                       required="required"
                                       placeholder="ยอดคงเหลือ">
                            </div>
                            <input type="hidden" class="form-control"
                                   id="unit_id"
                                   name="unit_id">
                            <div class="col-sm-6">
                                <label for="quantity"
                                       class="control-label">หน่วยนับ</label>
                                <input type="text" class="form-control"
                                       id="unit_name"
                                       name="unit_name"
                                       required="required"
                                       placeholder="หน่วยนับ">
                            </div>

                            <div class="col-sm-2">
                                <label for="quantity"
                                       class="control-label">เลือก</label>

                                <a data-toggle="modal" href="#SearchModal"
                                   class="btn btn-primary">
                                    Click <i class="fa fa-search"
                                             aria-hidden="true"></i>
                                </a>
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
                <input type="hidden" name="id" id="id"/>
                <input type="hidden" name="action" id="action" value=""/>
                <input type="submit" name="save" id="save"
                       class="btn btn-primary" value="Save"/>
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">Close
                </button>
            </div>
            </form>

        </div>
    </div>
</div>
