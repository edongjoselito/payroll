<?php include('includes/connections.php'); ?>
<?php include('includes/functions.php'); ?>
<!DOCTYPE html>
<html>

<head>
	<title>Enrolment Report</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="UTF-8">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="assets/plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="assets/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="assets/plugins/summernote/summernote-bs4.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <script>
  function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // Specify file name
    filename = filename?filename+'.xls':'excel_data.xls';
    
    // Create download link element
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
    
        // Setting the file name
        downloadLink.download = filename;
        
        //triggering the function
        downloadLink.click();
    }
}
  </script>
  <style>
      table td
      {
        border:1px solid #000;
      }
      table td table td
      {
        border:0;
      }
      body{
        text-align:center;
      }
	  
	  
	  .form{
		  background-color: #3a0153;
		  color: white;
	  }
    </style>
</head>
  <body>

  <div class="form">
  <br />
		  <form method="post" action="er.php" class="form-inline">
		  <div class="form-group mx-sm-3 mb-2">
			<label for="staticEmail" class="col-sm-2 col-form-label">Semester</label>
			<div class="col-sm-10">
			<select name="sem" class="form-control">
			<option>First Semester</option>
			<option>Second Semester</option>
			<option>Summer</option>
		  </select>
			</div>
		  </div>
		   <div class="form-group mx-sm-3 mb-2">
			<label for="inputPassword" class="col-sm-2 col-form-label">SY</label>
			<div class="col-sm-10">
			  <input type="text" class="form-control" name="sy" placeholder="2021-2022">
			</div>
		  </div>

		   <div class="form-group mx-sm-3 mb-2">
			<div class="col-sm-10">
			<input type="submit" class="btn btn-primary btn-lg" name="submit" value="Submit" />
			</div>
		  </div>

		</form>
		<br />
    </div>

<?php
  if(isset($_POST['submit'])){
  $sem = $_POST['sem'];
  $sy = $_POST['sy'];

?>

<?php 
 $setting_set = mysqli_query($con, "SELECT * FROM o_srms_settings");
 $set = mysqli_fetch_assoc($setting_set);
?>

<br /><br />
<h1 class="textHeader">ENROLLMENT REPORT</h1>
<h2 class="yl"><?php echo $sem; ?>, School Year <?php echo $sy; ?></h2>
<br /><br />

<div class="block"></div>
<div class="mainTable">
<!--<button onclick="exportTableToExcel('tblData', 'ER')">Export To Excel</button>-->
<table class="table" cellspacing="0" cellpadding="0" id="tblData">
   <?php
     $sql  = "SELECT * FROM registration WHERE SY='$sy' ";
     $sql .= "AND Sem='$sem' ";
     $sql .= "GROUP BY StudentNumber ";
     $sql .= "ORDER BY Course, YearLevel, LastName ";
     //$sql .= "LIMIT 1";
     $row_set = mysqli_query($con, $sql);
     if(!$row_set){
       die('Database query failed.' . mysqli_error($con));
     }
   ?>
  <tr>
    <th>No.</th>
    <th>Last Name</th>
    <th>First Name</th>
    <th>M-Name</th>
	<th>Name Extn.</th>
    <th>Sex</th>
    <th>B-Date</th>
    <th>Course</th>
    <th>Year</th>
    <?php
     $count = 0;
     while($count < 20){
    ?>
    <th>Subject</th>
    <th>Unit</th>
    <?php $count++; }?>
    <th>Total Units</th>
  </tr>
  <?php 
   $no = 1;
   while($row = mysqli_fetch_assoc($row_set)){

     $id = $row['StudentNumber'];
     $sql  = "SELECT * FROM studeprofile INNER JOIN registration ON studeprofile.StudentNumber = registration.StudentNumber ";
     $sql .= "WHERE studeprofile.StudentNumber = '$id' ";
     $sql .= "ORDER BY registration.Course, registration.YearLevel, registration.LastName";
     $prof_set = mysqli_query($con, $sql);
     $prof = mysqli_fetch_assoc($prof_set);
  ?>
  <tr>
    <td><?php echo $no; ?></td>
    <td><?php echo utf8_decode($prof['LastName']); ?></td>
    <td><?php echo htmlentities($prof['FirstName']); ?></td>
    <td><?php echo htmlentities($prof['MiddleName']); ?></td>
	<td><?php echo htmlentities($prof['nameExtn']); ?></td>
	<td><?php echo htmlentities($prof['Sex']); ?></td>
    <td><?php echo htmlentities($prof['birthDate']); ?></td>
    <td><?php echo htmlentities($row['Course']); ?></td>
    <td><?php echo htmlentities($row['YearLevel']); ?></td>
     <?php
     //display student by student number, sy and sem
     $sql  = "SELECT * FROM registration ";
     $sql .= "WHERE Sem='$sem' ";
     $sql .= "AND SY='$sy' ";
     $sql .= "AND StudentNumber='$id'";
     $sub_set = mysqli_query($con, $sql);
     if(!$sub_set){
       die('Database query failed.' . mysqli_error($con));
     }
     $s = mysqli_num_rows($sub_set);
     while($sub = mysqli_fetch_assoc($sub_set)){
     ?>
    <td  class="in">
      <table>
        <tr>
          <td><p><?php echo htmlentities($sub['SubjectCode']. ' - ' .$sub['Description']); ?></p></td>
         </tr>
      </table>
    </td>
    <td  class="in">
      <table>
        <tr>
          <?php $to = $sub['LecUnit']+$sub['LabUnit']; ?>
          <td><?php echo htmlentities($to); ?></td>
        </tr>
      </table>
    </td>
    <?php } ?>
    <?php
     $count = $s;
     while($count < 20){
    ?>
    <td  class="in">
      <table>
        <tr>
          <td><p>&nbsp;&nbsp;&nbsp;</p></td>
        </tr>
      </table>
    </td>
    <td  class="in">
      <table>
        <tr>
          <td class="in">&nbsp;&nbsp;&nbsp;</td>
        </tr>
      </table>
    </td>

     <?php $count++; } ?>
     
     <?php
     // get the total sum of the lec tunit column
     $sql  = "SELECT sum(LecUnit) as lec FROM registration WHERE Sem='$sem' AND SY='$sy' AND StudentNumber='$id'";
     $lec_set = mysqli_query($con, $sql);
     $lec = mysqli_fetch_assoc($lec_set);
     // get the total sum of the lab unit column
     $sql  = "SELECT sum(LabUnit) as lab FROM registration WHERE Sem='$sem' AND SY='$sy' AND StudentNumber='$id'";
     $lab_set = mysqli_query($con, $sql);
     $lab = mysqli_fetch_assoc($lab_set);
     // total the lab unit and lec unit
     $total_unit = $lab['lab']+$lec['lec'];
     ?>
     

     <td><?php echo $total_unit; ?></td>

  </tr>

  <?php $no++; } mysqli_free_result($row_set)?>
</table>



<div class="block"></div>
<?php } ?>
</div>

  </body>
</html>