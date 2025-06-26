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
                            <i class="fas fa-users"></i>
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
                            <li><a href="<?= base_url(); ?>Project/project_view">Project List</a></li>
                        </ul>
                    </li>

 <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="fas fa-coins"></i>
                            <span> Manage Loans </span>
                            <span class="menu-arrow"></span>
                        </a>

                        <ul class="nav-second-level nav" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Loan/personnel_loan"> Personnel Loans</a></li>
                        </ul>
                        
                    </li>
<li>
    <a href="javascript: void(0);" class="waves-effect">
        <i class="fas fa-hand-holding-usd"></i>
        <span> Borrow </span>
        <span class="menu-arrow"></span>
    </a>

    <ul class="nav-second-level nav" aria-expanded="false">
        <li><a href="<?= base_url(); ?>Borrow/cash_advance">Cash Advance</a></li>
        <li><a href="<?= base_url(); ?>Borrow/materials_loan">Materials</a></li>
    </ul>
</li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="fas fa-cogs"></i>
                            <span> Settings </span>
                            <span class="menu-arrow"></span>
                        </a>

                        <ul class="nav-second-level nav" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Loan/loans_view">Loans</a></li>
                        </ul>
                         
                    </li>
  <!-- <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="loanDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        MANAGE LOAN
    </a>
    <div class="dropdown-menu" aria-labelledby="loanDropdown">
        <a class="dropdown-item" href="<?= base_url('Loan/personnel_loan') ?>">Personnel Loan</a>
    </div>
</li> -->

                  
                        <ul class="nav-second-level" aria-expanded="false">
                            <!-- <li><a href="<?= base_url(); ?>Rate/index">Company Rate</a></li> -->
                            <li><a href="#">Company Information</a></li>
                            <!-- <li><a href="#">Login Page Image</a></li> -->
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