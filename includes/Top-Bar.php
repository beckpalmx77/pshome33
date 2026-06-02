<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<style>
    /* Custom CSS */
    @media (max-width: 768px) {
        #clock {
            font-size: 0.9rem;
            text-align: center;
        }

        .navbar-nav {
            flex-direction: column;
            align-items: flex-start;
        }

        .img-profile {
            max-width: 50px;
        }
    }

    @media (max-width: 576px) {
        #clock {
            font-size: 0.8rem;
        }

        .navbar-nav {
            flex-direction: column;
            align-items: flex-start;
        }

        .img-profile {
            max-width: 40px;
        }

        .navbar-nav .nav-link {
            font-size: 0.9rem;
        }
    }

</style>

<?php
$topbar_theme = isset($_SESSION['theme_topbar']) && $_SESSION['theme_topbar'] != "" ? $_SESSION['theme_topbar'] : "bg-navbar";
$sidebar_theme = isset($_SESSION['theme_sidebar']) && $_SESSION['theme_sidebar'] != "" ? $_SESSION['theme_sidebar'] : "sidebar-light";
$sidebar_color = isset($_SESSION['theme_sidebar_color']) && $_SESSION['theme_sidebar_color'] != "" ? $_SESSION['theme_sidebar_color'] : "";
?>

<!-- TopBar -->
<nav class="navbar navbar-expand navbar-light <?php echo $topbar_theme; ?> topbar mb-4 static-top">
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <div class="d-flex flex-grow-1 justify-content-between align-items-center">
        <?php if ($_SESSION['deviceType'] !== 'computer') { ?>
            <ul class="navbar-nav ml-auto d-flex align-items-center">
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span><?php echo "System : " . $_SESSION['first_name'] . " " . $_SESSION['last_name'] ?>&nbsp;</span>
                    </a>
                </li>
            </ul>
        <?php } else { ?>
            <div class="text-white" id="clock" style="font-size: 1rem;"></div>
            <ul class="navbar-nav ml-auto d-flex align-items-center">
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-heart"></i>
                        <span>&nbsp;<?php echo $_SESSION['system_name_1'] ?></span>
                    </a>
                </li>

                <?php if ($_SESSION['account_type'] !== 'user') { ?>
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="manage-message.php" target="_self">
                        <span class="badge badge-danger">Message</span>
                        &nbsp;<i class="fa fa-bell"></i>&nbsp;
                        <span class="badge badge-danger ms-2" id="message_badge" style="display:none;">0</span>
                    </a>
                </li>
                <?php } ?>

                <li class="nav-item dropdown no-arrow">
                    <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                         aria-labelledby="searchDropdown">
                        <form class="navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-1 small"
                                       placeholder="What do you want to look for?" aria-label="Search"
                                       aria-describedby="basic-addon2" style="border-color: #710714;">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php
                        if (!empty($_SESSION['line_picture_profile'])) {
                            $src = $_SESSION['line_picture_profile'];
                        } elseif ($_SESSION['account_type'] === 'admin') {
                            $src = 'img/icon/admin.png';
                        } elseif ($_SESSION['account_type'] === 'supervisor') {
                            $src = 'img/icon/supervisor.png';
                        } else {
                            $src = 'img/default.png';
                        }
                        ?>
                        <img class="img-profile rounded-circle" src="<?php echo $src; ?>" style="max-width: 60px">
                        <span class="ml-2 d-none d-lg-inline text-white small">
                            <?php echo $_SESSION['first_name'] . " " . $_SESSION['last_name'] . " - " . $_SESSION['role'] ?>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                         aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#themeModal">
                            <i class="fas fa-palette fa-sm fa-fw mr-2 text-gray-400"></i>
                            Theme Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        <?php } ?>
    </div>
</nav>

