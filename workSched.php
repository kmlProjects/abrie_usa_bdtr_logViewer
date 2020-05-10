<?php
  session_start();
  require "db/dbconnect.php";

  
  $dtruser = $_SESSION['login_userId'];
  $empID = $_SESSION['empId'];
  $employeeName = $_SESSION['workSched_empName'];


  $isHideExpiredSched_view = $_SESSION['WS_hideExpiredSched'];
  
  $isDateFiltered = $_SESSION['WS_DateFiltered'];
  //echo $isHideExpiredSched_view;
  //echo "<br/>";
  $roomCode_array = array();
  $programName_array = array();


  //worksched.php page scenarios

      //with filter dates activated
      if($isDateFiltered == 1){
            $filter_dateStarted = "'" . $_SESSION['WS_def_startDate'] ."'";
            $filter_dateEnd = "'" . $_SESSION['WS_def_endDate'] ."'";

            $query = "SELECT 
                                WS.id_work_schedule,
                                E.id_employee, concat(E.fname, ' ', left(E.midname,1), '. ', E.lname) As EmpName, 
                                WS.work_name, R.room_code,
                                DATE(WS.duration_from) As durationFrom, DATE(WS.duration_to) As durationTo,
                                WS.is_sunday, WS.is_monday, WS.is_tuesday, WS.is_wednesday, WS.is_thursday, WS.is_friday, WS.is_saturday,
                                WS.log_start, WS.log_end, WS.is_voided
                          FROM tbl_work_schedule AS WS
                            INNER JOIN tbl_employee AS E on E.id_employee = WS.	id_employee
                            INNER JOIN tbl_terminal AS T on T.id_terminal = WS.id_terminal
                            INNER JOIN tbl_rooms	AS R on R.room_code = WS.room_code
                          WHERE E.id_employee = $empID AND WS.duration_from >= $filter_dateStarted AND  WS.duration_to <= $filter_dateEnd
                          ORDER BY WS.work_name ASC";
      }
      else{
        
          //Default settings (No filter & hide expired dates)
          if($isHideExpiredSched_view=='yes'){
           // $filter_dateStarted = "'" . $_SESSION['WS_def_startDate'] ."'";
            $query = "SELECT 
                                WS.id_work_schedule,
                                E.id_employee, concat(E.fname, ' ', left(E.midname,1), '. ', E.lname) As EmpName, 
                                WS.work_name, R.room_code,
                                DATE(WS.duration_from) As durationFrom, DATE(WS.duration_to) As durationTo,
                                WS.is_sunday, WS.is_monday, WS.is_tuesday, WS.is_wednesday, WS.is_thursday, WS.is_friday, WS.is_saturday,
                                WS.log_start, WS.log_end, WS.is_voided
                          FROM tbl_work_schedule AS WS
                              INNER JOIN tbl_employee AS E on E.id_employee = WS.	id_employee
                              INNER JOIN tbl_terminal AS T on T.id_terminal = WS.id_terminal
                              INNER JOIN tbl_rooms	AS R on R.room_code = WS.room_code
                          WHERE E.id_employee = $empID AND  WS.duration_to > NOW()
                          ORDER BY WS.work_name ASC";
          }
          else{ // No filter but show expired schedules
            $query = "SELECT 
                                WS.id_work_schedule,
                                E.id_employee, concat(E.fname, ' ', left(E.midname,1), '. ', E.lname) As EmpName, 
                                WS.work_name, R.room_code,
                                DATE(WS.duration_from) As durationFrom, DATE(WS.duration_to) As durationTo,
                                WS.is_sunday, WS.is_monday, WS.is_tuesday, WS.is_wednesday, WS.is_thursday, WS.is_friday, WS.is_saturday,
                                WS.log_start, WS.log_end, WS.is_voided
                          FROM tbl_work_schedule AS WS
                            INNER JOIN tbl_employee AS E on E.id_employee = WS.	id_employee
                            INNER JOIN tbl_terminal AS T on T.id_terminal = WS.id_terminal
                            INNER JOIN tbl_rooms	AS R on R.room_code = WS.room_code
                          WHERE E.id_employee = $empID AND  WS.duration_to <= NOW()
                          ORDER BY WS.work_name ASC";
          }          
      }
  
 
             
             // echo "<br />";        
             // echo $query;

  //for list of room codes
  $query_allRoomCode = "SELECT id_room, room_code, id_terminal FROM tbl_rooms";

  //for list of program names
  $query_allProgramList = "SELECT P.id_program, P.program_name 
                              FROM  tbl_program AS P
                                  INNER JOIN tbl_userprogram AS UP on UP.id_program = P.id_program
                              WHERE UP.id_dtrviewer_user = $dtruser";
  try{

    $executeQuery_RoomCode = mysqli_query($con, $query_allRoomCode);
    if(!$executeQuery_RoomCode){
      //error
    }
    else{
      $loop_counter = 0;
      while($result=mysqli_fetch_assoc($executeQuery_RoomCode)){
        $roomCode_array[$loop_counter] = array( "idRoom"=>$result['id_room'],
                                                "roomCode"=>$result['room_code'],
                                                "idTerminal"=>$result['id_terminal']
  
        );
        $loop_counter++;
      }
    }

    //for program names
    $executeQuery_ProgramName = mysqli_query($con,$query_allProgramList);
    if(!$executeQuery_ProgramName ){
      //error
    }
    else{
      $loop_counter = 0;
      while($result = mysqli_fetch_assoc($executeQuery_ProgramName)){
        $programName_array[$loop_counter] = array( "idProg"=> $result['id_program'],
                                                    "progName"=> $result['program_name']

        );
        $loop_counter++;
      }
    }
  }
  catch(Exception $e){
    echo 'Caught exception: ', $e->getMessage(), "\n";
  }
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>DTR LOG VIEWER</title>
  <?php
	
		//Check for Session ID
		if (!isset ($_SESSION['login_userId'])){
			  header("Location:login.php");
    }
    else if(!isset($_SESSION['login_userId'])){
          header("Location:index.php");
    }
            
        
    
  ?> 
  <!-- icon on the title -->
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

  <!-- custom css created for modal by kmLaureta -->
  <link href="css/modal.css" rel="stylesheet">

  <!-- Custom styles for dataTable  -->
  <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

