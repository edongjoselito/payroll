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
        <i class="fas fa-calendar-check"></i>
        <span> Payroll </span>
        <span class="menu-arrow"></span>
    </a>

    <ul class="nav-second-level nav" aria-expanded="false">
        <li><a href="<?= base_url(); ?>WeeklyAttendance">Attendance</a></li>
        <li><a href="<?= base_url(); ?>WeeklyAttendance/records">View Attendance</a></li>
        <li><a href="<?= base_url('Generatepayroll/form'); ?>">Generate Payroll</a></li>

       
    </ul>
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
        <i class="fas fa-hand-holding-usd"></i>
        <span>Manage Deductions </span>
        <span class="menu-arrow"></span>
    </a>

    <ul class="nav-second-level nav" aria-expanded="false">
         <li><a href="<?= base_url(); ?>Loan/personnel_loan"> Personnel Loans</a></li>
        <li><a href="<?= base_url(); ?>Borrow/cash_advance">Cash Advance</a></li>
        <li><a href="<?= base_url(); ?>Borrow/govt_deductions">Gov't Deductions</a></li>
        <li><a href="<?= base_url(); ?>Borrow/materials_loan">Other Deductions</a></li>
        
    </ul>
</li>

<li>
    <a href="javascript: void(0);" class="waves-effect">
        <i class="fas fa-file-alt"></i>
        <span>View Summaries </span>
        <span class="menu-arrow"></span>
    </a>
     <ul class="nav-second-level nav" aria-expanded="false">
<li><a href="<?= base_url('OtherDeduction/attendance_summary') ?>"></i> Attendance Summary</a></li>
        <li><a href="<?= base_url('OtherDeduction/summary') ?>">Deduction Summary</a></li>
<li><a href="<?= base_url('OtherDeduction/loan_summary') ?>"></i> Loan Summary</a></li>


     </ul>
<li>
    <a href="<?= base_url(); ?>User" class="waves-effect">
        <i class="fas fa-user-cog"></i>
        <span> Manage Users </span>
    </a>
</li>
<li>
    <a href="<?= base_url('Company') ?>" class="waves-effect">
        <i class="fas fa-building"></i>
        <span> Company Information </span>
    </a>
</li>



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