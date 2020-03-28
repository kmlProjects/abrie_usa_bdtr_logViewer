<?php 
    session_start();
    require "db/dbconnect.php";     
    $empID=  $_SESSION['empId'];
    $idLogs =   $_SESSION['idLogs'];

    //variables for result of $auditTrail_query1
    $empName;
    $workName;
    $deptName;
    $location;
    $dutyDate;
    $dutyIn;
    $dutyOut;
    
    if(!$con){
      die("Connection Failed:".mysqli_connect_error());
    }

    try{
        $auditTrail_query1 = "SELECT 
                                        concat(E.lname, ', ', E.fname, ' ', left(E.midname,1), '.') AS EName,
                                        L.work_name, 
                                        P.program_name, 
                                        T.location,  
                                        DATE(L.duty_in) AS Duty_Date, 
                                        TIME(L.duty_in) As Duty_InTime, 
                                        TIME(L.duty_out) AS Duty_OutTime                                    
                                FROM tbl_logs AS L
                                    INNER JOIN tbl_employee AS E on E.id_employee = L.id_employee
                                    INNER JOIN tbl_program AS P on L.id_program_emp = P.id_program
                                    INNER JOIN tbl_terminal AS T on T.id_terminal = L.id_terminal
                                WHERE L.id_logs = $idLogs";

        $execute_query1 = mysqli_query($con, $auditTrail_query1);

        while($result_empDetails= mysqli_fetch_assoc($execute_query1)){
            $empName = $result_empDetails['EName'];
            $workName = $result_empDetails['work_name'];
            $deptName = $result_empDetails['program_name'];
            $location = $result_empDetails['location'];
            $dutyDate = $result_empDetails['Duty_Date'];
            $dutyIn = $result_empDetails['Duty_InTime'];
            $dutyOut = $result_empDetails['Duty_OutTime'];
        }


        $auditTrail_query2 = "SELECT 
                                    TIME(LAT.actual_in) AS Actual_timeIn_timeOnly,
                                    TIME(LAT.actual_out) AS Actual_timeOut_timeOnly,
                                    LAT.min_late AS LATES, 
                                    LAT.min_under AS Undertime, 
                                    LAT.is_voided,
                                    LAT.remarks, 
                                    LAT.date_time AS DateTime_Edit,
                                   (SELECT concat(U.lname, ', ', U.fname) FROM tbl_employee AS U WHERE U.id_employee = LAT.id_user) AS EditedBy
                                FROM tbl_logs AS L
                                   INNER JOIN tbl_logs_at AS LAT on L.id_logs = LAT.id_logs
                                WHERE LAT.id_logs = $idLogs";
        
        $execute_query2 = mysqli_query($con, $auditTrail_query2);                                       
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
      if(!isset($_SESSION['empId'])){
        header("Location:index.php");
      }
      else{
        if(!isset($_SESSION['idLogs'])){
          header("Location:empLogs.php");
        }
      }
    }
    
  ?> 
  <!-- icon on the title -->
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

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
        <a class="nav-link" href="index.php">
            <i class="fas fa-user"></i>
            <span>Employee List</span>
        </a>
        <a class="nav-link" href="emplogs.php">
            <i class='fas'>&#xf2bb;</i>
            <span>Employee Log Viewer</span>
        </a>
        <a class="nav-link" href="empLogAT.php">
            <i class='fas'>&#xf1da;</i>
            <span>Employee Log Audit Trail</span>
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
          </ul>

        </nav>
        <!-- End of Topbar -->

        
        <div class="container-fluid">
          <!-- Page Heading -->
          <h1 class="h3 mb-2 font-weight-bold text-danger">Employee's Log Viewer</h1>
          <div class="card shadow mb-4">
              <div class="card-header py-3">
                  <h6 id="title" class="m-0 font-weight-bold text-danger" align="center">AUDIT TRAIL VIEW</h6>
              </div>
              
              <!-- EMPLOYEE LOGS with Actual In's only-->
              <div class="card-body" id="divEmployeeLogs">
                <div class="container">
                  <div class="row"> <!-- row b4 dataTable -->
                    <div class="col-3"> <br /> 
                      <div id="divImage">  </div> 
                    </div> 
                    <div class="col-9" >
                        <br /> 
                      <div class="row"> <!-- row Employee Name: -->
                        <div class="col-3">
                          <label class="label text-gray-900">Employee Name:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php echo $empName; ?></strong></label>
                        </div>  
                      </div>
                      <div class ="row" > <!--row Work Name: -->
                        <div class="col-3">
                          <label class="label text-gray-900">Work Name:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php echo $workName; ?></strong></label>
                        </div> 
                      </div>
                      <div class ="row" > <!--row Dept Name: -->
                        <div class="col-3">
                          <label class="label text-gray-900">Department:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php echo $deptName; ?></strong></label>
                        </div> 
                      </div>
                      <div class ="row" > <!--row DTR Location: -->
                        <div class="col-3">
                          <label class="label text-gray-900">DTR Location:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php echo $location; ?></strong></label>
                        </div> 
                      </div>
                      <div class ="row" > <!--row Date Scheduled: -->
                        <div class="col-3">
                          <label class="label text-gray-900">Date Schedule:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php 
                                                                $dutyDate = new DateTime($dutyDate);
                                                                echo $dutyDate->format('F d, Y');
                                                        ?>
                                                </strong>
                            </label>
                        </div> 
                      </div>
                      <div class ="row" > <!--row Login Scheduled: -->
                        <div class="col-3">
                          <label class="label text-gray-900">Login Schedule:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php 
                                                                 $dutyIn = new DateTime($dutyIn);
                                                                 echo $dutyIn ->format('g:i A');
                                                        ?>
                                                </strong>
                            </label>
                        </div> 
                      </div>
                      <div class ="row" > <!--row Logout Scheduled: -->
                        <div class="col-3">
                          <label class="label text-gray-900">Logout Schedule:&nbsp;</label>
                        </div>
                        <div class="col-9 text-left text-gray-900">
                            <label class="label"><strong><?php
                                                                $dutyOut = new DateTime($dutyOut);
                                                                echo $dutyOut ->format('g:i A');
                                                        ?>
                                                </strong>
                            </label>
                        </div> 
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              <!-- /EMPLOYEE LOGS with Actual In's only-->
              
              <div class="card-body" id="tblLogsAT">  <!--card-body TABLE -->
                <div class="table-responsive " id="divDataTable_empLogAT"> <!-- table-responsive -->         
                  <table class="table table-bordered table-bordered table-sm" id="dataTable_AT" width="100%" cellspacing="0"> <!-- table-bordered -->
                    <thead>
                      <tr class="text-center">
                        <th><strong>LOG IN</strong></th>    <!-- column0 -->
                        <th><strong>LOG OUT</strong></th> <!-- column1 -->
                        <th><strong>LATE (min)</strong></th> <!-- column2 -->
                        <th><strong>UNDERTIME (min)</strong></th> <!-- column3 -->
                        <th><strong>VOIDED</strong></th> <!-- column4 -->
                        <th><strong>REMARKS</strong></th> <!-- column5 -->
                        <th><strong>DATE & TIME of EDIT</strong></th> <!-- column6 -->
                        <th><strong>EDITED BY</strong></th> <!-- column7 for lates -->
                      </tr>
                    </thead>
                    <tfoot >
                    </tfoot>
                    <tbody class="text-nowrap">
                      <?php
                            if(isset($execute_query2)){
                                $rowCount = mysqli_num_rows($execute_query2);
                                if($rowCount==0){  
                                }
                                else{
                                          while($result_empLogs = mysqli_fetch_assoc($execute_query2)){ ?>
                                            <tr>
                                                <!-- column0 ActualIn-->
                                                <td class="text-right"><?php 
                                                        $actualIn = new DateTime($result_empLogs['Actual_timeIn_timeOnly']); 
                                                        echo $actualIn->format('g:i A');
                                                    ?>
                                                </td>
                                                
                                                 <!-- column1 Actual Out-->
                                                <td class="text-right"><?php 
                                                        if($result_empLogs['Actual_timeOut_timeOnly']=='00:00:00'){
                                                          echo "MISSED";
                                                        }
                                                        else{
                                                          $actualOut = new DateTime($result_empLogs['Actual_timeOut_timeOnly']);
                                                          echo $actualOut->format('g:i A');
                                                        }
                                                        
                                                    ?>
                                                </td>
                                                
                                                <!-- column2 Minutes late-->
                                                <td class="text-right"><?php echo $result_empLogs['LATES'];?></td>
                                                
                                                <!-- column3 Minutes undertime-->
                                                <td class="text-right"><?php echo $result_empLogs['Undertime'];$result_empLogs['Actual_timeOut_timeOnly'];?></td>
                                                
                                                 <!-- column4 is_void-->
                                                <td class="text-center"><?php
                                                        if($result_empLogs['is_voided']==0){
                                                          echo '<img src="img/no.jpg" height="20" width="20"/>';
                                                        }
                                                        else{
                                                            echo '<img src="img/yes.jpg"  height="30" width="30" />';
                                                        }            
                                                    ?>
                                                </td>
                                                
                                                <!-- column5 remarks -->
                                                <td><?php echo $result_empLogs['remarks']; ?></td>

                                                <!-- column6 DateTime of Edit-->
                                                <td class="text-right"><?php echo $result_empLogs['DateTime_Edit'];?></td>
                                                
                                                 <!-- column7 Edited By-->
                                                <td><?php echo $result_empLogs['EditedBy'];?></td>
                                            </tr>
                                      <?php } 

                                }
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
 
  <script>
   
    $(document).ready(function() {
      //declaration of variables
      
    
      
      
     
      // settings after loading the page 
      $('#sidebarToggle').trigger('click');
      $('#dataTable_AT').DataTable();
       


      //load user's picture
      getUserPic();

      //load employee's picture   
      loadEmpImage(); 


     

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


    });

  </script>
</body>

</html>