<style>
    
    input[type=checkbox] + label {
            color: #ccc;
            font-style: italic;
    } 
    input[type=checkbox]:checked + label {
            color: black;
            font-style: normal; 
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
    #btnFilter{
      float: left;
      margin-left:185px;
      margin-top:-3px;
    }

    legend
		{
			font-size:14px;
			font-weight:bold;
			margin-bottom: 0px; 
			width: 35%; 
			border: 1px solid #ddd;
			border-radius: 4px; 	 
			background-color: white;
      padding:0 10px; /* To give a bit of padding on the left and right */
      border-bottom:none;
		} 

    #mdl_legend_duration{
      /*background-color: lightgray;*/
      border: none;
      font-size:18px;
      width: 25%; 
      padding:0 10px;
      
    }

    #mdl_ws_body.label{
        font-size: 8px;
    }
    #mdl_legend_Time{
      /*background-color: lightgray;*/
      border: none;
      width: 34%; 
      font-size:18px;
    }

    #mdl_legend_Day{
      /*background-color: lightgray;*/
      border: none;
      width: 20%; 
      font-size:18px;
    }

    #mdl_ws_details{
      position:relative;
      margin-top:-20px;
    }

    .mdl_chkboxes{
      font-size:13px;
    }
    /*
    #mdlAddEdit_WS{
      position:relative;
      margin-left:10px;
      margin-top:10px;
    }*/
    




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
        <a class="nav-link" id="sideBar-EmpList" href="index.php">
            <i class="fas fa-user"></i>
            <span>Employee List</span>
        </a>
        <a class="nav-link" id="sideBar-EmpList" href="workSched.php">
            <i class='fas'>&#xf073;</i>
            <span>Work Schedule</span>
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

          <label style="margin-top:25px">LOGIN USER:</label>
            <div class="topbar-divider d-none d-sm-block"></div>

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
              <!-- Dropdown - User Informatixon -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <!-- <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a> -->
                <!-- <a class="dropdown-item" href="#">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a> -->
                <!-- <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Activity Log
                </a> -->
                <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        
        <div class="container-fluid">
          <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4" >
                <h1 class="h3 mb-2 font-weight-bold text-danger">
                    <i class='fas'>&#xf073;</i>
                    &nbsp;
                    Work Schedule List
                </h1>
                
            </div>
            <div class="card shadow mb-4">
              <div class="card-header text-right">
                  <button style="font-size:20px"  id="btnAddWorkSched">Add Work Schedule <i class="material-icons">add_box</i></button>
              </div>
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
                          <label id="empName"><?php echo $employeeName; ?> </label>
                        </div>  
                      </div>
                      <div class ="row" > 
                          <fieldset class="border p-2 col-10" >
                              <legend class="w-auto"><h6>Filter Date:</h6></legend>
                              <div class="row">
                                <div class="col-4">
                                    <div class="row">
                                        &nbsp;&nbsp;
                                        <div class="col-3">
                                            <label class="label">FROM:</label>
                                        </div>
                                        <div class="col-3 text-right">
                                            <input class='form-control' id='wsStartDate' name='startDate'  placeholder='Start Date' type='date' style='width:180px'/>  
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="row">
                                        <div class="col-3"></div>
                                        <div class="col-2">
                                            <label class="label">TO:</label>
                                        </div>
                                        <div class="col-2">
                                            <input class='form-control' id='wsEndDate' name='endDate' placeholder='End Date' type='date' style='width:180px'/>
                                        </div>
                                        <div class="col-3">
                                          <button class="btn btn-danger btn-md" id="btnFilter" data-role="filter" >
                                                <span class="text">FILTER</span>
                                          </button> 
                                        </div>
                                    </div>
                                </div>
                              </div>
                          </fieldset>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
             
              
              <div class="card-body">  <!--card-body TABLE -->
                <div class="table-responsive"> <!-- table-responsive -->         
                  <table class="table table-bordered table-bordered table-hover table-sm" id="dataTable_workSchedule" width="100%" cellspacing="0"> <!-- table-bordered -->
                    <thead>
                      <tr class="text-center">
                        <th><strong>WORK NAME</strong></th>    <!-- column0 -->
                        <th><strong>ROOM CODE</strong></th> <!-- column1 -->
                        <th style='display:none'>durationFrom</th> <!-- column2 -->
                        <th style='display:none'>durationTo</th> <!-- column3 -->
                        <th><strong>DURATION</strong></th> <!-- column4 -->
                        <th><strong>DAYS</strong></th> <!-- column5 -->
                        <th style='display:none'>logStart</th> <!-- column6 -->
                        <th style='display:none'>logEnd</th> <!-- column7 -->
                        <th><strong>TIME</strong></th> <!-- column8 -->
                        <th><strong>ACTION</strong></th> <!-- column9 -->
                      </tr>
                    </thead>
                    <tfoot >
                        <tr class='text-danger'>
                            <th colspan='10'>
                                <div class='custom-control custom-checkbox small text-right text-danger' style="position:relative;margin-top:10px">
                                        <input type='checkbox'  data-role='ws_hideExpired' name='chkBx_empAll' id='chkHideExpired'
                                          <?php 
                                              if($isHideExpiredSched_view=='yes'){
                                                  echo 'checked';
                                              }   
                                          ?>
                                          >
                                        <label class='custom-label'><strong>Hide Expired Work Schedules</strong></label>
                                </div>
                            </th>                   
                        </tr> 
                    </tfoot>
                    <tbody class="text-nowrap">
                      <?php
                        if(isset($query)){  
                          $execute_query = mysqli_query($con, $query);

                          if(!$execute_query){
                            //error in executing
                          }
                          else{
                            $rowCount = mysqli_num_rows($execute_query);
                            
                            if($rowCount == 0) {
                            }
                            else{
                              while($result_workSched = mysqli_fetch_assoc($execute_query)){ 
                      ?>

                                  <tr>
                                        <td >
                                            <?php echo $result_workSched['work_name']; ?>   <!-- column0 WORKNAME-->
                                        </td>
                                        <td>
                                            <?php echo $result_workSched['room_code'];?> <!-- column1 ROOM CODE-->
                                        </td>
                                        <td style='display:none'> <!-- column2 DurationFrom-->
                                            <?php  echo $result_workSched['durationFrom'];?>
                                        </td>
                                        <td style='display:none'> <!-- column3 DurationTo-->
                                            <?php echo $result_workSched['durationTo'];?>
                                      </td>
                                      <td>
                                            <?php 
                                                $durationFrom = new DateTime($result_workSched['durationFrom']);
                                                $durationTo = new DateTime($result_workSched['durationTo']);
                                                echo $durationFrom->format('M.d,Y').' - '. $durationTo->format('M.d,Y');
                                            ?> <!-- column4 DURATION-->
                                      </td>
                                        <td> <!-- column5 DAYS-->
                                            <?php 
                                                $flag_Day=0; //indicator if there were days have been listed

                                                for ($x = 1; $x <= 7; $x++) {
                                                  
                                                  switch ($x) {
                                                    case "1":
                                                        if($result_workSched['is_sunday']==1){
                                                          echo 'Sun';
                                                          $flag_Day =1;
                                                        }
                                                        break;
                                                    case "2":
                                                        if($result_workSched['is_monday']==1){
                                                          if($flag_Day==1){
                                                            echo ' M';
                                                          }
                                                          else{
                                                            echo 'M';
                                                            $flag_Day =1;
                                                          }
                                                        }
                                                        break;
                                                    case "3":
                                                      if($result_workSched['is_tuesday']==1){
                                                        if($flag_Day==1){
                                                          echo ' Tu';
                                                        }
                                                        else{
                                                          echo 'Tu';
                                                          $flag_Day =1;
                                                        }
                                                      }
                                                      break;
                                                    case "4":
                                                      if($result_workSched['is_wednesday']==1){
                                                          if($flag_Day==1){
                                                            echo ' W';
                                                          }
                                                          else{
                                                            echo 'W';
                                                            $flag_Day =1;
                                                          }
                                                      }
                                                        break;
                                                      case "5":
                                                        if($result_workSched['is_thursday']==1){
                                                              if($flag_Day==1){
                                                                echo ' Th';
                                                              }
                                                              else{
                                                                echo 'Th';
                                                                $flag_Day =1;
                                                              }
                                                        }
                                                        break;
                                                      case "6":
                                                        if($result_workSched['is_friday']==1){
                                                                if($flag_Day==1){
                                                                  echo ' F';
                                                                }
                                                                else{
                                                                  echo 'F';
                                                                  $flag_Day =1;
                                                                }
                                                        }
                                                        break;
                                                    default:
                                                        if($flag_Day==1){
                                                          echo ' Sat';
                                                        }
                                                        else{
                                                          echo 'Sat';
                                                          $flag_Day =1;
                                                        }
                                                  }      
                                                }

                                            ?>
                                        </td>
                                        <td style='display:none'>
                                            <?php echo $result_workSched['log_start'];?>  <!-- column6 logStart-->
                                        </td>
                                        <td style='display:none'>
                                            <?php echo $result_workSched['log_end'];?>    <!-- column7 logEnd-->
                                        </td>
                                        <!-- column8-->
                                        <td> 
                                              <?php 
                                                    $logStart = new DateTime($result_workSched['log_start']);
                                                    $logEnd = new DateTime($result_workSched['log_end']);
                                                    echo $logStart->format('g:i A').'-'.$logEnd->format('g:i A'); ?>
                                        </td>
                                        
                                        
                                        <td>
                                          <button data-toggle="editWS" title="EDIT" data-role="editWS" class="btn btn-success btn-sm btn-circle"  
                                                  data-WSid="<?php echo $result_workSched['id_work_schedule']; ?>" data-isvoided="<?php echo $result_workSched['is_voided']; ?>" 
                                                  style="width:18%" name='btnEditWS' id="btnEditWS">
                                              <span class='icon text-white-50'>
                                              <i class='far'>&#xf044;</i> 
                                              </span>
                                            <!-- <span class='text'>EDIT </span> -->
                                          </button>
                                          &nbsp; 
                                          <button data-toggle="deleteWS" title="DELETE" data-role="deleteWS" class="btn btn-primary btn-sm btn-circle"  
                                                  data-WSid="<?php echo $result_workSched['id_work_schedule']; ?>" data-isvoided="<?php echo $result_workSched['is_voided']; ?>" 
                                                  style="width:18%" name='btnDeleteWS' id="btnDeleteWS">
                                            <span class='icon text-white-50'>
                                            <i class='fas'>&#xf4fd; </i>
                                            </span>
                                          <!-- <span class='text'>SHOW</span> -->
                                          </button>
                                          &nbsp;
                                          <button data-toggle="voidWS" title="VOID" data-role="voidWS" class="btn btn-danger btn-sm btn-circle"  
                                                  data-WSid="<?php echo $result_workSched['id_work_schedule']; ?>" data-isvoided="<?php echo $result_workSched['is_voided']; ?>" 
                                                  style="width:18%" name='btnVoidWS' id="btnVoidWS">
                                            <span class='icon text-white-50'>
                                            <i class='fas'>&#xf410; </i>
                                            </span>
                                            <!-- <span class='text'>VOID</span> -->
                                          </button>
                                          &nbsp;
                                          <button data-toggle="audittrailWS" title="AUDIT TRAIL"data-role="viewAuditTrailWS" class="btn btn-warning btn-sm btn-circle"  
                                                  data-WSid="<?php echo $result_workSched['id_work_schedule']; ?>"
                                                  style="width:18%" name='btnAuditTrailWS' id="btnAuditTrailWS">
                                            <span class='icon text-white-50'>
                                            <i class='fas'>&#xf1da; </i>
                                            </span>
                                            <!-- <span class='text'>AUDIT</span> -->
                                          </button>

                                        </td>
                                  </tr> 
                      <?php   }
                            }
                            
                          }
                        } 
                        mysqli_close($con);
                      ?>
                    </tbody>
                  </table>
              </div>
          </div>
        <div>
        


    


      </div><!-- /.content-wrapper -->
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

  <!-- Modal for Add/Edit Form --> <!-- modal-dialog-centered -->
  <div class="modal fade" id="mdlAddEdit_WS" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header mdl_header">
          <h5 class="modal-title mdl_title">Add Work Schedule</h5>
          <button class="close mdl_x_button" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body mdl_body">
            <div class="card" id="mdl_ws_body">
              <div class="card-body">
                  <!--============================= NAME & WORK NAME ====================================-->
                  <div class="form-row">
                    <div class="col-3 text-right">
                      <label class="label text-dark"><strong>Employee Name:</strong> </label>
                    </div>
                    <div class="col-9">
                      <label class="label" id="lblEmpName" style="color:red;font-size:25px;margin-top:-5px"></label>
                    </div>
                  </div>
                  <div class ="form-row">
                    <div class="col-3 text-right">
                        <label class="label text-dark"><strong>Work Name:</strong> </label>
                    </div>
                    <div class="col-9">
                        <input class='form-control' id='txtWorkName'  type='text'/>  
                    </div>
                  </div>
                  <!--=======================================================================================-->
                  <br />
                  
                  <!---============================ Room Code & Program ====================================== -->
                  <div class="form-row">
                      <div class="col-5">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="roomCodeList">Room Code:</label>
                            </div>
                            <select id="roomCodeList" class="custom-select">
                              <option value="0" disabled selected>Pick a room code..
                              </option>
                                <?php
                                    $roomCode_array_count = count($roomCode_array);
                                    for($x=0; $x<$roomCode_array_count; $x++){
                                              $idTerminal = $roomCode_array[$x]['idTerminal'];
                                              echo "<option value= $idTerminal />";
                                                    echo $roomCode_array[$x]['roomCode'];
                                              echo "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                      </div>
                      <div class="col-7">
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <label class="input-group-text" for="programNameList">Program:</label>
                          </div>
                          <select id="programNameList" class="custom-select">
                                <option value="0" disabled selected>Pick a program name..
                                </option> 
                                <?php
                                    $progNam_array_count = count($programName_array);
                                    for($y=0; $y<$progNam_array_count; $y++){
                                           $valueProgram = $programName_array[$y]['idProg'];
                                          echo "<option value= $valueProgram />";
                                                    echo $programName_array[$y]['progName'];
                                          echo "</option>";
                                    }
                                ?>
                          </select>
                        </div>
                      </div>
                  </div>
                   <!--=======================================================================================-->

                  <br />
                  <!--====================================== DETAILS =========================================================-->                
                  <div class="form-row" id="mdl_ws_details">
                       <!--====================================== DATE AND TIME DURATION =========================================================-->   
                      <div class="col-7">
                          <!--====================================== DATE =========================================================--> 
                          <div class="form-row">
                              <fieldset class="border p-2 col-12 card" >
                                  <legend id="mdl_legend_duration"><h6><strong>Duration</strong></h6></legend>
                                  <div class="form-row"  sytle="postion:relative;margin-top:-50px">
                                    <div class="col-6">
                                      <label class="label" for="mdl_addEdit_durationFrom">From:</label>
                                      <input class='form-control' id='mdl_addEdit_durationFrom' type='date'/> 
                                    </div>
                                    <div class="col-6" id="divDurationTo">
                                      <label class="label" for="mdl_addEdit_durationTo">To:</label>
                                      <input class='form-control' id='mdl_addEdit_durationTo' type='date'/> 
                                    </div>
                                  </div>
                                  <div class="form-row" style="postion:relative;margin-top:10px">
                                      <div class="col-12 text-right">
                                          <input type='checkbox'  data-role='mdl_addEdit_durationPerpetual' name='chkBx_durationPerpetual' id='chkdurationPerpetual'>
                                          <label class='custom-label' style="postion:relative;margin-right:10px"><strong>Perpetual</strong></label>
                                      </div>
                                  </div>
                              </fieldset>
                          </div>
                          <!--=======================================================================================-->
                          <br />
                          <!--====================================== TIME=========================================================--> 
                          <div class="form-row" style="position:relative;margin-top:-20px">
                              <fieldset class="border p-2 col-12 card">
                                  <legend id="mdl_legend_Time"><h6><strong>Login/Logout</strong></h6></legend>
                                  <div class="form-row">
                                    <div class="col-6">
                                          <label class="label" for="mdl_addEdit_startTime">Start Time:</label>
                                          <input class='form-control' id='mdl_addEdit_startTime' type='time'/> 
                                    </div>
                                    <div class="col-6">
                                          <label class="label" for="mdl_addEdit_endTime">End Time:</label>
                                          <input class='form-control' id='mdl_addEdit_endTime' type='time'/> 
                                    </div> 
                                  </div>
                                  <div class="form-row">
                                        <div class="col-12 text-right" style="position:realative;margin-right:10px;margin-top:10px">
                                                <input type='checkbox'  data-role='mdl_addEdit_isSunday' name='chkBx_nextDayLogout' id='chkNextDayLogout'>      
                                                <label class='custom-label mdl_chkboxes'><strong>Next Day Log Out</strong></label>
                                        </div>
                                  </div>
                                  
                              </fieldset>
                          </div>
                          <!--=======================================================================================-->
                      </div>
                      <!--=======================================================================================-->     

                      <!--====================================== DAYS =========================================================--> 
                      <div class="col-5" >        
                           
                                <fieldset class="border p-2 col-12 card" style="height:100%" >
                                    <legend id="mdl_legend_Day"><h6><strong>Day</strong></h6></legend>
                                    <div class="form-row">
                                        <div class="col-12">
                                          <div class="card" style="height:100%">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <button id="btnWeekDays" type="button" class="btn btn-success btn-md" style="width:100%" >Weekdays</button>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            <div class="card-body">
                                                <div class="form-row">
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isSunday' name='chkBx_isSunday' id='chkIsSunday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Sunday</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isMonday' name='chkBx_isMonday' id='chkIsMonday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Monday</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isTuesday' name='chkBx_isTueday' id='chkIsTuesday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Tuesday</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isWednesday' name='chkBx_isWednesday' id='chkIsWednesday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Wednesday</strong></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isThursday' name='chkBx_isThursday' id='chkIsThursday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Thursday</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isFriday' name='chkBx_isFriday' id='chkIsFriday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Friday</strong></label>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <input type='checkbox'  data-role='mdl_addEdit_isSaturday' name='chkBx_isSaturday' id='chkIsSaturday'>
                                                                <label class='custom-label mdl_chkboxes'><strong>Saturday</strong></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                          </div> 
                                        </div>
                                    </div>            
                                </fieldset>
                            
                          
                      </div>
                  </div>
                   <!--=======================================================================================-->
              </div>
            </div>
        </div>
        <div class="modal-footer mdl_footer">
          <button class="btn btn-primary" type="button" data-dismiss="modal" id="btnWorkSched">Sample</button>
          <button class="btn btn-warning" type="button" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal  No selected Dates-->     

  
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
      $('#sidebarToggle').trigger('click');
      
      var CurDate = null;
      var prevDate = null;
      var dateStart, dateEnd;
      var employeeName; 
      var workName,roomCode, roomCode, programId;
      var durationFrom, durationTo, chkPerpetual;
      var startTime, endTime, chkNextDayLogOut;
      var idTerminal;
      var chkSun, chkMon, chkTue, chkWed, chkThu, chkFri, chkSat, daysHadSelected;
      var phpMode;
      var update_last, update_next;

      daysHadSelected = 0;
      chkPerpetual = 0;
      chkNextDayLogOut =0;
      chkSun = 0;
      chkMon = 0;
      chkTue = 0; 
      chkWed = 0; 
      chkThu = 0; 
      chkFri = 0;
      chkSat = 0;
      

      employeeName = $('#empName').text();

      $('#dataTable_workSchedule').DataTable({
        "oLanguage": {
                       "sSearch": "Search:",
                       "emptyTable": "No data available in table"
                    }
      }); //initialize id=dataTable as DataTable
      
      getUserPic();
      loadEmpImage(); 
      GetCurrentDate();
     

        $(document).on('click','input:checkbox[data-role=ws_hideExpired]',function(){

            //check current status after it was selected
            if($(this).prop("checked")==true){ 
                $.ajax({
                    url: 'db/session_empid.php',
                    method: 'post',
                    data:{
                          viewOption: 'viewWorkSched',
                          hideExpiredSched: 'yes',
                          isDateFiltered: 0,
                          calledFrom: 'worksched.php'
                    },
                    success: function(data){
                      location.reload(true);
                    }
                });
            }
            else{
              $.ajax({
                    url: 'db/session_empid.php',
                    method: 'post',
                    data:{
                          viewOption: 'viewWorkSched',
                          hideExpiredSched: 'no',
                          isDateFiltered: 0,
                          calledFrom: 'worksched.php'
                    },
                    success: function(data){
                      location.reload(true);
                    }
                });
            }
           
       

        });

       //*******FILTER DATES */
        //to get StartDate value for filtering records
        $('#wsStartDate').change(function(){
            dateStart = $(this).val();
          }
        );

        // //to get EndDate value  for filtering records
        $('#wsEndDate').change(function(){
             dateEnd = $(this).val();
           }
        );


        //filter Date button click event
        $(document).on('click','button[data-role=filter]',function(){
          if((dateStart == null || dateEnd == null) && resultShowOption == 0)  {
            $('#alertNoselectedDates').modal('show');
          }
          else{

              //determine the endDate if it is less than current date 
              var hideExpiredSched_status; 
              if(dateEnd < CurDate){
                hideExpiredSched_status = 'no';
              }
              else{
                hideExpiredSched_status = 'yes';
              }
              
              $.ajax({
                   url: 'db/session_empid.php',
                   method: 'post',
                   data:{ empId:<?php echo $empID; ?>,
                          calledFrom: 'worksched.php',
                          viewOption:'viewWorkSched',
                          hideExpiredSched: hideExpiredSched_status,
                          isDateFiltered:1, 
                          dateStart:dateStart,
                          dateEnd:dateEnd,
                          
                        },
                   success: function(data){
                     location.reload(true);
                     //SetDateFilterValue(prevDate, CurDate);
                    
                   }
              });
              
              
              
          }
        });
       //*****END OF FILTER DATES */

      
      $('#btnAddWorkSched').click(function(){
        
       
        $('#lblEmpName').text(employeeName);
        $('#mdlAddEdit_WS').modal('show');
        $('.modal-title').text("Add Work Schedule");
        phpMode = 'add';
      });

      $('#mdlAddEdit_WS').on('shown.bs.modal', function (){
          clearWSForm()
          $('#txtWorkName').focus();
          $('#mdl_addEdit_durationFrom').val(CurDate);
          durationFrom = $('#mdl_addEdit_durationFrom').val();
          if(phpMode == 'add'){
            $('#btnWorkSched').html('Submit');
          }
          else{
            $('#btnWorkSched').html('Update');
          }
          btnWorkSched_Settings();
      });

      $('#mdlAddEdit_WS').on('hidden.bs.modal', function (){
        clearWSForm();
      });

      //modal elements events
      $('#txtWorkName').change(function(){
        workName = $(this).val();
        btnWorkSched_Settings();
      });
      
      $('#roomCodeList').change(function(){
        roomCode = $('#roomCodeList option:selected').text();
        //intended for ID TERMINAL
        idTerminal = $('#roomCodeList option:selected').val();  
        btnWorkSched_Settings();
      });

      $('#programNameList').change(function(){
        programId = $('#programNameList option:selected').val();
        btnWorkSched_Settings();
      });

      $('#mdl_addEdit_durationFrom').change(function(){
        durationFrom = $(this).val();
        btnWorkSched_Settings();
      });
      
      $('#mdl_addEdit_durationTo').change(function(){
        durationTo = $(this).val();
        if(durationTo < durationFrom ){
          alert("Selected Date must not earlier than Duration From Date!");
          durationTo = null;
          $(this).val('');
          $(this).focus();
        }
        else{
          btnWorkSched_Settings();
        }
        
      });

      $('#chkdurationPerpetual').change(function(){
        if($(this).prop("checked")==true){
          chkPerpetual = 1; 
          $('#mdl_addEdit_durationTo').val('2100-12-31');
          durationTo =  $('#mdl_addEdit_durationTo').val();
          $('#divDurationTo').hide();
        }
        else{
          chkPerpetual = 0;
          $('#mdl_addEdit_durationTo').val('');
          $('#divDurationTo').show();
          durationTo='';

        }
        
        btnWorkSched_Settings();
      });

      $('#mdl_addEdit_startTime').change(function(){
        startTime = $(this).val();
        btnWorkSched_Settings();
      });
      
      $('#mdl_addEdit_endTime').change(function(){
        endTime = $(this).val();
        

        if( chkNextDayLogOut == 0){
          if(endTime < startTime){
            alert("Invalid End Time value. It must not be earlier than Start Time");
            endTime = null;
            $(this).val('');
            $(this).focus();
          }
          else{
            btnWorkSched_Settings();
          }
        }
        else{
          btnWorkSched_Settings();
        }
        
       

        
      });

      $('#chkNextDayLogout').change(function(){
        if($(this).prop("checked")==true){
          chkNextDayLogOut = 1;
          btnWorkSched_Settings();
        }
        else{
          chkNextDayLogOut = 0;
          if(endTime < startTime){
            alert("Invalid End Time value. It must not be earlier than Start Time");
            endTime = null;
            $('#mdl_addEdit_endTime').val('');
            $('#mdl_addEdit_endTime').focus();
          }
        }
         
      });

      $('#btnWeekDays').click(function(){
        $('#chkIsMonday').prop("checked", true);
        $('#chkIsTuesday').prop("checked", true);
        $('#chkIsWednesday').prop("checked", true);
        $('#chkIsThursday').prop("checked", true);
        $('#chkIsFriday').prop("checked", true);
        chkMon = 1;
        chkTue = 1;
        chkWed = 1;
        chkThu = 1;
        chkFri = 1;
        daysHadSelected = 1;
        btnWorkSched_Settings();
      });

      $('#chkIsSunday').change(function(){
        if($(this).prop("checked")==true){
          chkSun = 1;
          daysHadSelected = 1; 
        }
        else{
          chkSun = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });

      $('#chkIsMonday').change(function(){
        if($(this).prop("checked")==true){
          chkMon = 1;
          daysHadSelected = 1;
        }
        else{
          chkMon = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });

      $('#chkIsTuesday').change(function(){
        if($(this).prop("checked")==true){
          chkTue = 1;
          daysHadSelected = 1;
        }
        else{
          chkTue = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });

      $('#chkIsWednesday').change(function(){
        if($(this).prop("checked")==true){
          chkWed = 1;
          daysHadSelected = 1;
        }
        else{
          chkWed = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });

      $('#chkIsThursday').change(function(){
        if($(this).prop("checked")==true){
          chkThu = 1;
          daysHadSelected = 1;
        }
        else{
          chkThu = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });

      $('#chkIsFriday').change(function(){
        if($(this).prop("checked")==true){
          chkFri = 1;
          daysHadSelected = 1;
        }
        else{
          chkFri = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });

      $('#chkIsSaturday').change(function(){
        if($(this).prop("checked")==true){
          chkSat = 1;
          daysHadSelected = 1;
        }
        else{
          chkSat = 0;
          HasDaysSelected();
        }
        btnWorkSched_Settings();
      });



 
      $('#btnWorkSched').click(function(){
     
       if(durationFrom<CurDate){
        update_last = CurDate;
       }
       else{
        update_last = durationFrom;
       }
       update_next = moment(update_last).subtract(3, 'days').format('YYYY-MM-DD');


       $.ajax({
            url:'db/dbWorkSched.php',
            method: 'post',
            data:{
                    phpMode: phpMode,
                    php_empId: <?php echo $empID; ?>,
                    php_workName: workName,
                    php_durationFrom: durationFrom,
                    php_durationTo: durationTo,
                    php_chkPerpetual: chkPerpetual,
                    php_logStart: startTime,
                    php_logEnd: endTime, 
                    php_chkNextDayLogOut: chkNextDayLogOut,
                    php_isSun: chkSun,
                    php_isMon: chkMon,
                    php_isTue: chkTue,
                    php_isWed: chkWed,
                    php_isThu: chkThu,
                    php_isFri: chkFri,
                    php_isSat: chkSat,
                    php_idTerminal: idTerminal,
                    php_updateLast:update_last,
                    php_updateNext:update_next,
                    php_isOutNextDay:chkNextDayLogOut,
                    php_isPerpetual: chkPerpetual,
                    php_roomCode: roomCode,
                    php_idProgram:programId
            },
            success: function(data){
             // alert(data);  
              if(phpMode=='add'){
                if(data =='0'){
                   alert('The work schedule that you are about to save for ' +  employeeName +  ' is in conflict with his/her existing schedule named ' + workName +  ' effective between ' + durationFrom + ' to ' + durationTo + ' with scheduled logs from ' + startTime + ' to ' + endTime);
                }
                else if (data =='00'){
                  alert('ERROR in saving work schedule details');
                }
                else if (data=='000'){
                  alert('Error in checking conflict schedules');
                }
                else if (data=='0000'){
                  alert('Error in saving in its audit trail');
                }
                else if (data=='1'){
                  alert("Successfully saved the work schedule details!");
                  location.reload();
                }
              }
              
              
              
            }
        });
        
        
      });

      function getUserPic(){
        $.ajax({
            url: 'db/dbEmpImage.php',
            method: 'post',
            data:{phpFile:3 },
            success: function(data){
                 $('#userPic').html(data);
            }
          });
      }

      function GetCurrentDate(){

        var fullDate = new Date();
       
        CurDate = moment(fullDate).format('YYYY-MM-DD');
        prevDate = moment(CurDate).subtract(3, 'month');
        prevDate = moment(prevDate).set('date',1).format('YYYY-MM-DD');
        
        SetDateFilterValue(prevDate, CurDate);
       
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

      function SetDateFilterValue(fromDate, toDate){

        <?php 
                if(($isDateFiltered == 0) && ($isHideExpiredSched_view=='yes')){
        ?>
                      $('#wsStartDate').val(fromDate);
                      $('#wsEndDate').val(toDate);
        <?php   }
                else if(($isDateFiltered == 0) && ($isHideExpiredSched_view=='no')){ 
        ?>
                      $('#wsStartDate').val('');
                      $('#wsEndDate').val('');
        <?php   }
               
                else{
        ?>
                      $('#wsStartDate').val(<?php echo $filter_dateStarted; ?>);
                      $('#wsEndDate').val(<?php echo $filter_dateEnd; ?>);
        <?php          
                }
        ?>
                dateStart = $('#wsStartDate').val();
                dateEnd = $('#wsEndDate').val();

      }

      function clearWSForm(){
          $('#txtWorkName').val('');
          $('#roomCodeList').val(0);
          $('#programNameList').val(0);
          $('#mdl_addEdit_durationFrom').val(CurDate);
          $('#mdl_addEdit_durationTo').val('');
          $('#chkdurationPerpetual').prop("checked", false);
          $('#mdl_addEdit_startTime').val('');
          $('#mdl_addEdit_endTime').val('');
          $('#chkNextDayLogout').prop("checked", false);
          $('#chkIsSunday').prop("checked", false);
          $('#chkIsMonday').prop("checked", false);
          $('#chkIsTuesday').prop("checked", false);
          $('#chkIsWednesday').prop("checked", false);
          $('#chkIsThursday').prop("checked", false);
          $('#chkIsFriday').prop("checked", false);
          $('#chkIsSaturday').prop("checked", false);



      }

      function btnWorkSched_Settings(){
        
        if((workName == null || workName == '') || (roomCode == null || roomCode == '') || (programId == null || programId == 0) || 
           (durationFrom == null) || (durationTo == null) || 
           (startTime == null) || (endTime == null) || (daysHadSelected == 0)){
              
            $('#btnWorkSched').attr("disabled", true);
          
        }
        else{
            $('#btnWorkSched').attr("disabled", false);
        
        }
       
     
        
      }

      function HasDaysSelected(){
        if((chkSun == 1) || (chkMon==1) || (chkTue==1) || (chkWed==1) || (chkThu==1) || (chkFri==1) || (chkSat==1)){
          daysHadSelected = 1;
        }
        else{
          daysHadSelected = 0;
        }
      }

     

      
    });
  </script>

  
</body>

</html>

