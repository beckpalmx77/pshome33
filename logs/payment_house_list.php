<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ข้อมูลการจัดเก็บค่าส่วนกลาง</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; }
            .table-container {
                max-height: calc(100vh - 250px); /* กำหนดความสูงสูงสุดเพื่อให้ตารางเลื่อนได้ */
                overflow-y: auto;
                border: 1px solid #dee2e6;
                border-radius: .25rem;
            }
            .table thead th {
                position: sticky;
                top: 0;
                background-color: #f8f9fa; /* สีพื้นหลังของ header */
                z-index: 10;
                cursor: pointer; /* ทำให้รู้ว่าคลิกเรียงลำดับได้ */
            }
            .filter-section {
                margin-bottom: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border-radius: .25rem;
            }
            .pagination-container {
                margin-top: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                display: none; /* ซ่อนไว้ตอนแรก */
            }
            .sort-icon {
                margin-left: 5px;
                font-size: 0.8em;
                vertical-align: middle;
            }
            /* เพิ่ม style สำหรับรูปภาพใน modal */
            #slipImage {
                max-width: 100%;
                height: auto;
                display: block; /* ลบช่องว่างด้านล่างรูปภาพ */
                margin: auto; /* จัดกึ่งกลางรูปภาพ */
            }
        </style>
    </head>
    <body>
    <div class="container-fluid">
        <h2 class="mb-4">ข้อมูลการจัดเก็บค่าส่วนกลาง</h2>

        <div class="filter-section">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="searchHouseNo" class="form-label">Search House No.</label>
                    <input type="text" class="form-control" id="searchHouseNo" placeholder="Enter House Number">
                </div>
                <!--div class="col-md-3">
                    <label for="paymentStatus" class="form-label">Payment Status</label>
                    <select class="form-select" id="paymentStatus">
                        <option value="">All</option>
                        <option value="ชำระเรียบร้อยแล้ว">ชำระเรียบร้อยแล้ว</option>
                        <option value="ยังไม่ยืนยันการชำระ">ยังไม่ยืนยันการชำระ</option>
                    </select>
                </div-->
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="applyFilters">Apply Filters</button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-secondary w-100" id="resetFilters">Reset Filters</button>
                </div>
            </div>
        </div>

        <div class="table-responsive table-container position-relative">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <table class="table table-hover table-striped table-bordered align-middle">
                <thead>
                <tr>
                    <th scope="col" data-sort-column="payment_date">วันที่<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="house_number">บ้านเลขที่<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="alley">ซอย<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="detail">ผู้ชำระ<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="area_size">ขนาดพื้นที่บ้าน<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="common_fee">ค่าส่วนกลาง<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="month_name_start">งวดเดือนเริ่มต้น<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="month_name_to">งวดเดือนสิ้นสุด<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="period_year">ปี<span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="amount" class="text-end">Amount <span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="payment_status">Status <span class="sort-icon"></span></th>
                    <th scope="col" data-sort-column="created_at">Created At <span class="sort-icon"></span></th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody id="paymentTableBody">
                <tr><td colspan="8" class="text-center">Loading payments...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div class="d-flex align-items-center">
                <label for="rowsPerPage" class="form-label mb-0 me-2">Rows per page:</label>
                <select class="form-select form-select-sm" id="rowsPerPage" style="width: 80px;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <nav>
                <ul class="pagination mb-0" id="pagination">
                </ul>
            </nav>
            <div id="paginationInfo"></div>
        </div>

    </div>

    <div class="modal fade" id="slipModal" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="slipModalLabel">Payment Slip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="slipImage" src="" alt="Payment Slip">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentPage = 1;
        let currentLimit = $('#rowsPerPage').val();
        let currentSortColumn = 'created_at';
        let currentSortOrder = 'DESC'; // ASC or DESC

        // ฟังก์ชันหลักในการโหลดข้อมูลการชำระเงิน
        function loadPayments() {
            $('#loadingOverlay').show(); // แสดง Loading Overlay

            const searchHouseNo = $('#searchHouseNo').val();
            const paymentStatus = $('#paymentStatus').val();

            $.ajax({
                // *** ส่วนที่แก้ไข: เปลี่ยนชื่อไฟล์ API ให้ถูกต้อง ***
                url: 'api/get_house_payments.php',
                // *** สิ้นสุดส่วนที่แก้ไข ***
                method: 'GET',
                dataType: 'json',
                data: {
                    page: currentPage,
                    limit: currentLimit,
                    searchHouseNo: searchHouseNo,
                    paymentStatus: paymentStatus,
                    sortColumn: currentSortColumn,
                    sortOrder: currentSortOrder
                },
                success: function(response) {
                    $('#loadingOverlay').hide(); // ซ่อน Loading Overlay
                    if (response.success) {
                        populateTable(response.data); // นำข้อมูลไปใส่ในตาราง
                        updatePagination(response.totalPages, response.currentPage, response.totalRecords); // อัพเดท Pagination
                        updateSortIcons(); // อัพเดทไอคอนเรียงลำดับ
                    } else {
                        // แสดงข้อความผิดพลาดหาก API มีปัญหา
                        $('#paymentTableBody').html('<tr><td colspan="13" class="text-center text-danger">Error: ' + response.message + '</td></tr>');
                        $('#pagination').empty();
                        $('#paginationInfo').text('');
                    }
                },
                error: function(xhr, status, error) {
                    $('#loadingOverlay').hide(); // ซ่อน Loading Overlay
                    // แสดงข้อความผิดพลาดของ AJAX
                    $('#paymentTableBody').html('<tr><td colspan="13" class="text-center text-danger">AJAX Error: ' + status + ' - ' + error + '</td></tr>');
                    $('#pagination').empty();
                    $('#paginationInfo').text('');
                    console.error("AJAX Error:", status, error, xhr.responseText);
                }
            });
        }

        // ฟังก์ชันสำหรับใส่ข้อมูลลงในตาราง
        function populateTable(payments) {
            const tbody = $('#paymentTableBody');
            tbody.empty(); // ล้างข้อมูลเดิมในตารางออก

            if (payments.length === 0) {
                tbody.html('<tr><td colspan="13" class="text-center">No payment records found.</td></tr>');
                return;
            }

            // Loop เพื่อสร้างแต่ละแถวของข้อมูล
            payments.forEach(payment => {
                const statusClass = payment.payment_status === 'Y' ? 'badge bg-success' : 'badge bg-warning text-dark';
                // ตรวจสอบว่ามีชื่อไฟล์รูปภาพหรือไม่
                const viewButtonHtml = payment.picture_payment
                    ? `<button class="btn btn-info btn-sm view-btn" data-id="${payment.id}" data-picture-file="${payment.picture_payment}">View</button>`
                    : `<button class="btn btn-secondary btn-sm" disabled>No Slip</button>`; // ปุ่ม View ถูกปิดใช้งานถ้าไม่มีรูป

                const row = `
                    <tr>
                        <td>${payment.payment_date}</td>
                        <td>${payment.house_number}</td>
                        <td>${payment.alley}</td>
                        <td>${payment.detail}</td>
                        <td>${payment.area_size}</td>
                        <td>${payment.common_fee}</td>
                        <td>${payment.month_name_start}</td>
                        <td>${payment.month_name_to}</td>
                        <td>${payment.period_year}</td>
                        <td class="text-end">${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td><span class="${statusClass}">${payment.payment_status === 'Y' ? 'ชำระเรียบร้อยแล้ว' : 'ยังไม่ยืนยันการชำระ'}</span></td>
                        <td>${payment.created_at}</td>
                        <td>
                            ${viewButtonHtml}
                            <button class="btn btn-warning btn-sm edit-btn" data-id="${payment.id}">Edit</button>
                        </td>
                    </tr>
                `;
                tbody.append(row); // เพิ่มแถวเข้าใน tbody
            });
        }

        // ฟังก์ชันสำหรับอัพเดท Pagination UI (เหมือนเดิม)
        function updatePagination(totalPages, currentPage, totalRecords) {
            const paginationUl = $('#pagination');
            paginationUl.empty();

            const maxPagesToShow = 5;
            let startPage, endPage;

            if (totalPages <= maxPagesToShow) {
                startPage = 1;
                endPage = totalPages;
            } else {
                startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
                endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

                if (endPage - startPage + 1 < maxPagesToShow) {
                    startPage = Math.max(1, endPage - maxPagesToShow + 1);
                }
            }

            paginationUl.append(`
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                </li>
            `);

            if (startPage > 1) {
                paginationUl.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                if (startPage > 2) {
                    paginationUl.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationUl.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationUl.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
                paginationUl.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
            }

            paginationUl.append(`
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                </li>
            `);

            const startRecord = (totalRecords === 0) ? 0 : (currentPage - 1) * currentLimit + 1;
            const endRecord = (totalRecords === 0) ? 0 : Math.min(currentPage * currentLimit, totalRecords);
            $('#paginationInfo').text(`Showing ${startRecord}-${endRecord} of ${totalRecords} records.`);
        }

        // ฟังก์ชันสำหรับอัพเดทไอคอนเรียงลำดับที่หัวตาราง (เหมือนเดิม)
        function updateSortIcons() {
            $('th[data-sort-column] .sort-icon').text(''); // ล้างไอคอนทั้งหมดก่อน
            const currentHeader = $(`th[data-sort-column="${currentSortColumn}"]`);
            currentHeader.find('.sort-icon').text(currentSortOrder === 'ASC' ? ' ▲' : ' ▼'); // ตั้งค่าไอคอนสำหรับคอลัมน์ที่เลือก
        }

        // --- Event Listeners ---

        // Event Listener สำหรับเปลี่ยนหน้า (ใช้ Event Delegation เพราะปุ่มถูกสร้างใหม่)
        $('#pagination').on('click', '.page-link', function(e) {
            e.preventDefault();
            const newPage = parseInt($(this).data('page'));
            if (!isNaN(newPage) && newPage !== currentPage && $(this).parent().hasClass('disabled') === false) {
                currentPage = newPage;
                loadPayments();
            }
        });

        // Event Listener สำหรับเปลี่ยนจำนวน Rows per page
        $('#rowsPerPage').on('change', function() {
            currentLimit = $(this).val();
            currentPage = 1;
            loadPayments();
        });

        // Event Listener สำหรับ Apply Filters
        $('#applyFilters').on('click', function() {
            currentPage = 1;
            loadPayments();
        });

        // Event Listener สำหรับ Reset Filters
        $('#resetFilters').on('click', function() {
            $('#searchHouseNo').val('');
            $('#paymentStatus').val('');
            currentPage = 1;
            loadPayments();
        });

        // Event Listener สำหรับ Sorting (ใช้ Event Delegation)
        $('th[data-sort-column]').on('click', function() {
            const column = $(this).data('sort-column');
            if (currentSortColumn === column) {
                currentSortOrder = (currentSortOrder === 'ASC' ? 'DESC' : 'ASC');
            } else {
                currentSortColumn = column;
                currentSortOrder = 'ASC';
            }
            currentPage = 1;
            loadPayments();
        });

        // *** ส่วนที่แก้ไข: Event Listener สำหรับปุ่ม View เพื่อแสดงรูปใน Modal ***
        $('#paymentTableBody').on('click', '.view-btn', function() {
            const paymentId = $(this).data('id');
            const pictureFile = $(this).data('picture-file');

            if (pictureFile) {
                const imageUrl = 'uploads/slips/' + pictureFile; // กำหนด Path ไปยังโฟลเดอร์ uploads/slip/
                $('#slipImage').attr('src', imageUrl);
                // เพิ่มการจัดการเมื่อรูปภาพโหลดเสร็จ (เผื่อมีปัญหาเรื่องขนาดหรือการแสดงผล)
                $('#slipImage').on('load', function() {
                    // หากรูปภาพโหลดสำเร็จ อาจจะปรับขนาด modal หรือไม่ทำอะไรก็ได้
                    // $(this).removeClass('d-none'); // หากเคยซ่อนไว้
                }).on('error', function() {
                    // หากโหลดรูปภาพไม่สำเร็จ ให้แสดงข้อความแจ้งเตือน
                    $('#slipImage').attr('src', ''); // ล้าง src ที่ผิดพลาด
                    alert('ไม่สามารถโหลดรูปภาพ Slip ได้ อาจเป็นไฟล์ไม่ถูกต้อง หรือไม่พบไฟล์');
                    // อาจจะซ่อน modal ถ้าโหลดรูปไม่ได้
                    $('#slipModal').modal('hide');
                });

                const slipModal = new bootstrap.Modal(document.getElementById('slipModal'));
                slipModal.show();
            } else {
                alert('ไม่พบรูปภาพ Slip สำหรับรายการนี้');
            }
        });
        // *** สิ้นสุดส่วนที่แก้ไข ***

        // Event Listener สำหรับปุ่ม Edit (เหมือนเดิม)
        $('#paymentTableBody').on('click', '.edit-btn', function() {
            const paymentId = $(this).data('id');
            alert('Editing payment ID: ' + paymentId);
            // TODO: สามารถเปลี่ยนตรงนี้เพื่อเปิด Modal Dialog หรือ Redirect ไปยังหน้า Edit Form
            // window.location.href = 'edit_payment.php?id=' + paymentId;
        });

        // โหลดข้อมูลครั้งแรกเมื่อหน้าเว็บโหลดเสร็จ
        $(document).ready(function() {
            loadPayments();
        });
    </script>
    </body>
    </html>

<?php } ?>