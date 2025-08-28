<?php
defined('BASEPATH') or exit('No direct script access allowed');


$route['default_controller'] = 'Login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['Page/saveAdminFromSuperAdmin'] = 'Page/saveAdminFromSuperAdmin';
$route['Page/saveSuperAdmin'] = 'Page/saveSuperAdmin';
$route['Page/addNewSuperAdmin'] = 'Page/addNewSuperAdmin';

$route['Loan/personnel_loan'] = 'Loan/personnel_loan';

$route['Material/update'] = 'Material/update';
$route['MonthlyPayroll/generate_bimonth']     = 'MonthlyPayroll/generate_bimonth';
$route['MonthlyPayroll/list_bimonth_batches'] = 'MonthlyPayroll/list_bimonth_batches';
$route['MonthlyPayroll/open_bimonth_batch/(:num)'] = 'MonthlyPayroll/open_bimonth_batch/$1';
