<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">

    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include('includes/Top-Bar.php'); ?>

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">Expense Master / Detail</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item">Expense</li>
                        <li class="breadcrumb-item active" aria-current="page">Expense Master / Detail</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Expense Entry Form</h6>
                            </div>
                            <div class="card-body">
                                <form id="expenseForm">

                                    <div class="mb-3">
                                        <label for="doc_id" class="form-label">Doc ID</label>
                                        <input type="text" class="form-control" id="doc_id" name="doc_id" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="doc_date" class="form-label">Doc Date</label>
                                        <input type="date" class="form-control" id="doc_date" name="doc_date" required>
                                    </div>

                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered align-middle" id="expenseTable">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Upload</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary mb-3" onclick="addRow()">➕ Add Row</button>

                                    <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
                                </form>

                                <div id="result" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>

        </div>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Dropzone -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
    let rowIndex = 0;
    const dropzoneFiles = {};

    function addRow() {
        const tbody = $("#expenseTable tbody");
        const rowHtml = `
            <tr>
                <td><input type="text" name="description[]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="amount[]" class="form-control" required></td>
                <td><div class="dropzone dz-row" id="dropzone-${rowIndex}" data-row="${rowIndex}"></div></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">🗑 Remove</button></td>
            </tr>
        `;
        tbody.append(rowHtml);
        createDropzone(rowIndex);
        rowIndex++;
    }

    function removeRow(btn) {
        $(btn).closest("tr").remove();
    }

    function createDropzone(index) {
        new Dropzone(`#dropzone-${index}`, {
            url: "upload_temp.php",
            paramName: "file",
            maxFilesize: 5,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function () {
                this.on("success", function(file, response) {
                    if (!dropzoneFiles[index]) dropzoneFiles[index] = [];
                    dropzoneFiles[index].push(response.file);
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = `uploaded_images[${index}][]`;
                    input.value = response.file;
                    document.getElementById(`dropzone-${index}`).appendChild(input);
                });
            }
        });
    }

    $(document).ready(function() {
        addRow(); // เพิ่มแถวเริ่มต้น 1 แถว

        $("#expenseForm").on("submit", function(e) {
            e.preventDefault();

            // ส่งข้อมูลฟอร์มแบบ AJAX
            let formData = new FormData(this);

            // เพิ่มไฟล์ที่อัพโหลดจาก Dropzone ลง formData
            Object.keys(dropzoneFiles).forEach(idx => {
                dropzoneFiles[idx].forEach((fileName, i) => {
                    formData.append(`uploaded_images[${idx}][]`, fileName);
                });
            });

            $.ajax({
                url: 'save_expense.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response == "1") {
                        $("#expenseForm")[0].reset();
                        $("#expenseTable tbody").empty();
                        addRow();
                        $("#result").html('<div class="alert alert-success">บันทึกข้อมูลเรียบร้อย</div>');
                    } else {
                        $("#result").html('<div class="alert alert-danger">เกิดข้อผิดพลาด: ' + response + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $("#result").html('<div class="alert alert-danger">เกิดข้อผิดพลาด: ' + error + '</div>');
                }
            });
        });
    });
</script>
</body>
</html>
