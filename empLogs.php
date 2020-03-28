<?php 
    session_start();
    require "db/dbconnect.php";     
    $empID=  $_SESSION['empId'];
    $showOption = $_SESSION['showOption_logs'];
    
    //needed for date filter
    $isDateFilter = $_SESSION['datefilter'];
    
    if($isDateFilter==1){
      $filter_dateStarted = "'" . $_SESSION['def_startDate'] ."'";
      $filter_dateEnd = "'" . $_SESSION['def_endDate'] ."'";
    }
    else{
      $filter_dateStarted = "'" . $_SESSION['startDate'] ."'";
      $filter_dateEnd = "'" . $_SESSION['endDate'] ."'";
    }
    
    
    //needed for edit form
    $employeeName="";
    $workName="";
    $department="";
    $dtrLoc="";
    $min_late;
    $mis_undertime;
    
    

    if(!$con){
      die("Connection Failed:".mysqli_connect_error());
    }

    try{
      $empDetails = mysqli_query($con, "SELECT E.id_employee, concat(E.lname, ', ', E.fname, ' ', left(E.midname,1), '.') AS EName
                                              FROM tbl_employee AS E
                                               WHERE E.is_active = 1 and E.id_employee = $empID;");
      
      $select_query = "SELECT L.id_logs,  E.id_employee, L.work_name, T.location, L.log_stage, 
                              L.duty_in As StartOfDuty,
                              DATE(L.duty_in) AS Duty_Date, 
                              TIME(L.duty_in) As Duty_InTime, TIME(L.duty_out) AS Duty_OutTime,
                              TIME(L.actual_in) AS Actual_timeIn_timeOnly,
                              TIME(L.actual_out) AS Actual_timeOut_timeOnly,
                              L.actual_in AS Actual_timeIn, 
                              L.actual_out As Actual_timeOut,
                              D.name AS Dept, L.min_late, L.min_under, L.is_voided, L.duty_in, L.duty_out
                        FROM tbl_logs AS L 
                            INNER JOIN tbl_employee AS E on  E.id_employee = L.id_employee
                            INNER JOIN tbl_terminal AS T on T.id_terminal = L.id_terminal
                            INNER JOIN tbl_department AS D on D.id_department = L.emp_dept
                        WHERE E.is_active = 1 and E.id_employee = $empID";
          
          if($showOption == 1){
            $select_query .= " AND ((L.log_stage = 2 OR L.log_stage = 3) AND (is_voided = 0))";
          }
          else if ($showOption == 2){
            $select_query .= " AND  (L.log_stage = 1 AND is_voided = 0)";
          }
          else if ($showOption == 3){
            $select_query .= " AND  (L.log_stage <= 3 AND is_voided = 0)";
          }
          else if ($showOption == 4){
            $select_query .= " AND  is_voided = 1";
          }
          else if ($showOption == 5){
            $select_query .= " AND  (is_voided = 1 OR (L.log_stage = 2 OR L.log_stage = 3))";
          }
          else if ($showOption == 6){
            $select_query .= " AND  (is_voided = 1 OR L.log_stage = 1)";
          }

        
          $select_query .= " AND (DATE(L.duty_in) >= Date($filter_dateStarted) AND  DATE(L.duty_in)  <= DATE($filter_dateEnd))";
          
          $select_query .= " ORDER BY DATE(L.duty_in);";

          $query = mysqli_query($con,$select_query);
     }
    catch(Exception $e){

       echo 'Caught exception: ', $e->getMessage(), "\n";

     }  
   // echo $select_query;
?> 

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>DTR LOG VIEWER</title>
  <?php
		
		//Check for Session ID
		if (!isset ($_SESSION['login_userId']))
		{
			header("Location:login.php");
    }
    else{
      if(!isset($_SESSION['login_empId'])){
        header("Location:index.php");
      }
    }
    
  ?> 
  <!-- icon on the title -->
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

  <!-- Custom styles for dataTable  -->
  <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

  <style>
    #btnFilter{
      float: left;
      margin-left:370px
    }
    
    #empImage{
      border-radius: 15px;
      border: 1px solid #ddd;
      padding: 5px;
      max-width: 100%;
      float: left;
    }
    #empImage:hover{
      box-shadow: 0 0 4px 4px rgba(0, 140, 186, 0.5);
    }
    #empName{
      position: relative; 
      font-size: 25px;
      color: red;
      left: 10px;
      top: -8px; 
    }
   
    fieldset 
	  {
		  border: 1px solid #ddd !important;
		  margin: 0;
		  xmin-width: 0;
		  padding: 10px;       
		  position: relative;
		  border-radius:4px;
		  background-color:#f5f5f5;
		  padding-left:10px!important;
	  }	
	
		legend
		{
			font-size:14px;
			font-weight:bold;
			margin-bottom: 0px; 
			width: 35%; 
			border: 1px solid #ddd;
			border-radius: 4px; 	 
			background-color: #ffffff;
      padding:0 10px; /* To give a bit of padding on the left and right */
      border-bottom:none;
		} 

    table thead th {
      white-space: nowrap
    }
    
    .dataTables_paginate {
      float: right;
      margin:0;
    }
    
  </style>

