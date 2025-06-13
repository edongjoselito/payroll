<div class="left-side-menu">
    <div class="slimscroll-menu">
        <!-- System Administrator -->
        <?php if ($this->session->userdata('level') === 'Admin'): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">

                    <li class="menu-title">ADMINISTRATION</li>

                    <li>
                        <a href="<?= base_url(); ?>Page/admin" class="waves-effect">
                            <i class="ion-md-speedometer"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="fas fa-archive"></i>
                            <span> Personnel </span>
                            <span class="menu-arrow"></span>
                        </a>

                        <ul class="nav-second-level nav" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Personnel/manage"> Personnel List</a></li>
                        </ul>
                    </li>


                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ion ion-md-cloud-upload"></i>
                            <span> Projects </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="#">Project List</a></li>
                        </ul>
                    </li>


                    <!-- <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ion ion-md-paper"> </i>
                            <span> To Do </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>ToDo/">ToDo</a></li>
                        </ul>
                    </li> -->



                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ion ion-md-settings"></i>
                            <span> Settings </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="#">Company Information</a></li>
                            <li><a href="#">Login Page Image</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ion ion-md-settings"></i>
                            <span> Manage Users </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                             <li><a href="<?= base_url(); ?>Page/userAccounts">Users</a></li>
                        </ul>
                    </li>
                    <!-- 
                    <li>
                        <a href="<?= base_url(); ?>Page/changepassword" class="waves-effect">
                            <i class=" ion ion-ios-key"></i>
                            <span> Change Password </span>
                        </a>
                    </li> -->

                </ul>

            </div>
            <!-- End Sidebar -->

        <?php elseif ($this->session->userdata('level') === 'Super Admin'): ?>
 <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">

                    <li class="menu-title">SUPER ADMIN</li>

                    <li>
                        <a href="<?= base_url(); ?>Page/superAdmin" class="waves-effect">
                            <i class="ion-md-speedometer"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>
                         </ul>

            </div>

        <?php endif; ?>



        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>