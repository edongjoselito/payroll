<!DOCTYPE html>
<html lang="en">
<?php include(APPPATH . 'views/includes/head.php'); ?>

<body>

    <div id="wrapper">
        <?php include(APPPATH . 'views/includes/top-nav-bar.php'); ?>
        <?php include(APPPATH . 'views/includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h4 class="header-title">Payroll Statement of Account</h4>
                                    <p class="text-muted mb-2">
                                        Cutoff: <strong><?= html_escape($cutoff); ?></strong><br>
                                        Period: <?= html_escape($dateFrom); ?> to <?= html_escape($dateTo); ?>
                                    </p>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Personnel</th>
                                                    <th>Position</th>
                                                    <th class="text-right">Hours</th>
                                                    <th class="text-right">Gross Pay</th>
                                                    <th class="text-right">Loan</th>
                                                    <th class="text-right">Cash Advance</th>
                                                    <th class="text-right">Other Deduction</th>
                                                    <th class="text-right">Gov’t (SSS+Pag-IBIG+PHIC)</th>
                                                    <th class="text-right">Total Deductions</th>
                                                    <th class="text-right">Net Pay</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $grandGross  = 0;
                                                $grandDeduct = 0;
                                                $grandNet    = 0;

                                                if (!empty($payroll)):
                                                    foreach ($payroll as $row):
                                                        $fullName = $row->last_name . ', ' . $row->first_name;

                                                        $gross          = (float)$row->gross;
                                                        $loan           = (float)$row->loan_deduction;
                                                        $cashAdvance    = (float)$row->cash_advance;
                                                        $otherDeduction = (float)$row->other_deduction;
                                                        $govtTotal      = (float)$row->govt_total_deduction;

                                                        $totalDeductions = $loan + $cashAdvance + $otherDeduction + $govtTotal;
                                                        $netPay          = $gross - $totalDeductions;

                                                        $grandGross  += $gross;
                                                        $grandDeduct += $totalDeductions;
                                                        $grandNet    += $netPay;
                                                ?>
                                                        <tr>
                                                            <td><?= html_escape($fullName); ?></td>
                                                            <td><?= html_escape($row->position); ?></td>
                                                            <td class="text-right">
                                                                <?= number_format((float)$row->total_reg_hours, 2); ?>
                                                            </td>
                                                            <td class="text-right"><?= number_format($gross, 2); ?></td>
                                                            <td class="text-right"><?= number_format($loan, 2); ?></td>
                                                            <td class="text-right"><?= number_format($cashAdvance, 2); ?></td>
                                                            <td class="text-right"><?= number_format($otherDeduction, 2); ?></td>
                                                            <td class="text-right"><?= number_format($govtTotal, 2); ?></td>
                                                            <td class="text-right"><?= number_format($totalDeductions, 2); ?></td>
                                                            <td class="text-right"><?= number_format($netPay, 2); ?></td>
                                                        </tr>
                                                    <?php
                                                    endforeach;
                                                else:
                                                    ?>
                                                    <tr>
                                                        <td colspan="10" class="text-center">
                                                            No payroll data found for this cutoff.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>

                                            <?php if (!empty($payroll)): ?>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">TOTAL</th>
                                                        <th class="text-right"><?= number_format($grandGross, 2); ?></th>
                                                        <th colspan="3"></th>
                                                        <th class="text-right"><?= number_format($grandDeduct, 2); ?></th>
                                                        <th class="text-right"><?= number_format($grandDeduct, 2); ?></th>
                                                        <th class="text-right"><?= number_format($grandNet, 2); ?></th>
                                                    </tr>
                                                </tfoot>
                                            <?php endif; ?>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div><!-- end row -->

                </div><!-- container -->
            </div><!-- content -->
        </div><!-- content-page -->
    </div><!-- wrapper -->

    <?php include(APPPATH . 'views/includes/footer.php'); ?>
</body>

</html>