</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-danger sidebar sidebar-dark accordion" id="accordionSidebar">

       <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15 ">
          <!-- <i class="fas fa-laugh-wink"></i> -->
        </div>
        <div class="sidebar-brand-text mx-3">ADMIN PANEL </div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

     <!-- Nav Item - Dashboard -->
      <li class="nav-item ">
        <a class="nav-link" href="index.php">
            <i class="fas fa-user"></i>
            <span>Employee List</span>
        </a>
        <a class="nav-link" href="emplogs.php">
            <i class='fas'>&#xf2bb;</i>
            <span>Employee Log Viewer</span>
        </a>
      </li>
      <!-- Divider -->
      <hr class="sidebar-divider">  

      <!-- Heading -->
      <!-- <div class="sidebar-heading">
        Interface
      </div> -->

     

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->



    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
              <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
              </a>
            
              <div class="topbar-divider d-none d-sm-block"></div>

              <!-- Nav Item - User Information -->
              <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['empName']; ?></span>
                  <!-- for user's pic to show -->
                  <div id="userPic"> </div>  
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                  <!-- <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                      </a>
                      <a class="dropdown-item" href="#">
                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                        Settings
                      </a>
                      <a class="dropdown-item" href="#">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                          Activity Log
                      </a>
                      <div class="dropdown-divider"></div> -->
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                      Logout
                  </a>
                </div>
              </li>
            </li>
          </ul>

        </nav>
        <!-- End of Topbar -->

        
        <div class="container-fluid">
          <!-- Page Heading -->
          <h1 class="h3 mb-2 font-weight-bold text-danger">Employee's Log Viewer</h1>
          <div class="card shadow mb-4">
              <div class="card-header py-3">
                  <h6 id="title" class="m-0 font-weight-bold text-danger" align="center">Search Logs by Date Range</h6>
              </div>
              
              <!-- EMPLOYEE LOGS with Actual In's only-->
              <div class="card-body" id="divEmployeeLogs">
                <div class="container" id="groupbox">
                  <div class="row"> <!-- row b4 dataTable -->
                    <div class="col-3"> <br /> 
                      <div id="divImage">  </div> 
                    </div> 
                    <div class="col-9" >
                        <br /> 
                      <div class="row"> <!-- row Employee Name: -->
                        <div class="col-3">
                          <label class="label">Employee Name:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left">
                          <?php
                            while($result_empDetails= mysqli_fetch_assoc($empDetails)){?>
                              <p id="empName"> <strong><?php 
                                                          $employeeName = $result_empDetails['EName'];
                                                          echo $result_empDetails['EName'];  
                                                      ?> 
                                              </strong>
                              </p><br />
                          <?php } ?>
                        </div>  
                      </div>
                      <div class ="row" > <!-- Groupbox -->
                          <fieldset class="border p-2 col-12" >
                              <legend class="w-auto"><h6>Filter Date:</h6></legend>
                              <div class="row">
                                <div class="col-5">
                                  <div class="row">
                                    <div class="col-1">&nbsp;
                                    </div>
                                    <div class="col-2">
                                      <label class="label">FROM:</label>
                                    </div>
                                    <div class="col-3">
                                      <input class="form-control" id="startDate" name="startDate" placeholder="Start Date" type="date" style="width:180px"/>
                                    </div>
                                  </div>
                                  <div class="row">&nbsp;</div>  
                                  <div class="row">
                                    <div class="col-1">&nbsp;
                                    </div>
                                    <div class="col-2">
                                      <label class="label">TO:</label>
                                    </div>
                                    <div class="col-3">
                                      <input class="form-control" id="endDate" name="endDate" placeholder="End Date" type="date" style="width:180px"/>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-7">
                                  <fieldset class="border p-2 col-12">
                                    <legend style='background-color: #f5f5f5'><h6>Show Option:</h6></legend>
                                    <div class="row"> 
                                       <div class="col-2 text-right">
                                        <input type="checkbox"  data-role='emplogs_toggle1' name="chkBx_empLogs" id="chkEmpLogsOnly" value="Logged Schedules">
                                      </div>
                                      <div class="col-8">
                                          <label class="custom-label" id="loggedSched"><strong>Logged Schedules</strong></label>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="col-2 text-right">
                                        <input type="checkbox"  data-role='emplogs_toggle2' name="chkBx_empLogs" id="chkEmpAbsentedOnly" value="Absented Schedules">
                                      </div>
                                      <div class="col-8">
                                        <label class="custom-label" id="absentedSched"><strong>Absented Schedules</strong></label>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="col-2 text-right">
                                        <input type="checkbox"  data-role='emplogs_toggle3' name="chkBx_empLogs" id="chkEmpVoidedOnly" value="Voided Schedules">
                                      </div>
                                      <div class="col-8">
                                        <label class="custom-label" id="voidSched"><strong>Voided Schedules</strong></label>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="row text-right">
                                        <div class="col-12">
                                          <button class="btn btn-danger btn-sm" id="btnFilter" data-role="filter" >
                                            <span class="icon text-white-50"><i class='fas fa-search'></i></span>
                                            <span class="text">FILTER</span>
                                          </button> 
                                        </div>
                                      </div>
                                    </div>
                                  </fieldset>
                                </div>
                              </div>
                          </fieldset>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              <!-- /EMPLOYEE LOGS with Actual In's only-->
              
              <div class="card-body" id="tblLogs">  <!--card-body TABLE -->
                <div class="table-responsive" id="divDataTable_empLogHis"> <!-- table-responsive -->         
                  <table class="table table-bordered table-bordered table-sm" id="dataTable" width="100%" cellspacing="0"> <!-- table-bordered -->
                    <thead>
                      <tr class="text-center">
                        <th><strong>DATE</strong></th>    <!-- column0 -->
                        <th><strong>DUTY IN</strong></th> <!-- column1 -->
                        <th><strong>DUTY OUT</strong></th> <!-- column2 -->
                        <th><strong>WORK NAME</strong></th> <!-- column3 -->
                        <th><strong>DTR TERMINAL</strong></th> <!-- column4 -->
                        <th><strong>LOG IN</strong></th> <!-- column5 -->
                        <th><strong>LOG OUT</strong></th> <!-- column6 -->
                        <th style="display:none"></th> <!-- column7 for lates -->
                        <th style="display:none"></th> <!-- column8  for undertime-->
                        <th style="display:none"></th> <!-- column9 for log_stage-->
                        <th style="display:none"></th> <!-- column10 for actualIn_TIME_only-->
                        <th style="display:none"></th> <!-- column11 for actualOut_TIME_only-->
                        <th style="display:none"><strong>VOIDED</strong></th>  <!-- column12 -->
                        <th><strong>ACTION</strong></th> <!-- column13 -->
                      </tr>
                    </thead>
                    <tfoot >
                    </tfoot>
                    <tbody class="text-nowrap">
                      <?php
                        if(isset($query)){  
                          $rowCount = mysqli_num_rows($query);

                          if($rowCount == 0) {
                          }
                          else{
                            while($result_empLogs = mysqli_fetch_assoc($query)){ 
                              $department = $result_empLogs['Dept'];
                              ?>
                              <tr id="<?php echo $result_empLogs['id_logs']; ?>">
                                <!-- column0 -->
                                <td data-target="dutyDate">
                                    <?php echo $result_empLogs['Duty_Date']; ?> 
                                </td>
                                <!-- column1 -->
                                <td data-target="dutyInTime"><?php 
                                                                   
                                                                   $DutyIn = new DateTime($result_empLogs['Duty_InTime']);
                                                                  echo $DutyIn->format('g:i A');
                                                              ?>
                                </td>
                                <!-- column2 -->
                                <td data-target="dutyOutTime"><?php 
                                                                   $DutyOut = new DateTime($result_empLogs['Duty_OutTime']);
                                                                   echo $DutyOut->format('g:i A');
                                                              ?>
                                </td>
                                <!-- column3 -->
                                <td data-target="workName"><?php 
                                                                 $workName = $result_empLogs['work_name'];
                                                                  echo $result_empLogs['work_name']; 
                                                            ?>
                               </td>
                               <!-- column4 -->
                                <td data-target="location"><?php 
                                                                 $dtrLoc = $result_empLogs['location'];
                                                                 echo $result_empLogs['location'];
                                                           ?>
                                </td>
                                <!-- column5 -->
                                <td data-target="LogIn"><?php
                                                            if($result_empLogs['is_voided']==1){
                                                              echo "VOIDED";
                                                            }
                                                            else{
                                                              if($result_empLogs['log_stage'] == 1){
                                                                echo "MISSED";
                                                              }
                                                              else{
                                                                $actualIn = new DateTime($result_empLogs['Actual_timeIn']);
                                                                //echo $actualIn->format('M d, Y  - g:i A');
                                                                echo $actualIn->format('g:i A');
                                                              }
                                                            }
                                                        ?>
                                </td>
                                <!-- column6 -->
                                <td data-target="LogOut"><?php
                                                              if($result_empLogs['is_voided']==1){
                                                                echo "VOIDED";
                                                              }
                                                              else{
                                                                if($result_empLogs['log_stage'] < 3 ){
                                                                  echo "MISSED";
                                                                }
                                                                else{
                                                                  $actualOut = new DateTime($result_empLogs['Actual_timeOut']);
                                                                  //echo $actualOut->format('M d, Y  - g:i A');
                                                                  echo $actualOut->format('g:i A');
                                                                }
                                                              }
                                                                
                                                        ?>
                                </td>
                                <!-- column7 for lates -->
                                <td style="display:none"> 
                                      <?php $min_late = $result_empLogs['min_late']; 
                                            echo $min_late; ?>
                                </td>
                                <!-- column8 for undertime-->
                                <td style="display:none"> 
                                      <?php $min_undertime = $result_empLogs['min_under']; 
                                            echo $min_undertime; ?>
                                </td>
                                <!-- column9 for log_stage -->
                                <td style="display:none"> 
                                      <?php $logstage =$result_empLogs['log_stage'];
                                            echo $logstage; ?>
                                </td>
                                <!-- column10 for ActualIn_Time_Only -->
                                <td style="display:none" > 
                                      <?php 
                                            $actualTime = new DateTime($result_empLogs['Actual_timeIn_timeOnly']);
                                            echo $actualTime->format('g:i A'); 
                                      ?>
                                          
                                </td>
                                 <!-- column11 for ActualOut_Time_Only -->
                                 <td style="display:none" > 
                                      <?php 
                                            $actualTimeOut = new DateTime($result_empLogs['Actual_timeOut_timeOnly']);
                                            echo $actualTimeOut->format('g:i A'); 
                                      ?>
                                          
                                </td>
                                <td style="display:none" data-target="isvoid" class="text-center">
                                    <?php 
                                          if($result_empLogs['is_voided']==1){
                                            echo '<img src="img/yes.jpg"  height="30" width="30" />';
                                          }
                                          else{
                                            echo '<img src="img/no.jpg" height="20" width="20"/>';
                                          }
                                          
                                    ?>                           
                                </td>
                                <td>
                                  <button data-toggle="editLogs" title="EDIT" data-role="editLogs" class="btn btn-success btn-sm btn-circle"  
                                          data-logsid="<?php echo $result_empLogs['id_logs']; ?>" data-dutyIn="<?php echo $result_empLogs['duty_in'];?>"
                                          data-dutyOut="<?php echo $result_empLogs['duty_out'];?>"
                                          data-isvoided="<?php echo $result_empLogs['is_voided']; ?>" style="width:18%" name='btnViewReport' id="editLogs">
                                      <span class='icon text-white-50'>
                                       <i class='far'>&#xf044;</i> 
                                      </span>
                                    <!-- <span class='text'>EDIT </span> -->
                                  </button>
                                  &nbsp; 
                                  <button data-toggle="showDetails" title="SHOW" data-role="viewReport" class="btn btn-primary btn-sm btn-circle"  data-logsid="<?php echo $result_empLogs['id_logs']; ?>" 
                                          data-isvoided="<?php echo $result_empLogs['is_voided']; ?>" style="width:18%" name='btnViewReport' id="viewReport">
                                    <span class='icon text-white-50'>
                                     <i class='fas'>&#xf4fd; </i>
                                    </span>
                                   <!-- <span class='text'>SHOW</span> -->
                                  </button>
                                  &nbsp;
                                  <button data-toggle="voidLog" title="VOID" data-role="cancelLogs" class="btn btn-danger btn-sm btn-circle"  data-logsid="<?php echo $result_empLogs['id_logs']; ?>" 
                                          data-isvoided="<?php echo $result_empLogs['is_voided']; ?>" style="width:18%" name='btnViewReport' id="voidLogs">
                                    <span class='icon text-white-50'>
                                     <i class='fas'>&#xf410; </i>
                                    </span>
                                    <!-- <span class='text'>VOID</span> -->
                                  </button>
                                  &nbsp;
                                  <button data-toggle="audittrail" title="AUDIT TRAIL"data-role="viewAuditTrail" class="btn btn-warning btn-sm btn-circle"  data-logsid="<?php echo $result_empLogs['id_logs']; ?>" 
                                          style="width:18%" name='btnViewReport' id="viewAuditTrail">
                                    <span class='icon text-white-50'>
                                     <i class='fas'>&#xf1da; </i>
                                    </span>
                                    <!-- <span class='text'>AUDIT</span> -->
                                  </button>

                                </td>
                              </tr> 
                      <?php }
                          }
                          mysqli_close($con);
                        } 
                        else{
                          echo "ERROR IN QUERY FOR DATATABLE";
                        }
                      ?>
                    </tbody>
                  </table>
              </div>
          </div>
        </div>
      </div><!-- wrapper -->

  </div>  <!-- End of Main Content -->
    

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
          <span>Copyright &copy; 2019 Abrie Software Innovations</span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <form action="logout.php">
            <button type="submit" name="logout" class="btn btn-danger" >Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal fade" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Employee's Log Editor</h5 >
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
              <div class="row">
                <div class="col-3">
                  <div id="editdivImage" >  </div> 
                </div>
                <div class="col-9">
                  <div class="row">
                    <div class="col-4">
                      <label class="label">Employee Name:&nbsp;</label>
                    </div>
                    <div class="col-8">
                      <label class="label"><strong><?php echo $employeeName; ?></strong></label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-4">
                      <label class="label">Work Name:&nbsp;</label>
                    </div>
                    <div class="col-8">
                      <label class="label"><strong><?php echo $workName; ?></strong></label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-4">
                      <label class="label">Department:&nbsp;</label>
                    </div>
                    <div class="col-8">
                      <label class="label"><strong><?php echo $department; ?></strong></label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-4">
                      <label class="label">DTR Location:&nbsp;</label>
                    </div>
                    <div class="col-8">
                      <label class="label"><strong><?php echo $dtrLoc; ?></strong></label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-4">
                      <label class="label">Reason for editing:&nbsp;</label>
                    </div>
                    <div class="col-8">
                    <textarea class="form-control" rows="3" id="comment"></textarea>
                    </div>
                  </div>
                </div>
                
              </div>
              <br/>
              
                      
              <!-- LOGIN DETAILS -->
              <div class="row"> </div>
              <div class="row">
                  <div class="col-4">
                    <label class="label"><strong>LOGIN Details:</strong>&nbsp;</label>
                  </div>
              </div>
              <div class="row">
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <div class="col-2">
                    <label class="label">Start of duty:&nbsp;</label>
                  </div>
                  <div class="col-4 text-left">
                    <strong><label id="edit_dutyDate"></label></strong>
                  </div>
              </div>
              <div class="row">
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <div class="col-2">
                    <label class="label">Login time:</label>
                  </div>
                  <div class="col-3 text-left">
                  <strong>
                    <label id="lblActualIn" ></label>
                    <div>
                      <input type="time" id="txtActualIn" class="form-control">                      
                    </div>
                    </strong>
                  </div>
                  <div class="col-2 text-right">
                         <button type="button" data-toggle="editLogin" title="Click to EDIT the LOGIN!" 
                                data-placement="bottom" class="btn btn-success" style="width:100%" id="btnEditLogin">
                          <span class='icon text-white-50'>
                            <i class='far'>&#xf044;</i>
                          </span> 
                          EDIT
                        </button> 
                       
                  </div>
                  <div class="col-4 text-left">
                    <button type="button"  data-toggle="removeLogin" title="Click to remove the login!" class="btn btn-danger" style="width:100%" id="btnRemoveLogin">
                        <span class='icon text-white-50'>
                            <i class='fas'>&#xf235;</i> 
                        </span> 
                        REMOVE LOGIN
                    </button> 
                    
                  </div>
              </div>
              <div class="row">
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <div class="col-3">
                    <label id="lbllate" class="label">Minutes late:&nbsp;</label>
                  </div>
                  <div class="col-1 text-left">
                  <strong><label id="edit_minlate"></label></strong>
                  </div>
              </div>
              <!-- end of LOGIN DETAILS -->

              <!-- LOGOUT DETAILS -->
              <br/>
              <div class="row"> </div>
              <div class="row">
                  <div class="col-4">
                    <label class="label"><strong>LOGOUT Details:</strong>&nbsp;</label>
                  </div>
              </div>
              <div class="row">
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <div class="col-2">
                    <label class="label">End of duty:&nbsp;</label>
                  </div>
                  <div class="col-4 text-left">
                    <strong><label id="edit_OutdutyDate"></label></strong>
                  </div>
              </div>
              <div class="row">
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <div class="col-2">
                    <label class="label">Logout time:&nbsp;</label>
                  </div>
                  <div class="col-3">
                    <strong>
                    <label id="lblActualOut" ></label>
                    <input type="time" id="txtActualOut" class="form-control">
                    </strong>
                  </div>
                  <div class="col-2 text-right">
                        <button type="button" data-toggle="editLogout" title="Click to EDIT the LOGOUT!"
                                data-placement="bottom" class="btn btn-success" style="width:100%" id="btnEditLogout">
                          <span class='icon text-white-50'>
                            <i class='far'>&#xf044;</i> 
                          </span>
                          EDIT
                        </button>
                       
                  </div>
                  <div class="col-4 text-left ">
                        <button type="button" data-toggle="removeLogout" title="Click to REMOVE the LOGOUT!"
                                data-placement="bottom" class="btn btn-danger" style="width:100%" id="btnRemoveLogout">
                            <span class='icon text-white-50'>
                              <i class='fas'>&#xf235;</i> 
                            </span> 
                            REMOVE LOGOUT
                        </button>
                        
                  </div>
              </div>
              <div class="row">
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <div class="col-3">
                    <label class="label">Minutes undertime:&nbsp;</label>
                  </div>
                  <div class="col-2 text-left">
                    <strong><label id="edit_minunder"></label></strong>
                  </div>
              </div>
              <!-- end of LOGOUT DETAILS -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary pull-right" name="update" id="btnUpdate">Update</button>
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal" id="btnClose_edit">Close</button>
          </div>
          </form>
        </div>

      </div>
  </div>
  <!-- eDIT Modal-->

  <!-- Modal for No selected Dates from filter command -->
  <div class="modal fade" id="alertNoselectedDates" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">NO DATES AND SHOW OPTIONS TO FILTER!</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Please enter dates to filter or select an option to show.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal  No selected Dates-->         

  <!-- Modal for Invalid Date Range from filter commnad -->         
  <div class="modal fade" id="alertInvalidDates" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">INVALID DATE(S)</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Pleas enter valid date range!</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div> 
  <!-- Modal  Invalid Date Range--> 

  <!-- Modal for Invalid Edit Time after Editing Login/logout -->
  <div class="modal fade" id="alertInvalidTime" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">INVALID EDIT TIME</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Please enter valid edited log!
        <br>
        <p id="message"></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div> 
  <!-- Modal  Invalid Edit Time -->

  <!-- Modal No Changes has been made -->
   <div class="modal fade" id="noChanges_forUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">NOTHING TO UPDATE</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
          <div class="col-2 text-right">
            <i class="material-icons" style="font-size:48px;color:red">error</i>
          </div>
          <div class="col-9 text-left">
            <br/>
            All new values are the same from old values.
          </div>
          </div>
          
        </div>
        <div class="modal-footer">  
        </div>
      </div>
    </div>
  </div> 
  <!-- Modal No Changes has been made -->

   <!-- Modal for Update -->
  <div class="modal fade" id="UpdateForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="exampleModalLabel">UPDATE CONFIRMATION</h6>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Please review the following changes for <label id="dateSched"></label> :<label id="dateSched"></label><br /> <br />
          <table class= "table table-bordered table-bordered table-sm" width="100%" cellspacing="0">
            <thead>
              <tr class="text-center">
                <th> DESCRIPTION</th>
                <th>ORIGINAL VALUE</th>
                <th>NEW VALUE </th>
              </tr>
            </thead>
            <tfoot>
            </tfoot>
            <tbody class="text-nowrap">
              <tr>
                <td>Login time</td>
                <td id="oldLogin"></td>
                <td id="newLogin"></td>
              </tr>
              <tr>
                <td>Late</td>
                <td id="oldLate"></td>
                <td id="newLate"></td>
              </tr>
              <tr>
                <td>Logout time</td>
                <td id="oldLogout"></td>
                <td id="newLogout"></td>
              </tr>
              <tr>
                <td>Undertime</td>
                <td id="oldUndertime"></td>
                <td id="newUndertime"></td>
              </tr>
            </tbody>
          </table>
          <br />
          Reason for EDITING: &nbsp; <label id="lblReasonForEdit"></label>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal" id="updateConfirm_cancel">Cancel</button>
          <form>
            <button type="button" name="logout" class="btn btn-danger" id="confirm_update" >Confirm</button>
          </form>
        </div>
      </div>
    </div>
  </div> 
  <!-- Modal for Update -->

  <!-- Modal for Void -->
  <div class="modal fade" id="voidModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">VOID LOG CONFIRMATION</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <label class="label">Reason for voiding:&nbsp;</label>
          </div>
          <div class="row">
          <textarea class="form-control" rows="5" id="commentVoid"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <form>
            <button type="button" name="confirm" class="btn btn-danger" id="confirm_Void" >Confirm</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal for Void -->


 <!-- Modal for Success Update -->
  <div class="modal fade" id="successUpdateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <form action="logout.php">
            <button type="submit" name="logout" class="btn btn-danger" >Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal for Success Update -->
 



  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin-2.min.js"></script>

  <!-- Page level custom scripts for dataTable -->
  <script src="vendor/datatables/jquery.dataTables.min.js"></script> 
  <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Moment.js Library -->
  <script src="lib/moment.min.js"></script>
 
  <script>
   
    $(document).ready(function() {
      //declaration of variables
       var dateStart=null;
       var dateEnd=null;
       var fullDate = new Date();
       var GetMonth;
       var GetDate;
       var CurDate=null;
       var prevDate=null;
       var DutyDate;
       var logstage;
       var startOfDuty;
       var is_voided; //variable for is_voided data from database, for selected record only
       var actualIn;  //variable for login from database
       var actualOut; //variable for logout from database
       var undertime;  //variable for undertime from database
       var lates; //variable for late from database
       var id_logs;//variable for id_logs from database
       var startTimeIn; //variable container for expected login of the employee
       var endTimeOut; //variable container for expected logout of the employee
       var editLogin; //variable cONTAiner for edit login from EDIT FORM
       var editLogout; //variable cONTAiner for edit logout from EDIT FORM
       var minute_allowed; //variable for minute allowed after editing login/logout
       var editedLogin_flag; //indicator for editedLogin and basis for Update Button settings
       var removedLogin_flag;//indicator for removedLogin and basis for Update Button settings
       var editedLogout_flag;//indicator for editedLogout and basis for Update Button settings
       var removedLogout_flag;//indicator for removedLogout and basis for Update Button settings
       var newLogin_DateTime;
       var newLoginTime;
       var newLogout_DateTime;      
       var newLogoutTime;
       var login_status_edit; //indicator for newLogstage value
       var logout_status_edit; //indicator for newLogstage value
       var newLogstage;
       var comments_flag;
       var editComments; 
       var dutyIn_actualFormat, dutyOut_actualFormat;
       
      
    
      
       

       //declaration for Show Options
       var isLoggedSched = false;
       var isAbsentedSched = false;
       var isVoidSched = false;
       var resultShowOption = 0;

       //declare array of month
       var month = new Array();
            month[0] = "January";
            month[1] = "February";
            month[2] = "March";
            month[3] = "April";
            month[4] = "May";
            month[5] = "June";
            month[6] = "July";
            month[7] = "August";
            month[8] = "September";
            month[9] = "October";
            month[10] = "November";
            month[11] = "December";

     
     
      // settings after loading the page 
      $('#sidebarToggle').trigger('click');
     
      $('#dataTable').DataTable({
        searching:false,  
        responsive: true
      }); //initialize id=dataTable as DataTable
      
      //for checkboxes font style
      <?php if( ($showOption==1) OR ($showOption==3) OR ($showOption==5) OR ($showOption==7) ) { ?>
            isLoggedSched = true;
            checkbox_label_style();
      <?php } ?>
      <?php if( ($showOption==2) OR ($showOption==3) OR ($showOption==6) OR ($showOption==7) ) { ?>
            isAbsentedSched = true;
            checkbox_label_style();
      <?php } ?>
      <?php if( ($showOption==4) OR ($showOption==5) OR ($showOption==6) OR ($showOption==7) ) { ?>
            isVoidSched = true;
            checkbox_label_style();
      <?php } ?>
      //load user's picture
      getUserPic();
      //load employee's picture   
      loadEmpImage(); 

      //get current Date
      GetCurrentDate();

      getShowOption_result();
     
      showDefaultFilteredDate();
      $("#startDate").focus();
     

      //events happened during running time
        //filter button click event
        $(document).on('click','button[data-role=filter]',function(){
          if((dateStart == null || dateEnd == null) && resultShowOption == 0)  {
            $('#alertNoselectedDates').modal('show');
          }
          else{
            if(dateStart == null || dateEnd == null){
              $.ajax({
                   url: 'db/session_empid.php',
                   method: 'post',
                   data:{ empId:<?php echo $empID; ?>,
                          showOption:resultShowOption, 
                          filter:1},
                   success: function(data){
                     location.reload(true);
                   }
               });
            }
            else{
              $.ajax({
                   url: 'db/session_empid.php',
                   method: 'post',
                   data:{ empId:<?php echo $empID; ?>,
                          showOption:resultShowOption, 
                          filter:2,
                          dateStart:dateStart,
                          dateEnd:dateEnd},
                   success: function(data){
                     location.reload(true);
                   }
              });

            }
              
          }
        });

        //edit button tooltip
        $('[data-toggle="editLogs"]').tooltip();

        //show detatils button tooltip
        $('[data-toggle="showDetails"]').tooltip();

        // void logs button tooltip
        $('[data-toggle="voidLog"]').tooltip();

        // show audit trail button tooltip
        $('[data-toggle="audittrail"]').tooltip();
        

        //edit login button tooltip
        $('[data-toggle="editLogin"]').tooltip();

        //undo edit login button tooltip
        $('[data-toggle="undo_editLogin"]').tooltip();

        //remove login button tooltip
        $('[data-toggle="removeLogin"]').tooltip();


        //edit logout button tooltip
        $('[data-toggle="editLogout"]').tooltip();

        //undo edit logout button tooltip
        $('[data-toggle="undo_editLogout"]').tooltip();

        //remove logout button tooltip
        $('[data-toggle="removeLogout"]').tooltip();



        //edit button click event from table to show modal edit form
        $('.table tbody').on('mouseover','#editLogs',function(){
          is_voided = $(this).attr("data-isvoided");

          if(is_voided==1){
            $(this).attr('disabled',true);
            $('[data-toggle="editLogs"]').tooltip('hide');
          }
        });

        $('.table tbody').on('click','#editLogs',function(){
          $('[data-toggle="editLogs"]').tooltip('hide');
          id_logs = $(this).attr("data-logsid");
          dutyIn_actualFormat = $(this).attr("data-dutyIn");
          dutyOut_actualFormat = $(this).attr("data-dutyOut");

         
         
          $("#editModal").modal();
          edit_loadEmpImage();
         
      
          editedLogin_flag=0; 
          removedLogin_flag=0;
          editedLogout_flag=0;
          removedLogout_flag=0;
          comments_flag=0;
          newLogstage=0;
          newLoginTime = null;
          newLogoutTime = null;
          newLogin_DateTime = null;
          newLogout_DateTime = null;
          
          btnUpdate_settings();

          //to secure the correct settings after the edit button was pressed
          $('#lblActualIn').show();
          $('#btnEditLogin').show();    
          $('#btnRemoveLogin').show();

          $('#lblActualOut').show();
          $('#btnEditLogout').show();
          $('#btnRemoveLogout').show();


          $('#txtActualIn').val('');
          $('#txtActualIn').hide(); //input type=time from edit form
          $('#txtActualOut').val("");
          $('#txtActualOut').hide();//input type=time from edit form
         

          $("#btnUpdate").attr("disabled", true);
          $('#comment').val('');
          $('#comment').focus();
          
          

          //get the value of needed cell within that row
          var current_row = $(this).closest('tr');
          startOfDuty = new Date(current_row.find('td:eq(0)').text()); //column 1
          startTimeIn = current_row.find('td:eq(1)').text();  //column 2
          endTimeOut = current_row.find('td:eq(2)').text(); //column 3
          lates = current_row.find('td:eq(7)').text(); //column 8
          undertime = current_row.find('td:eq(8)').text(); //column 9
          logstage = current_row.find('td:eq(9)').text(); //column 10
          actualIn =  current_row.find('td:eq(10)').text(); //column 11
          actualOut =  current_row.find('td:eq(11)').text();//column 12
          
          
        
          //FOR LOGIN DETAILS
          //pass the value to the element of modal form - start of duty
                                  //month below is an array
          $('#edit_dutyDate').html(month[startOfDuty.getMonth()] + ' ' + startOfDuty.getDate()  + ', ' + startOfDuty.getFullYear() + ' - ' +  startTimeIn );
          
          var getMonth_Duty = startOfDuty.getMonth()+1;
          if(getMonth_Duty<10){
            getMonth_Duty = "0"+getMonth_Duty;
          }
         
          DutyDate = startOfDuty.getFullYear()+"-"+ getMonth_Duty + "-" + startOfDuty.getDate();

          //pass the value to the element of modal form- actual_timeIn
          if((logstage==2) || (logstage==3)){
            $('#lblActualIn').html(actualIn);
         
          }
          else{
            $('#lblActualIn').html('No Logged IN');
          }

          //pass the value to the element of modal form - minutes lates
          if(logstage==1){
            $('#edit_minlate').html('N/A');
          }
          else{
            $('#edit_minlate').html(lates);
          }
         
         //FOR LOGOUT DETAILS
          //pass the value to the element of modal form- end of duty
          $('#edit_OutdutyDate').html(month[startOfDuty.getMonth()] + ' ' + startOfDuty.getDate()  + ', ' + startOfDuty.getFullYear() + ' - ' +  endTimeOut );

          //pass the value to the element of modal form- actual_timeOut
          
          if(logstage==3){ 
           $('#lblActualOut').html(actualOut);

          }
          else{
            $('#lblActualOut').html('No Logged OUT');
          }

          //pass the value to the element of modal form - undertime
          if(logstage<3){
            $('#edit_minunder').html('N/A');
          }
          else{
            $('#edit_minunder').html(undertime);
          }

          //call the rules for buttons
          remove_login_rules();
          edit_logout_rules();
          remove_logout_rules();
          login_logout_flag_status();
        });
        
        //modal for edit form
        $('#editModal').on('shown.bs.modal', function () {
            $('#comment').trigger('focus')
        });

        //show button click event
        $(document).on('click','button[data-role=viewReport]',function(){  
          var idLogs = $(this).data('logsid');
        
          $.post("db/session_idLogs.php",{idLogs:idLogs},
                    function(data){
                      location.replace("empLogReport.php");
                    }
          );
        });

        //void button
        $('.table tbody').on('mouseover','#voidLogs',function(){
          is_voided = $(this).attr("data-isvoided");

          if(is_voided==1){
            $(this).attr('disabled',true);
            $('[data-toggle="voidLog"]').tooltip('hide');
          }
        });

        $('.table tbody').on('click','#voidLogs',function(){
          
          $('[data-toggle="voidLog"]').tooltip('hide');
          id_logs = $(this).attr("data-logsid");
          $("#voidModal").modal();

        });
        //************************ */

        //audit_trail button
        $('.table tbody').on('click','#viewAuditTrail',function(){
          id_logs = $(this).attr("data-logsid");

          $.ajax({
                url: 'db/session_idLogs.php',
                method: 'post',
                data:{idLogs:id_logs},
                success: function(data){
                  location.replace("empLogAT.php");
                }

          });
         
        });
       
        //SHOW OPTIONS
        //for LoggedSched checkbox
        $(document).on('click','input:checkbox[data-role=emplogs_toggle1]',function(){
            

              if(this.checked){ 
                isLoggedSched = true; 
              }
              else{
                isLoggedSched = false;
              }
            
              getShowOption_result();
              checkbox_label_style();
               $.ajax({
                   url: 'db/session_empid.php',
                   method: 'post',
                   data:{empId:<?php echo $empID; ?> ,showOption:resultShowOption },
                   success: function(data){
                     //location.reload(true);
                   }
               });
        }); 

        //for AbsentedSched checkbox
        $(document).on('click','input:checkbox[data-role=emplogs_toggle2]',function(){
          
              if(this.checked){ 
                isAbsentedSched = true;
              }
              else{
                isAbsentedSched = false;
              }
            
              getShowOption_result();
              checkbox_label_style();

               $.ajax({
                  url: 'db/session_empid.php',
                  method: 'post',
                   data:{empId:<?php echo $empID; ?> ,showOption:resultShowOption },
                   success: function(data){
                     //location.reload(true);
                   }
               });
              
              
        });

        //for VoidSched checkbox
        $(document).on('click','input:checkbox[data-role=emplogs_toggle3]',function(){
          
            if(this.checked){ 
                isVoidSched = true;
                
            }
            else{
                isVoidSched = false;
            }
            
            getShowOption_result();
            checkbox_label_style();

             $.ajax({
                   url: 'db/session_empid.php',
                   method: 'post',
                   data:{empId:<?php echo $empID; ?> ,showOption:resultShowOption },
                   success: function(data){
                    // location.reload(true);
                   }
               });
             
            
        });
        //***END OF SHOW OPTIONS */

        //*******FILTER DATES */
        //to get StartDate value for filtering records
        $('#startDate').change(function(){
              dateStart = $("#startDate").val();
          }
        );

        // //to get EndDate value  for filtering records
        $('#endDate').change(function(){
             dateEnd = $("#endDate").val();
           }
        );
        //*****END OF FILTER DATES */
      

        //void modal
        $('#commentVoid').on('keyup',function(){

          if($(this).val().length > 3){
            $('#confirm_Void').attr('disabled',false);
          }
          else{
            $('#confirm_Void').attr('disabled',true);
          }
        });

        //void modal show event
        $('#voidModal').on('shown.bs.modal', function () {
          $('#confirm_Void').attr('disabled',true);
          $('#commentVoid').focus();
        });
      
        //void confirmation
        $('#confirm_Void').on('click',function(){
          var voidComments =  $('#commentVoid').val();
            $.ajax({
                   url: 'db/dbUpdate_tblLogs.php',
                   method: 'post',
                   data:{
                          mode:'void',
                          voidComment:voidComments,
                          id_log:id_logs
                        },
                   success: function(data){
                    
                     location.reload(true);
                   }
            });
        });

        //comments for editing
        $('#comment').change(function(){
          editComments = $(this).val();
          var checkLength = $(this).val().length;
          
           if(checkLength > 3){
             comments_flag = 1;           
           }
           else{
             comments_flag = 0;
           }
          
          btnUpdate_settings();
        });

        //editing login time
        $('#txtActualIn').change(function(){
           editLogin = $('#txtActualIn').val();

         
           var login_edited = new Date(DutyDate + ' ' +editLogin);
           
           verify_editLog(login_edited,1, dutyIn_actualFormat, dutyOut_actualFormat);
        });

        $('#txtActualIn').focusout(function(){
       
          
           if((newLogstage>1) && (editLogin == null)){
           
             $('#alertInvalidTime').modal();
             $('#btnUpdate').attr("disable", true);
             $(this).val("");
             $(this).focus();
           }
           else{
             if(logstage>1){
                 $('#lblActualIn').html(actualIn);
             }
             else{
                 $('#lblActualIn').html('No Logged IN');
             }
           }
        });

        //editing logout time
        $('#txtActualOut').change(function(){
           editLogout = null;
           editLogout = $('#txtActualOut').val();

           var logout_edited = new Date(DutyDate + ' ' +editLogout);
          
           verify_editLog(logout_edited,2, dutyIn_actualFormat, dutyOut_actualFormat);
        });

        $('#txtActualOut').focusout(function(){
          if((newLogstage>1) && (editLogout == null)){
            $('#alertInvalidTime').modal();
            $('#btnUpdate').attr("disable", true);
            $(this).val("");
            $(this).focus();
          }
          else{
            if(logstage==3){ 
              $('#lblActualOut').html(actualOut);
            }
            else{
              $('#lblActualOut').html('No Logged OUT');
            }
          }
        });

        //edit form --  editLogin button
        $('#btnEditLogin').on('click', function(){
          //clear text
          $('#txtActualIn').val("");
          // hide the label  
          $('#lblActualIn').hide();
          //show textbox
          $('#txtActualIn').show();
          $('#txtActualIn').focus();
        });



        //edit form -- remove login button
        $('#btnRemoveLogin').on('click',function(){
             $(this).attr('disabled',true);
             $(this).tooltip('hide');
             $('#txtActualIn').hide();

             $('#lblActualIn').show();
             $('#lblActualIn').html('No Logged IN');
             $('#edit_minlate').html('N/A');
             
             $('#btnEditLogin').attr('disabled',false);
             $('#btnEditLogout').attr('disabled', true);
             $('#btnRemoveLogout').attr('disabled',true);
             newLogin_DateTime = null;
             removedLogin_flag=1;
             login_status_edit = 0;
             btnUpdate_settings();
             determine_newLogstage();
        });


        //edit form --  editLogout button
        $('#btnEditLogout').on('click', function(){
 
            // hide the label
            $('#lblActualOut').hide();
            //show textbox
            
            $('#txtActualOut').show();
            $('#txtActualOut').val("");
            $('#txtActualOut').focus();
           
        });

         //edit form -- remove logout button
        $('#btnRemoveLogout').on('click',function(){
            $(this).attr('disabled', true);
            $(this).tooltip('hide');
            $('#txtActualOut').hide();

            $('#lblActualOut').show();
            $('#lblActualOut').html('No Logged IN');         
            $('#edit_minunder').html('N/A');

            $('#btnEditLogout').attr('disabled', false);
            $('#btnRemoveLogin').attr('disabled',false);
            newLogout_DateTime = null;
            removedLogout_flag = 1;
            btnUpdate_settings();
            logout_status_edit = 0;
            determine_newLogstage();
        });

        //edit form -- update button
        $("#btnUpdate").on('click',function(){
          

          var schedule = null;
          var isAnyChangesApplied_inLogin =0;
          var isAnyChangesApplied_inLogout =0;

          schedule = month[startOfDuty.getMonth()] + ' ' + startOfDuty.getDate()  + ', ' + startOfDuty.getFullYear();
            
            determine_newLogstage();

            $("#editModal").modal('toggle').delay(1000);
            
           

            //elements in #UpdateForm Modal
            $("#dateSched").html(schedule);
         

            if(logstage==1){
              $("#oldLogin").html('No Logged IN');
              $("#oldLate").html('N/A');
            }
            else{
              $("#oldLogin").html(actualIn);
              $("#oldLate").html(lates);
            }
            
          

            if(logstage<3){
              $("#oldLogout").html('No Logged OUT');
              $("#oldUndertime").html('N/A');
            }
            else{
            
              $("#oldLogout").html(actualOut);
              $("#oldUndertime").html(undertime);
            }
            
           
          
            if(newLogin_DateTime==null){
              if(removedLogin_flag==1){
                $("#newLogin").html('No Logged IN');
                $("#newLate").html("N/A");
                isAnyChangesApplied_inLogin = 1;
              }
              else{
                $("#newLogin").html("NO CHANGES");
                $("#newLate").html("NO CHANGES");
                isAnyChangesApplied_inLogin = 0;
              }
            }
            else{
                newLoginTime = GetNewLoginAndLogoutTime(newLogin_DateTime);
                if(newLoginTime==($.trim(actualIn))){
                  $("#newLogin").html("NO CHANGES");
                  $("#newLate").html(lates);
                  isAnyChangesApplied_inLogin = 0;
                }
                else{
            
                  $("#newLogin").html(newLoginTime);
                  $("#newLate").html("0");
                  isAnyChangesApplied_inLogin = 1;
                }
              
            }

            if(newLogout_DateTime==null){     
              if(removedLogout_flag==1){
         
                $("#newLogout").html('No Logged OUT');
                $("#newUndertime").html("N/A");
                isAnyChangesApplied_inLogout = 1;
              }
              else{
                $("#newLogout").html("NO CHANGES");
                $("#newUndertime").html("NO CHANGES");
                isAnyChangesApplied_inLogout = 0;
              }
            }
            else{

                newLogoutTime = GetNewLoginAndLogoutTime(newLogout_DateTime);

                if(newLogoutTime==($.trim(actualOut))){
          
                    $("#newLogout").html("NO CHANGES");
                    $("#newUndertime").html(undertime);
                    isAnyChangesApplied_inLogout = 0;
                }
                else{
         
                    $("#newLogout").html(newLogoutTime);
                    $("#newUndertime").html("0");
                    isAnyChangesApplied_inLogout = 1;
                }
              
            }
            $("#lblReasonForEdit").html(editComments);
           

            //verification if there is a need to update
            if (logstage != newLogstage){
              $("#UpdateForm").modal();
            }
            else if(($("#oldLogin").val()==$("#newLogin").val()) && ($("#oldLogout").val()==$("#newLogout").val())){
              $("#noChanges_forUpdate").modal();
            } 
            else if((isAnyChangesApplied_inLogin==0) && (isAnyChangesApplied_inLogout==0)){
              $("#noChanges_forUpdate").modal();
            }
            else{
              $("#UpdateForm").modal();
            }
        });

        //edit form -- close button
        $('#btnClose_edit').on('click', function(){
            newLoginTime = null;
            newLogoutTime = null;
            newLogin_DateTime = null;
            newLogout_DateTime = null;
        });

        //edit form -- confirm update button
        $('#confirm_update').on('click',function(){
            
            if(newLogout_DateTime==null){

              $.ajax({
                       url: 'db/dbUpdate_tblLogs.php',
                       method: 'post',
                        data:{
                                forActualIn :newLogin_DateTime,    
                                wasRemovedLogin:removedLogin_flag,
                                wasRemovedLogout:removedLogout_flag,
                                id_log:id_logs,
                                log_stage:newLogstage,
                                editComment: editComments,
                                mode:'update'
                            },
                        success: function(data){
                              if(data=='success'){
                                $("#successUpdateModal").modal('show');
                              }
                              else{
                                alert(data);
                              }
                              location.reload(true);
                        }
            });
          }
          else{
            $.ajax({
                       url: 'db/dbUpdate_tblLogs.php',
                       method: 'post',
                        data:{
                                forActualIn :newLogin_DateTime,    
                                forActualOut:newLogout_DateTime, 
                                wasRemovedLogin:removedLogin_flag,
                                wasRemovedLogout:removedLogout_flag,
                                id_log:id_logs,
                                log_stage:newLogstage,
                                editComment: editComments,
                                mode:'update'
                            },
                        success: function(data){
                              if(data=='success'){
                               alert("Successfully updated!");
                              }
                              else{
                                alert(data);
                              }
                              location.reload(true);
                        }
            });
          }
            
        });

        $('#updateConfirm_cancel').on('click', function(){
          newLoginTime = null;
          newLogoutTime = null;
          newLogin_DateTime = null;
          newLogout_DateTime = null;
        });

        //modal for update confirmation form
        $('#UpdateForm').on('shown.bs.modal', function () {
          var newLoginVal;
          var newLogoutVal;

          newLoginVal = $("#newLogin").val();
          newLogoutVal = $("#newLogout").val();

          if(newLoginVal=='NO CHANGES'){
            newLogin_DateTime = null;
          }
          if(newLogoutVal=='NO CHANGES'){
            newLogout_DateTime = null;
          }


        });

        function GetCurrentDate(){
          
          CurDate = moment(fullDate).format('YYYY-MM-DD');
          prevDate = moment(CurDate).subtract(1, 'month');
          prevDate = moment(prevDate).set('date',1).format('YYYY-MM-DD');



          /*
          //to get the equivalent number of month
          if(String(fullDate.getMonth()).length == 1){
            GetMonth = fullDate.getMonth()+1;
            
            if(GetMonth <=10){
              prevMonth = "0" + fullDate.getMonth();
            }            
          
          }
          else{
            GetMonth = fullDate.getMonth()+1;
            prevMonth = fullDate.getMonth();
          }
        
          //to get the equivalent date
          if(String(fullDate.getDate()).length == 1){
           
            GetDate = fullDate.getDate();
            GetDate = "0" + GetDate;
          }
          else{
            GetDate = fullDate.getDate();
          }
       
          CurDate = fullDate.getFullYear() + "-" +  GetMonth + "-"  + GetDate;
          prevDate = fullDate.getFullYear() + "-" +  prevMonth   + "-"  + "01";
       
          */
        }

        function edit_loadEmpImage(){
          $.ajax({
            url: 'db/dbEmpImage.php',
            method: 'post',
            data:{phpFile:4 },
            success: function(data){
                      $('#editdivImage').empty().html(data);
                    }
          });
        }

        function loadEmpImage(){
        
          $.ajax({
            url: 'db/dbEmpImage.php',
            method: 'post',
            data:{phpFile:1 },
            success: function(data){
                          $('#divImage').empty().html(data);
                      }
          });
        }

        function getUserPic(){
          $.ajax({
            url: 'db/dbEmpImage.php',
            method: 'post',
            data:{phpFile:3 },
            success: function(data){
                          $('#userPic').empty().html(data);
                      }
          });
        }

        function getShowOption_result(){
          if ((isVoidSched == false) && (isAbsentedSched == false) && (isLoggedSched == true)){
            resultShowOption = 1;
          }
          else if ((isVoidSched == false) && (isAbsentedSched == true) && (isLoggedSched == false)){
            resultShowOption = 2;
            
          }
          else if ((isVoidSched == false) && (isAbsentedSched == true) && (isLoggedSched == true)){
            resultShowOption = 3;
            
          }
          else if ((isVoidSched == true) && (isAbsentedSched == false) && (isLoggedSched == false)){
            resultShowOption = 4;
            
          }
          else if ((isVoidSched == true) && (isAbsentedSched == false) && (isLoggedSched == true)){
            resultShowOption = 5;
           
          }
          else if ((isVoidSched == true) && (isAbsentedSched == true) && (isLoggedSched == false)){
            resultShowOption = 6;
           
          }
          else if ((isVoidSched == true) && (isAbsentedSched == true) && (isLoggedSched == true)){
            resultShowOption = 7;      
          }
          else{ 
            resultShowOption = 0;
            
          }
         
        }

        function checkbox_label_style(){
          if(isLoggedSched==true) {
            $("#loggedSched").css({
                   "color" : "#f00",
                   "font-style" : "normal"
            });
            $("#chkEmpLogsOnly").prop("checked", "true");
          }
          else{
            $("#loggedSched").css({
                   "color" : "#ccc",
                   "font-style" : "italic"
            });
           
          }

          if(isAbsentedSched==true) {
            $("#absentedSched").css({
                   "color" : "#f00",
                   "font-style" : "normal"
            });
            $("#chkEmpAbsentedOnly").prop("checked", "true");
          }
          else{
            $("#absentedSched").css({
                   "color" : "#ccc",
                   "font-style" : "italic"
            });
            
          }

          if(isVoidSched==true){
            $("#voidSched").css({
                   "color" : "#f00",
                   "font-style" : "normal"
            });
            $("#chkEmpVoidedOnly").prop("checked", "true");
          }
          else{
            $("#voidSched").css({
                   "color" : "#ccc",
                   "font-style" : "italic"
            });
           
          }
        }


        function remove_login_rules(){
          if(((logstage == 1) || (logstage==3)) && (newLogstage==2)){
            $("#btnRemoveLogin").attr("disabled", false);
          }
          else{
            $("#btnRemoveLogin").attr("disabled", true);
          }
        }

        function edit_logout_rules(){
          //for buttons
          if((logstage==1) && ((newLogstage == 1)||(newLogstage == 0))){
            $("#btnEditLogout").attr("disabled", true);
          }
          else{
            $("#btnEditLogout").attr("disabled", false);
          }
         
        }

        function remove_logout_rules(){
          
          if(logstage<3 ){
            $('#btnRemoveLogout').attr("disabled", true);
          }
          else{
            $('#btnRemoveLogout').attr("disabled", false);
          }
        }

        function verify_editLog(time_param, log_flag, login, logout){
          
          var result1, result2;
          var result3, result4;
          var getLoginMinute;
          var maxAllowedTimeIn, late;
          var login_maxAllowed, login_basisSched;
          var logout_maxAllowed, logout_basisSched, forError_maxTime;//, login_edited;
          
          

          if(log_flag == 1){
            //startTimeIn is the actual recorded timeIn
            //time_param is the actual edited time
            
            //need to compare if time_param is valid 
             login_basisSched = moment(dutyIn_actualFormat); //also the minimum value of login basis
             login_maxAllowed = moment(login_basisSched).add(15,'m');
             
             //get the basis
             result1 = moment(time_param).isSameOrAfter(login_basisSched, 'm');
             result2 = moment(time_param).isSameOrBefore(login_maxAllowed, 'm');
              
             
            //chek if time_param is allowable value
            if((result1 == true) && (result2 == true)){ 
                login_status_edit = 1;  
                editedLogin_flag = 1;

                
        

               
                  //get the maximum allowable login time
                  maxAllowedTimeIn = moment(login_basisSched).add(10,'m');

                  //verify if it is late
                  if(moment(time_param).isAfter(maxAllowedTimeIn)){
                    //meaning it is late, and compute the late
                   
                    //get the minute time from expected login and add 10minutes
                    getLoginMinute = parseInt(moment(login).format('m')) + parseInt(10);
                  
                    //compute the late
                    late = moment(time_param).subtract(getLoginMinute,'m').format('m');
                    
                    //display the late
                    $('#edit_minlate').html(late); 
                   
                  }
                  else{
                    //no late
                    $('#edit_minlate').html(0); 
                  }

                newLogin_DateTime = DutyDate + " " + editLogin +":00";
                
                if(logstage == 1){
                  $('#btnEditLogout').attr('disabled',false);
                }
                determine_newLogstage();
                edit_logout_rules()
                remove_login_rules();
                remove_logout_rules();
                btnUpdate_settings();
            }
            else{

                forError_maxTime = moment(login_basisSched).add(15,'m').format("hh:mm A");
                $('#message').empty().html('<p>The valid time range for login is from ' + startTimeIn + ' - ' + forError_maxTime + '</p>');
                $('#alertInvalidTime').modal('show');
                editLogin = $('#txtActualIn').val("");
                editLogin = null;
                $('#txtActualIn').focus();
            }
            

          }
          else{


             //need to compare if time_param is valid 
             logout_basisSched = moment(dutyOut_actualFormat); //also the minimum value of logout basis
             logout_maxAllowed = moment(logout_basisSched).add(15,'m');

             result3 = moment(time_param).isSameOrAfter(logout_basisSched, 'm');
             result4 = moment(time_param).isSameOrBefore(logout_maxAllowed, 'm');

             if((result3 == true) && (result4 == true)){
               
                editedLogout_flag=1;
                logout_status_edit = 1;

                $('#edit_minunder').html(0);
                newLogout_DateTime = DutyDate + " " + editLogout +":00";
                
                determine_newLogstage();
                edit_logout_rules()
                remove_login_rules();
                remove_logout_rules();
                btnUpdate_settings();
             }
             else{
                forError_maxTime = moment(login_basisSched2).add(15,'m').format("hh:mm A");  
                $('#message').empty().html('<p>The valid time range for logout is from ' + endTimeOut+ ' - ' + forError_maxTime + '</p>');
                $('#alertInvalidTime').modal('show');
                editLogout = $('#txtActualOut').val("");
                editLogout = null;
                $('#txtActualOut').focus();
             }
          }
          
        }

        function GetNewLoginAndLogoutTime(dateTime_param){
         
          var getHour_split = dateTime_param.split(' ')[1];
          var getHour = getHour_split.split(':')[0];
          var getMin = getHour_split.split(':')[1];
          var returnVal="";
          
          if(getHour>12){
            getHour = getHour - 12;
            returnVal = getHour + ":" + getMin + " PM";
          }
          else if(getHour==12){
            returnVal = getHour + ":" + getMin + " PM";
          }
          else{
            returnVal = getHour + ":" + getMin + " AM";
          }
          return returnVal;
        }

       

        function login_logout_flag_status(){
          if(logstage==1){
            login_status_edit = 0;
            logout_status_edit = 0;
          }
          else if(logstage==2){
            login_status_edit = 1;
            logout_status_edit = 0;
          }
          else{
            login_status_edit = 1;
            logout_status_edit = 1;
          }
        }

        function determine_newLogstage(){
          if((login_status_edit==0) && (logout_status_edit==0)){
            newLogstage = 1;
          }
          else if((login_status_edit==1) && (logout_status_edit==0)){
            newLogstage = 2;
          }
          else if((login_status_edit==1) && (logout_status_edit==1)){
            newLogstage = 3;
          }
          
        }

        function btnUpdate_settings(){
          if (((editedLogin_flag==1) || (removedLogin_flag==1) || (editedLogout_flag==1)||(removedLogout_flag==1))&&(comments_flag==1)){
            $("#btnUpdate").attr("disabled", false);
          }
          else{
            $("#btnUpdate").attr("disabled", true);
          }
          
        }

        function showDefaultFilteredDate(){
            <?php
                  if($isDateFilter==1){ ?>
                      $('#startDate').val(prevDate);
                      $('#endDate').val(CurDate);

            <?php } 
                  else{ ?>
                      $('#startDate').val(<?php echo $filter_dateStarted; ?>);
                      $('#endDate').val(<?php echo  $filter_dateEnd; ?>);
                      
            <?php } ?>

                dateStart = $('#startDate').val();
                dateEnd = $('#endDate').val();
          
        }
    });

  </script>
</body>

</html>




