<?php
  session_start();
  require "db/dbconnect.php";

  
  //$dtruser = $_SESSION['login_userId'];
  //$empID = $_SESSION['empId'];
  $employeeName = $_SESSION['workSched_empName'];
  $idWorkSched =  $_SESSION['idWorkSched'];
  $workName = $_SESSION['workName'];
  $WSauditTrail_array = array();


  $query_WS_auditTrail = "SELECT 
	                              W.room_code, CONCAT(DATE_FORMAT(WA.duration_from,'%b. %d,%Y' ), '-', DATE_FORMAT(WA.duration_to,'%b. %d,%Y')) As Duration,
                                WA.is_sunday, WA.is_monday, WA.is_tuesday, WA.is_wednesday, WA.is_thursday, WA.is_friday, WA.is_saturday,
                                CONCAT(TIME_FORMAT(W.log_start,'%h:%i %p'),'-', TIME_FORMAT(W.log_end,'%h:%i %p')) AS Sched, WA.is_voided, 
                                CONCAT(DATE_FORMAT(WA.date_time,'%b. %d,%Y'), ' ', TIME_FORMAT(WA.date_time,'%h:%i %p')) AS DateAndTime_ofEdit,
	                              (SELECT concat(U.lname, ', ', U.fname) FROM tbl_employee AS U WHERE U.id_employee = WA.id_user) AS EditedBy
	                          FROM tbl_work_schedule AS W
		                              INNER JOIN tbl_work_schedule_at AS WA on W.id_work_schedule = WA.id_work_schedule
                            WHERE WA.id_work_schedule = $idWorkSched;";
  
 
  try{
    
    $executeQuery_WSauditTrail = mysqli_query($con, $query_WS_auditTrail);
    if(!$executeQuery_WSauditTrail){
      //error
    }
    else{
      $loop_counter  = 0;
     
      
      while($result=mysqli_fetch_assoc($executeQuery_WSauditTrail)){
          $WSauditTrail_array[$loop_counter] = array("roomCode"=>$result['room_code'],
                                                    "duration"=>$result['Duration'],
                                                    "isSun"=>$result['is_sunday'],
                                                    "isMon"=>$result['is_monday'],
                                                    "isTue"=>$result['is_tuesday'],
                                                    "isWed"=>$result['is_wednesday'],
                                                    "isThu"=>$result['is_thursday'],
                                                    "isFri"=>$result['is_friday'],
                                                    "isSat"=>$result['is_saturday'],
                                                    "schedTime"=>$result['Sched'],
                                                    "isVoid"=>$result['is_voided'],
                                                    "timeOfEdit"=>$result['DateAndTime_ofEdit'],
                                                    "editBy"=>$result['EditedBy']);
          $loop_counter++;
      }
    }  

  }
  catch(Exception $e){
    echo 'Caught exception: ', $e->getMessage(), "\n";
  }
  finally{
    mysqli_close($con);
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
    else if(!isset($_SESSION['idWorkSched'])){
          header("Location:workSched.php");
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
        <a class="nav-link" href="workSchedAT.php">
            <i class='fas'>&#xf1da;</i>
            <span>Work Schedule Audit Trail</span>
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
                  <h6 id="title" class="m-0 font-weight-bold text-danger" align="center">AUDIT TRAIL VIEW</h6>
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
                        <div class="col-3">
                          <label class="label">Work Name:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left">
                          <label id="empName"><?php echo $workName; ?> </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
             
              
              <div class="card-body">  <!--card-body TABLE -->
                <div class="table-responsive"> <!-- table-responsive -->         
                  <table class="table table-bordered table-bordered table-hover table-sm" id="dataTable_workScheduleAT" width="100%" cellspacing="0"> <!-- table-bordered -->
                    <thead>
                      <tr class="text-center">
                        <th><strong>ROOM CODE</strong></th>    <!-- column0 -->
                        <th><strong>DURATION</strong></th> <!-- column1 -->
                        <th><strong>DAYS</strong></th> <!-- column2 -->
                        <th><strong>TIME</strong></th>  <!-- column3 -->
                        <th><strong>VOIDED</strong></th> <!-- column4 -->
                        <th><strong>DATE & TIME of EDIT</strong></th> <!-- column5 -->
                        <th><strong>EDITED BY</strong></th> <!-- column6 -->
                      </tr>
                    </thead>
                    <tbody class="text-nowrap">

                        <?php 
                            $flag_Day = 0;
                              for($i=0; $i<count($WSauditTrail_array); $i++) { 
                                echo "<tr>";  
                                  echo "<td>";
                                        echo $WSauditTrail_array[$i]['roomCode'];
                                  echo "</td>";
                                  echo "<td>";
                                        echo $WSauditTrail_array[$i]['duration'];
                                  echo "</td>";
                                  echo "<td>";
                                        for ($x = 1; $x <= 7; $x++) {     
                                          switch ($x) {
                                            case "1":
                                                if($WSauditTrail_array[$i]['isSun']==1){
                                                  echo 'Sun';
                                                  $flag_Day =1;
                                                }
                                                break;
                                            case "2":
                                                if($WSauditTrail_array[$i]['isMon']==1){
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
                                              if($WSauditTrail_array[$i]['isTue']==1){
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
                                              if($WSauditTrail_array[$i]['isWed']==1){
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
                                                if($WSauditTrail_array[$i]['isThu']==1){
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
                                                if($WSauditTrail_array[$i]['isFri']==1){
                                                        if($flag_Day==1){
                                                          echo ' F';
                                                        }
                                                        else{
                                                          echo 'F';
                                                          $flag_Day =1;
                                                        }
                                                }
                                                break;
                                              case "7":
                                                if($WSauditTrail_array[$i]['isSat']==1){
                                                  if($flag_Day==1){
                                                    echo ' Sat';
                                                  }
                                                  else{
                                                    echo 'Sat';
                                                    $flag_Day =1;
                                                  }
                                          }
                                              break;
                                          }      
                                        }
                                  echo "</td>";
                                  echo "<td>";
                                        echo $WSauditTrail_array[$i]['schedTime'];
                                  echo "</td>";
                                  echo "<td class='text-center'>";
                                        if($WSauditTrail_array[$i]['isVoid']==0){
                                          echo '<img src="img/no.jpg" height="20" width="20"/>';
                                        }
                                        else{
                                            echo '<img src="img/yes.jpg"  height="30" width="30" />';
                                        }    
                                  echo "</td>";
                                  echo "<td>";
                                        echo $WSauditTrail_array[$i]['timeOfEdit'];
                                  echo "</td>";
                                  echo "<td>";
                                        echo $WSauditTrail_array[$i]['editBy'];
                                  echo "</td>";
                                echo "</tr>";
                            }
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
   
    
     
  

      
     

      employeeName = $('#empName').text();

      $('#dataTable_workScheduleAT').DataTable({
        "oLanguage": {
                       "sSearch": "Search:",
                       "emptyTable": "No data available in table"
                    }
      }); //initialize id=dataTable as DataTable
      
      getUserPic();
      loadEmpImage(); 
      GetCurrentDate();
     

        



      

     

     
   

     

     

     
      

      

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

      

     

      

     

     

      
    });
  </script>

  
</body>

</html>

