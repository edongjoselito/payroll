<?php defined('BASEPATH') OR exit('No direct script access allowed');

// $config = array(
//     'protocol' => 'ssmtp', // 'mail', 'sendmail', or 'smtp'
//     'smtp_host' => 'ssl://ssmtp.googlemail.com', 
//     'smtp_port' => 465,
//     'smtp_user' => 'depedroxidev@gmail.com',
//     'smtp_pass' => 'Caballero18',
//     'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
//     'mailtype' => 'text', //plaintext 'text' mails or 'html'
//     'smtp_timeout' => '4', //in seconds
//     'charset' => 'iso-8859-1',
//     'wordwrap' => TRUE
// );

//Gmail

//$config['protocol'] = 'smtp';
//$config['smtp_host'] = 'ssl://smtp.googlemail.com';
//$config['smtp_user'] = 'artadybasty@gmail.com';
//$config['smtp_pass'] = 'lxe08092014';
//$config['smtp_port'] = 465;

//LXE Hosting
$config['protocol'] = 'mail';
$config['smtp_host'] = 'mail.lxeinfotechsolutions.com';
$config['smtp_user'] = 'no-reply@lxeinfotechsolutions.com';
$config['smtp_pass'] = 'moth34board';
$config['smtp_port'] = 465;
 
$config['charset'] = 'utf-8';
$config['mailtype'] = 'html';
$config['newline'] = "\r\n"; 
