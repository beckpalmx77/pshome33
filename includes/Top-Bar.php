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

<!-- TopBar -->
<nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <div class="d-flex flex-grow-1 justify-content-between align-items-center">
        <?php if ($_SESSION['deviceType'] !== 'computer') { ?>

        <!-- Right: Navbar items -->
        <ul class="navbar-nav ml-auto d-flex align-items-center">
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <span><?php echo "SAC System : " . $_SESSION['first_name'] . " " . $_SESSION['last_name']?>&nbsp;</span>
                </a>
            </li>
        </ul>
        <?php } else { ?>
        <!-- Left: Clock -->
        <div class="text-white" id="clock" style="font-size: 1rem;"></div>
        <!-- Right: Navbar items -->
        <ul class="navbar-nav ml-auto d-flex align-items-center">
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-heart"></i>
                    <span>&nbsp;<?php echo $_SESSION['system_name_1']?></span>
                </a>
            </li>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="manage-message.php" target="_self">
                    <span class="badge badge-danger">Message</span>&nbsp;<i class="fa fa-bell"></i>
                </a>
            </li>

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
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <?php $src = $_SESSION['sex']==="M" ? "img/boy.png" : "img/girl.png"; ?>
                    <img class="img-profile rounded-circle" src="<?php echo $src;?>" style="max-width: 60px">

                    <span class="ml-2 d-none d-lg-inline text-white small"><?php echo $_SESSION['first_name'] . " " . $_SESSION['last_name'] . " " . $_SESSION['dept_id_approve'] . "-" . $_SESSION['role'] ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
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


<script>
    $(document).ready(function(){

        function load_unseen_notification(view = '')
        {
            $.ajax({
                url:"fetch.php",
                method:"POST",
                data:{view:view},
                dataType:"json",
                success:function(data)
                {
                    //alert(data);
                    $('.dropdown-menu').html(data.notification);
                    if(data.unseen_notification > 0)
                    {
                        $('.count').html(data.unseen_notification);
                    }
                }
            });
        }

        load_unseen_notification();

        $(document).on('click', '.dropdown-toggle', function(){
            $('.count').html('');
            load_unseen_notification('yes');
        });

        //setInterval(function(){
            //load_unseen_notification();;
        //}, 5000);

    });
</script>
<!-- Topbar -->

<!-- JavaScript for updating the clock -->
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

    // Update the clock every second
    setInterval(updateClock, 1000);

    // Initial call to display the clock immediately
    updateClock();
</script>
