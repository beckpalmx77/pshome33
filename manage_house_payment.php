<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <input type="hidden" id="main_menu" name="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                    <input type="hidden" id="sub_menu" name="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-sm">
                                        <div class="col-sm-12">
                                            <div id='calendar'></div>
                                        </div>
                                </div>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.4/index.global.js'></script>
    <script src="js/popup.js"></script>


    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let calendarEl = document.getElementById('calendar');
            // *** Set locale to 'th' for Thai language ***
            let initialLocaleCode = 'th';

            let calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'local',
                headerToolbar: {
                    right: 'prev,next today',
                    center: 'title',
                    left: 'dayGridMonth'
                },
                buttonText: {
                    today: 'วันนี้', // เปลี่ยน "today" เป็น "วันนี้"
                    month: 'เดือน',  // เปลี่ยน "month" เป็น "เดือน" (หรือ "เดือน")
                    week: 'สัปดาห์', // เผื่อต้องการใช้ปุ่ม week ในอนาคต
                    day: 'วัน',      // เผื่อต้องการใช้ปุ่ม day ในอนาคต
                },
                // *** Apply Thai locale ***
                locale: initialLocaleCode,
                initialView: 'dayGridMonth',
                height: 550,
                events: 'model/calendar_house_payment_load.php',
                editable: true,
                selectable: true,

                eventClick: function (info) {

                    info.jsEvent.preventDefault();

                    // change the border color
                    //info.el.style.borderColor = 'red';

                    let main_menu = document.getElementById("main_menu").value;
                    let sub_menu = document.getElementById("sub_menu").value;
                    let url = "manage_house_payment_data.php?title=รายการข้อมูลการชำระค่าส่วนกลาง"
                        + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                        + '&payment_date=' + info.event.id;
                    window.open(url, "", "");
                }
            });

            calendar.render();
        });
    </script>


    </body>
    </html>

<?php } ?>
