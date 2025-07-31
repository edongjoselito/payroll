<!-- <script type="text/javascript"> 
    window.history.forward(); 
    function noBack() { 
        window.history.forward(); 
    } 
</script> -->
<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">
        <?php if($this->session->userdata('level') === 'Student'): ?>
            <!-- Student specific items -->
      <?php else: ?>
    <li class="dropdown notification-list">
        <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
            <i class="mdi mdi-cake-variant"></i>
            <?php if (!empty($birthday_count) && $birthday_count > 0): ?>
                <span class="badge badge-danger rounded-circle noti-icon-badge"><?= $birthday_count; ?></span>
            <?php endif; ?>
        </a>
      <div class="dropdown-menu dropdown-menu-right dropdown-lg">
    <div class="dropdown-item noti-title">
        <h5 class="font-16 m-0">🎉 Birthday Celebrants</h5>
    </div>

    <div class="slimscroll noti-scroll">
        <div class="inbox-widget">
            <!-- Today's Celebrants -->
            <?php if (!empty($today_birthdays)): ?>
                <?php foreach ($today_birthdays as $person): ?>
                    <div class="inbox-item">
                        <div class="inbox-item-img">
                            <img src="<?= base_url(); ?>assets/images/cake.png" class="rounded-circle" alt="Cake" />
                        </div>
                        <p class="inbox-item-author text-success">🎂 <?= $person->last_name . ' , ' . $person->first_name; ?></p>
                        <p class="inbox-item-text text-muted text-truncate">Happy Birthday today!</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="inbox-item">
                    <p class="inbox-item-author text-muted">No birthday today.</p>
                </div>
            <?php endif; ?>

            <hr class="my-1">

            <!-- This Month's Celebrants -->
            <a href="<?= base_url(); ?>Page/birthdays_month" class="dropdown-item text-center text-info">
                🎉 View all <?= count($month_birthdays); ?> celebrants this month
            </a>
        </div>
    </div>
</div>

    </li>
<?php endif; ?>


        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <img src="<?= base_url(); ?>upload/profile/<?php echo $this->session->userdata('avatar');?>" alt="user-image" class="rounded-circle">
                <span class="pro-user-name ml-1">
                    <?php echo $this->session->userdata('fname');?> <i class="mdi mdi-chevron-down"></i> 
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                <!-- Change Profile Pic -->
                <a href="<?= base_url(); ?>Page/changeDP?id=<?php echo $this->session->userdata('username');?>" class="dropdown-item notify-item"> 
                    <i class="mdi mdi-settings-outline"></i>
                    <span>Change Profile Pic</span>
                </a>

                <?php if($this->session->userdata('level') === 'Student'): ?>
                    <a href="<?= base_url(); ?>Page/studentsprofile" class="dropdown-item notify-item">
                        <i class="mdi mdi-account-outline"></i>
                        <span>My Profile</span>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url(); ?>Page/staffprofile?id=<?php echo $this->session->userdata('IDNumber');?>" class="dropdown-item notify-item">
                        <i class="mdi mdi-account-outline"></i>
                        <span>My Profile</span>
                    </a>
                <?php endif; ?>

                <!-- Lock Screen -->
                <a href="<?= base_url(); ?>Page/lockScreen?id=<?php echo $this->session->userdata('username');?>" class="dropdown-item notify-item">
                    <i class="mdi mdi-lock-outline"></i>
                    <span>Lock Screen</span>
                </a>

                <div class="dropdown-divider"></div>

                <!-- Logout -->
                <a href="<?php echo site_url('login/logout');?>" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout-variant"></i>
                    <span>Logout</span>
                </a>
            </div>
        </li>

        <li class="dropdown notification-list">
            <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect">
                <i class="mdi mdi-settings-outline noti-icon"></i>
            </a>
        </li>
    </ul>

    <!-- LOGO -->
    <div class="logo-box">
        <a href="#" class="logo text-center logo-dark">
            <span class="logo-lg">
                <img src="<?= base_url(); ?>assets/images/pms-logo.png" alt="" height="18">
            </span>
            <span class="logo-sm">
                <img src="<?= base_url(); ?>assets/images/pms-logo1.png" alt="" height="22">
            </span>
        </a>

        <a href="#" class="logo text-center logo-light">
            <span class="logo-lg">
                <img src="<?= base_url(); ?>assets/images/pms-logo.png" alt="" height="85">
            </span>
            <span class="logo-sm">
                <img src="<?= base_url(); ?>assets/images/pms-logo1.png" alt="" height="40">
            </span>
        </a>
    </div>

    <!-- MENU -->
    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect">
                <i class="mdi mdi-menu"></i>
            </button>
        </li>

        <li class="d-none d-lg-block">
            <form class="app-search">
                <div class="app-search-box">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search...">
                        <div class="input-group-append">
                            <button class="btn" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </li>
    </ul>
</div>