<!-- Theme Modal -->
<div class="modal fade" id="themeModal" tabindex="-1" role="dialog" aria-labelledby="themeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="themeModalLabel">Theme Settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="themeForm">
                    <div class="form-group">
                        <label for="theme_topbar">Topbar Color</label>
                        <select class="form-control" id="theme_topbar" name="theme_topbar">
                            <option value="bg-navbar" <?php echo $topbar_theme == 'bg-navbar' ? 'selected' : ''; ?>>Default Blue</option>
                            <option value="bg-gradient-primary" <?php echo $topbar_theme == 'bg-gradient-primary' ? 'selected' : ''; ?>>Gradient Blue</option>
                            <option value="bg-gradient-success" <?php echo $topbar_theme == 'bg-gradient-success' ? 'selected' : ''; ?>>Gradient Green</option>
                            <option value="bg-gradient-danger" <?php echo $topbar_theme == 'bg-gradient-danger' ? 'selected' : ''; ?>>Gradient Red</option>
                            <option value="bg-gradient-warning" <?php echo $topbar_theme == 'bg-gradient-warning' ? 'selected' : ''; ?>>Gradient Yellow</option>
                            <option value="bg-gradient-dark" <?php echo $topbar_theme == 'bg-gradient-dark' ? 'selected' : ''; ?>>Gradient Dark</option>
                            <option value="bg-gradient-info" <?php echo $topbar_theme == 'bg-gradient-info' ? 'selected' : ''; ?>>Gradient Info</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="theme_sidebar">Sidebar Style</label>
                        <select class="form-control" id="theme_sidebar" name="theme_sidebar">
                            <option value="sidebar-light" <?php echo $sidebar_theme == 'sidebar-light' ? 'selected' : ''; ?>>Light</option>
                            <option value="sidebar-dark" <?php echo $sidebar_theme == 'sidebar-dark' ? 'selected' : ''; ?>>Dark</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="theme_sidebar_color">Sidebar Color (Optional)</label>
                        <select class="form-control" id="theme_sidebar_color" name="theme_sidebar_color">
                            <option value="" <?php echo $sidebar_color == '' ? 'selected' : ''; ?>>Default</option>
                            <option value="bg-gradient-primary" <?php echo $sidebar_color == 'bg-gradient-primary' ? 'selected' : ''; ?>>Gradient Blue</option>
                            <option value="bg-gradient-success" <?php echo $sidebar_color == 'bg-gradient-success' ? 'selected' : ''; ?>>Gradient Green</option>
                            <option value="bg-gradient-danger" <?php echo $sidebar_color == 'bg-gradient-danger' ? 'selected' : ''; ?>>Gradient Red</option>
                            <option value="bg-gradient-warning" <?php echo $sidebar_color == 'bg-gradient-warning' ? 'selected' : ''; ?>>Gradient Yellow</option>
                            <option value="bg-gradient-dark" <?php echo $sidebar_color == 'bg-gradient-dark' ? 'selected' : ''; ?>>Gradient Dark</option>
                            <option value="bg-gradient-info" <?php echo $sidebar_color == 'bg-gradient-info' ? 'selected' : ''; ?>>Gradient Info</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveThemeBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#saveThemeBtn').on('click', function() {
        let formData = $('#themeForm').serialize();
        $.ajax({
            url: 'model/save_theme_process.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response == 1) {
                    alertify.success('Theme updated successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alertify.error('Failed to update theme');
                }
            }
        });
    });
</script>

<!-- Clock Script -->
<script>
    function updateClock() {
        const options = {
            timeZone: 'Asia/Bangkok',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const bangkokTime = new Intl.DateTimeFormat('th-TH', options).format(new Date());
        document.getElementById('clock').textContent = bangkokTime;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<!-- Message Badge Script -->
<script>

    function loadMessageBadge() {
        $.ajax({
            url: "includes/get_unread_count.php",
            method: "GET",
            success: function (data) {
                console.log("Data received: ", data); // ตรวจสอบข้อมูลที่ได้จาก PHP
                const count = parseInt(data);
                if (count > 0) {
                    $('#message_badge').text(count).show();
                } else {
                    $('#message_badge').hide();
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX error: ", status, error);  // ถ้ามีข้อผิดพลาดใน AJAX
            }
        });
    }

    loadMessageBadge();
    setInterval(loadMessageBadge, 5000);

</script>
