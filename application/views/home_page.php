<!DOCTYPE html>
<html lang="en">

<head>
	<?php include('includes/title.php'); ?>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">


	<!--===============================================================================================-->
	<link rel="icon" type="<?= base_url(); ?>assets/image/png" href="<?= base_url(); ?>assets/images/favicon.ico" />
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/vendor/animate/animate.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/vendor/css-hamburgers/hamburgers.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/vendor/select2/select2.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/css/util.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/css/main.css">
	<!--===============================================================================================-->
	<style>
		/* Fix for autofill styling */
		input:-webkit-autofill,
		input:-webkit-autofill:focus,
		input:-webkit-autofill:hover,
		input:-webkit-autofill:active {
			-webkit-box-shadow: 0 0 0 30px #fff8c6 inset !important;
			box-shadow: 0 0 0 30px #fff8c6 inset !important;
			-webkit-text-fill-color: #000 !important;
		}

		/* Force floating label to move up when autofilled */
		input:-webkit-autofill~.focus-input100,
		input:-webkit-autofill~.label-input100 {
			top: -15px;
			font-size: 12px;
			color: #999999;
		}
	</style>

</head>

<body style="background-color: #666666;">

	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">

				<form action="<?php echo site_url('Login/auth'); ?>" method="post" class="login100-form validate-form">
					<span class="login100-form-title p-b-43"></span>
					<span class="login100-form-title p-b-43">
						<img src="<?= base_url(); ?>upload/banners/<?php echo $data[0]->login_form_image; ?>" style="  max-width: 120px; height: auto;" alt="mySRMS Portal">
						<!-- <img src="<?= base_url(); ?>upload/banners/<?php echo $data[0]->login_form_image; ?>" alt="mySRMS Portal" width="100%"> -->
					</span>

					<div style="text-align:center; color:#f8f7fc; background-color:#050168;text-transform:uppercase; style:bold;"><small><?php echo $this->session->flashdata('msg'); ?></small></div>
					<div class="wrap-input100 validate-input" data-validate="Username is required">
						<input class="input100" type="text" autocomplete="off" name="username">
						<span class="focus-input100"></span>
						<span class="label-input100">Username</span>
					</div>


					<div class="wrap-input100 validate-input" data-validate="Password is required">
						<input class="input100" type="password" autocomplete="off" name="password">
						<span class="focus-input100"></span>
						<span class="label-input100">Password</span>
					</div>


					<input class="input100" type="hidden" name="sy" value="<?php echo isset($active_sy) ? $active_sy : ''; ?>">
					<input class="input100" type="hidden" name="semester" value="<?php echo isset($active_sem) ? $active_sem : ''; ?>">


					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Login
						</button>
					</div>

					<div class="text-center p-t-46 p-b-20">
						<span class="txt2">
							<?php if (isset($allow_signup) && $allow_signup == 'Yes') : ?>
								<a href="<?= base_url(); ?>Registration"><span style="color: #3498db">CREATE AN ACCOUNT</span></a> |
							<?php endif; ?>
							<a href="#" data-toggle="modal" data-target="#forgotModal">FORGOT PASSWORD</a>
						</span>
					</div>


				</form>

				<div class="login100-more" style="background-image: url('<?= base_url(); ?>upload/banners/<?php echo $data[0]->loginFormImage; ?>');">


				</div>
			</div>
		</div>
	</div>





	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/vendor/jquery/jquery-3.2.1.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/vendor/animsition/js/animsition.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/vendor/bootstrap/js/popper.js"></script>
	<script src="<?= base_url(); ?>assets/vendor/bootstrap/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/vendor/select2/select2.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/vendor/daterangepicker/moment.min.js"></script>
	<script src="<?= base_url(); ?>assets/vendor/daterangepicker/daterangepicker.js"></script>
	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/vendor/countdowntime/countdowntime.js"></script>
	<!--===============================================================================================-->
	<script src="<?= base_url(); ?>assets/js/main.js"></script>





</body>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotModal" tabindex="-1" role="dialog" aria-labelledby="forgotModalLabel" style="color:black">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="forgotModalLabel">Forgot Password</h4>
			</div>
			<div class="modal-body">
				<form id="resetPassword" name="resetPassword" method="post" action="<?php echo base_url(); ?>login/forgot_pass" onsubmit='return validate()'>
					<div class="input-group mb-3">
						<input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-envelope"></span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<input type="submit" value="Request a New Password" class="btn btn-primary btn-block name=" forgot_pass">
						</div>
						<!-- /.col -->
					</div>
				</form>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

			</div>
		</div>
	</div>
</div>
<!-- End Forgot Password Modal -->

</html>