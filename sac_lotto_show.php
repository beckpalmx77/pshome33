<?php
include('includes/Header.php');
require_once 'config/connect_lotto_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- datatable -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8"
            src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <title>SAC LOTTO LIST</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12"><br>
            <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
            <h6 class="text-primary"><b>SAC LOTTO LIST</b></h6>
            <button type="button" id="backBtn" class="btn btn-danger mb-3"><i class="fa fa-reply"></i> กลับหน้าแรก
            </button>
            <table id="DataTable" class="table table-striped table-hover table-bordered">
                <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อร้าน</th>
                    <th>โทรศัพท์</th>
                    <th>จังหวัด</th>
                    <th>หมายเลข</th>
                    <th>Sale</th>
                    <th>อนุมัติ</th>
                    <th>วันที่บันทึก</th>
                    <th>Action</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM ims_lotto ORDER BY id DESC");
                $stmt->execute();
                $result = $stmt->fetchAll();
                $line_no = 0;
                foreach ($result as $rows) {
                    $line_no++;
                    ?>
                    <tr>
                        <td><?= $line_no; ?></td>
                        <td><?= htmlspecialchars($rows['lotto_name']); ?></td>
                        <td><?= htmlspecialchars($rows['lotto_phone']); ?></td>
                        <td><?= htmlspecialchars($rows['lotto_province']); ?></td>
                        <td><?= htmlspecialchars($rows['lotto_number']); ?></td>
                        <td><?= htmlspecialchars($rows['sale_name']); ?></td>
                        <td class="text-center text-<?= $rows['approve_status'] == 'Y' ? 'success' : 'secondary'; ?>">
                            <?= $rows['approve_status'] == 'Y' ? 'อนุมัติ' : 'ยังไม่อนุมัติ'; ?>
                        </td>

                        <?php
                            $create_date = new DateTime($rows['create_date']);
                            //echo $date->format('d-m-Y');
                        ?>

                        <td><?= htmlspecialchars($create_date->format('d-m-Y H:i:s')); ?></td>
                        <td>
                            <button type="button" class="btn btn-success text-white"
                                    onclick="openLottoCheck(<?= $rows['id']; ?>)">Check
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-info text-white"
                                    onclick="openUpdateModal(<?= $rows['id']; ?>)">Update
                            </button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Popup แสดงภาพ -->
    <div id="imagePopup" class="popup">
        <div class="popup-content">
            <span class="close-popup">✖ ปิด</span>
            <img id="popupImage" src="" alt="Preview">
        </div>
    </div>

</div>

<!-- Modal -->
<div class="modal fade" id="updateModal" name="updateModal" tabindex="-1" aria-labelledby="updateModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm" enctype="multipart/form-data">
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label>ชื่อร้าน</label>
                        <input type="text" class="form-control" id="lotto_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>โทรศัพท์</label>
                        <input type="text" class="form-control" id="lotto_phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="lotto_province">จังหวัด</label>
                        <select id="lotto_province" name="lotto_province" required="true"
                                class="form-control" data-live-search="true"
                                title="Please select">
                            <option value="" selected></option>
                            <?php
                            $sql1 = "SELECT * FROM ims_provinces WHERE 1 =1";
                            $query1 = $conn->prepare($sql1);
                            $query1->execute();
                            $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                            if ($query1->rowCount() > 0) {
                                foreach ($results1 as $result1) { ?>
                                    <option value="<?php echo htmlentities($result1->province_name); ?>"><?php echo htmlentities($result1->province_name); ?></option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lotto_number" class="control-label">หมายเลขที่เลือก
                            (000-999)</label>
                        <input type="number" class="form-control" id="lotto_number"
                               name="lotto_number"
                               min="0" max="999" required="true"
                               value=""
                               placeholder="">
                    </div>
                    <div class="form-group">
                        <div class="form-group">
                            <label class="control-label" for="sale_name">ชื่อ Sale</label>
                            <select id="sale_name" name="sale_name" required="true"
                                    class="form-control" data-live-search="true"
                                    title="Please select">
                                <option value="" selected></option>
                                <?php
                                $sql2 = "SELECT * FROM ims_sale_team WHERE 1 =1 ORDER BY id ";
                                $query2 = $conn->prepare($sql2);
                                $query2->execute();
                                $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                if ($query2->rowCount() > 0) {
                                    foreach ($results2 as $result2) { ?>
                                        <option value="<?php echo htmlentities($result2->sale_name); ?>"><?php echo htmlentities($result2->sale_name); ?></option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>สถานะอนุมัติ</label>
                        <select class="form-select" id="approve_status" name="status">
                            <option value="N">ยังไม่อนุมัติ</option>
                            <option value="Y">อนุมัติ</option>
                        </select>
                    </div>
                    
                    <input type="hidden" id="text_lotto_file_input" value="">
                    <input type="hidden" id="text_lotto_file1_input" value="">
                    <input type="hidden" id="text_lotto_file2_input" value="">
                    <input type="hidden" id="text_lotto_file3_input" value="">
                    <input type="hidden" id="text_lotto_file4_input" value="">
                    <input type="hidden" id="text_lotto_file5_input" value="">
                    <input type="hidden" id="text_lotto_file6_input" value="">
                    <input type="hidden" id="text_lotto_file7_input" value="">
                    <input type="hidden" id="text_lotto_file8_input" value="">

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 1</label>
                        <input type="file" name="lotto_file[]" id="lotto_file_input" multiple>
                    </div>
                    <div id="lotto_file_images"></div> <!-- แสดงรูปจาก lotto_file -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 2</label>
                        <input type="file" name="lotto_file1[]" id="lotto_file1_input" multiple>
                    </div>
                    <div id="lotto_file1_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 3</label>
                        <input type="file" name="lotto_file3[]" id="lotto_file3_input" multiple>
                    </div>
                    <div id="lotto_file3_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 4</label>
                        <input type="file" name="lotto_file4[]" id="lotto_file4_input" multiple>
                    </div>
                    <div id="lotto_file4_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 5</label>
                        <input type="file" name="lotto_file5[]" id="lotto_file5_input" multiple>
                    </div>
                    <div id="lotto_file5_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 6</label>
                        <input type="file" name="lotto_file6[]" id="lotto_file6_input" multiple>
                    </div>
                    <div id="lotto_file6_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 7</label>
                        <input type="file" name="lotto_file7[]" id="lotto_file7_input" multiple>
                    </div>
                    <div id="lotto_file7_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปป้ายไวนิล 8</label>
                        <input type="file" name="lotto_file8[]" id="lotto_file8_input" multiple>
                    </div>
                    <div id="lotto_file8_images"></div> <!-- แสดงรูปจาก lotto_file2 -->

                    <div class="form-group">
                        <label>อัพโหลดรูปเลขหลังป้าย</label>
                        <input type="file" name="lotto_file2[]" id="lotto_file2_input" multiple>
                    </div>
                    <div id="lotto_file2_images"></div> <!-- แสดงรูปจาก lotto_file2 -->


                    <div class="form-group">
                        <label>หมายเหตุ</label>
                        <input type="text" class="form-control" id="remark" name="remark">
                    </div>

                    <input type="hidden" id="action" name="action" value="">
                    <div class="md-3">
                        <div class="form-group">
                            <button type="button" name="saveBtn" id="saveBtn" tabindex="4"
                                    class="form-control btn btn-primary">
                                            <span>
                                                <i class="fa fa-save" aria-hidden="true"></i>
                                                บันทึก
                                            </span>
                        </div>
                    </div>
                    <div class="md-3">
                        <div class="form-group">
                            <button type="button" name="closetBtn" id="closetBtn" tabindex="4"
                                    class="form-control btn btn-danger">
                                            <span>
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                                ปิด
                                            </span>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* สไตล์สำหรับภาพที่แสดงเมื่อ hover */
    #imagePreview {
        display: none;
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 9999;
        border: 3px solid #333;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        max-width: 300px;
        max-height: 300px;
        background-color: #fff;
    }
</style>

<style>
    .popup {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        text-align: center;
    }

    .popup-content img {
        max-width: 100%;
        max-height: 100%;
        border-radius: 10px;
    }

    .close-popup {
        position: absolute;
        top: 10px;
        right: 15px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        font-size: 18px;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    .close-popup:hover {
        background: rgba(0, 0, 0, 0.9);
    }


</style>

<style>
    .btn-success, .btn-info {
        color: white !important;
    }
</style>

<!-- เพิ่มองค์ประกอบสำหรับแสดงรูปภาพ -->
<img id="imagePreview" src="" alt="Image Preview">

<script>
    $(document).ready(function () {
        $('.hover-image').hover(function (e) {
            const imgSrc = $(this).data('img');
            $('#imagePreview').attr('src', imgSrc).fadeIn();
        }, function () {
            $('#imagePreview').fadeOut();
        });

        $(document).on('mousemove', function (e) {
            $('#imagePreview').css({
                top: e.pageY + 15 + 'px',
                left: e.pageX + 15 + 'px'
            });
        });
    });
</script>

<script>
    $(document).ready(function () {

        $('#DataTable').DataTable({
            "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "ทั้งหมด"]],
            "language": {
                "search": "ค้นหา: ",
                "lengthMenu": "แสดง _MENU_ รายการ",
                "info": "หน้าที่ _PAGE_ จาก _PAGES_",
                "infoEmpty": "ไม่มีข้อมูล",
                "zeroRecords": "ไม่มีข้อมูลตามเงื่อนไข",
                "infoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)",
                "paginate": {
                    "first": "หน้าแรก",
                    "previous": "ก่อนหน้า",
                    "next": "ถัดไป",
                    "last": "สุดท้าย"
                }
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // แสดงพรีวิวรูปภาพ
        function previewImages(inputSelector, previewContainerSelector) {
            $(inputSelector).on('change', function (e) {
                let files = e.target.files;
                let previewContainer = $(previewContainerSelector);
                previewContainer.html("");

                Array.from(files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function (event) {
                        previewContainer.append(`
                        <div class="file-preview">
                            <img src="${event.target.result}" alt="${file.name}" width="150" height="150">
                        </div>
                    `);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        previewImages('#lotto_file_input', '#lotto_file_images');
        previewImages('#lotto_file1_input', '#lotto_file1_images');
        previewImages('#lotto_file2_input', '#lotto_file2_images');
        previewImages('#lotto_file3_input', '#lotto_file3_images');
        previewImages('#lotto_file4_input', '#lotto_file4_images');
        previewImages('#lotto_file5_input', '#lotto_file5_images');
        previewImages('#lotto_file6_input', '#lotto_file6_images');
        previewImages('#lotto_file7_input', '#lotto_file7_images');
        previewImages('#lotto_file8_input', '#lotto_file8_images');
    });

    function openUpdateModal(id) {
        $.ajax({
            type: "POST",
            url: 'model/lotto_process.php',
            data: {action: "GET_DATA", id: id, table_name: "ims_lotto"},
            dataType: "json",
            success: function (data) {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                $('#id').val(data.id);
                $('#lotto_name').val(data.lotto_name);
                $('#lotto_phone').val(data.lotto_phone);
                $('#lotto_province').val(data.lotto_province);
                $('#lotto_number').val(data.lotto_number);
                $('#sale_name').val(data.sale_name);
                $('#approve_status').val(data.approve_status);
                $('#remark').val(data.remark);

                $('#text_lotto_file_input').val(data.lotto_file);
                $('#text_lotto_file1_input').val(data.lotto_file1);
                $('#text_lotto_file2_input').val(data.lotto_file2);
                $('#text_lotto_file3_input').val(data.lotto_file3);
                $('#text_lotto_file4_input').val(data.lotto_file4);
                $('#text_lotto_file5_input').val(data.lotto_file5);
                $('#text_lotto_file6_input').val(data.lotto_file6);
                $('#text_lotto_file7_input').val(data.lotto_file7);
                $('#text_lotto_file8_input').val(data.lotto_file8);

                displayImages('#lotto_file_images', data.lotto_file);
                displayImages('#lotto_file1_images', data.lotto_file1);
                displayImages('#lotto_file2_images', data.lotto_file2);
                displayImages('#lotto_file3_images', data.lotto_file3);
                displayImages('#lotto_file4_images', data.lotto_file4);
                displayImages('#lotto_file5_images', data.lotto_file5);
                displayImages('#lotto_file6_images', data.lotto_file6);
                displayImages('#lotto_file7_images', data.lotto_file7);
                displayImages('#lotto_file8_images', data.lotto_file8);

                $('#updateModal').modal('show');
            },
            error: function (xhr, status, error) {
                alert(`เกิดข้อผิดพลาด: ${status} - ${error}`);
            }
        });
    }

    function displayImages(container, files) {
        $(container).empty();
        if (files) {
            files.split(',').forEach(file => {
                let fileName = file.trim();
                if (fileName) {
                    $(container).append(`
                    <div class="file-preview">
                        <img src="uploads/${fileName}" class="img-thumbnail mb-2" style="max-width:150px;">
                        <!--button type="button" class="btn btn-danger btn-sm remove-file" data-file="${fileName}">ลบ</button-->
                    </div>
                `);
                }
            });
        }
    }

    $('#saveBtn').click(function () {
        let requiredFields = ['#lotto_name', '#lotto_phone', '#lotto_province', '#sale_name', '#lotto_number'];
        if (requiredFields.some(field => !$(field).val())) {
            alertify.error("กรุณากรอกข้อมูลให้ครบถ้วน");
            return;
        }

        let files = $('#lotto_file_input')[0].files;
        let files1 = $('#lotto_file1_input')[0].files;
        let files2 = $('#lotto_file2_input')[0].files;
        let files3 = $('#lotto_file3_input')[0].files;
        let files4 = $('#lotto_file4_input')[0].files;
        let files5 = $('#lotto_file5_input')[0].files;
        let files6 = $('#lotto_file6_input')[0].files;
        let files7 = $('#lotto_file7_input')[0].files;
        let files8 = $('#lotto_file8_input')[0].files;

        if (![...files, ...files2].every(file => file.type.startsWith('image/'))) {
            alertify.error("ไฟล์ที่อัพโหลดต้องเป็นไฟล์ภาพ");
            return;
        }

        let formData = new FormData();
        formData.append("action", "UPDATE");
        formData.append("id", $('#id').val());
        formData.append("table_name", "ims_lotto");
        formData.append("lotto_name", $('#lotto_name').val());
        formData.append("lotto_phone", $('#lotto_phone').val());
        formData.append("lotto_province", $('#lotto_province').val());
        formData.append("lotto_number", $('#lotto_number').val());
        formData.append("sale_name", $('#sale_name').val());
        formData.append("approve_status", $('#approve_status').val());
        formData.append("remark", $('#remark').val());

        Array.from(files).forEach(file => formData.append("lotto_file[]", file));
        Array.from(files1).forEach(file => formData.append("lotto_file1[]", file));
        Array.from(files2).forEach(file => formData.append("lotto_file2[]", file));
        Array.from(files3).forEach(file => formData.append("lotto_file3[]", file));
        Array.from(files4).forEach(file => formData.append("lotto_file4[]", file));
        Array.from(files5).forEach(file => formData.append("lotto_file5[]", file));
        Array.from(files6).forEach(file => formData.append("lotto_file6[]", file));
        Array.from(files7).forEach(file => formData.append("lotto_file7[]", file));
        Array.from(files8).forEach(file => formData.append("lotto_file8[]", file));
        $.ajax({
            type: "POST",
            url: 'model/lotto_process.php',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response === "0") {
                    alertify.error("ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูล");
                } else {
                    alertify.success("บันทึกสำเร็จ");
                    $('#updateModal').modal('hide');

                    // ดึงค่า ID และส่งไปใน URL
                    let id = $('#id').val();
                    window.open(`show_data_lotto_after.php?id=${id}`, '_blank');

                    // รีโหลดหน้า
                    setTimeout(function () {
                        location.reload();
                    }, 1000);

                }
            },
            error: function (xhr, status, error) {
                let errorMessage = "เกิดข้อผิดพลาด : " + error;
                if (xhr.responseText) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        errorMessage += "\n" + JSON.stringify(response, null, 2);
                    } catch (e) {
                        errorMessage += "\n" + xhr.responseText;
                    }
                }
                alertify.error(errorMessage);
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // ใช้ event delegation เพื่อให้ทำงานแม้ element ถูกโหลดทีหลัง
        $(document).on("click", ".open-popup", function () {
            let imgSrc = $(this).attr("data-img");

            if (imgSrc) {
                $("#popupImage").attr("src", imgSrc);
                $("#imagePopup").fadeIn();
            } else {
                console.error("ไม่มีค่า data-img");
            }
        });

        // ปิด popup เมื่อกดที่ปุ่มปิด หรือที่พื้นที่นอก popup
        $(document).on("click", ".close-popup, #imagePopup", function () {
            $("#imagePopup").fadeOut();
        });

        // ป้องกัน popup-content จากการ trigger event ปิด popup
        $(document).on("click", ".popup-content", function (event) {
            event.stopPropagation();
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#closetBtn').click(function () {
            $('#updateModal').modal('hide');
        });
    });
</script>

<script>
    function openLottoCheck(id) {
        window.open(`show_data_lotto?id=${id}`);
    }
</script>

<script>
    $('#backBtn').click(function () {
        window.location.href = "sac_lotto";
    });
</script>


</body>
</html>
