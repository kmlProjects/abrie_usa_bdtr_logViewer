<?php
    session_start();
    require "db/dbconnect.php"; 
    $idLogs =   $_SESSION['idLogs'];
  
    
    if(!$con){
        die("Connection Failed:".mysqli_connect_error());
    }
    try{

        $query = mysqli_query($con, "SELECT concat(E.lname, ', ', E.fname, ' ', left(E.midname,1), '.') AS EName,
                                                        L.work_name,  
                                                        P.program_name,
                                                        T.location,
                                                        L.duty_in AS DutyIn, L.duty_out, 
                                                        L.actual_in AS actual_in, 
                                                        L.actual_out As actual_out,
                                                        L.log_stage, L.min_late, L.min_under                                    
                                            FROM tbl_logs AS L 
                                                INNER JOIN tbl_employee AS E on  E.id_employee = L.id_employee
                                                INNER JOIN tbl_terminal AS T on T.id_terminal = L.id_terminal
                                                INNER JOIN tbl_program AS P on L.id_program_emp = P.id_program
                                            WHERE L.id_logs = $idLogs;");
        
        

        if($query){

            $rowCount = mysqli_num_rows($query);
           
            if($rowCount>0){
                $loop_counter = 0;
                $empLogs_array = array();

                while($result = mysqli_fetch_assoc($query)){
                    $empLogs_array[$loop_counter] = array( "empName"=>$result['EName'],
                                                           "workName"=>$result['work_name'],
                                                           "dept"=>$result['program_name'],
                                                           "location"=>$result['location'],
                                                           "dutyIn"=>$result['DutyIn'],
                                                           "dutyOut"=>$result['duty_out'],
                                                           "actualIn"=>$result['actual_in'],
                                                           "actualOut"=>$result['actual_out'],
                                                           "logStage"=>$result['log_stage'],
                                                           "minLate"=>$result['min_late'],
                                                           "minUnder"=>$result['min_under']);

                    $loop_counter++;
                }
            }
        }

    }
    catch(Exception $e){
        echo 'Caught exception: ', $e->getMessage(), "\n";
    } 
   
    mysqli_close($con);
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
      box-shadow: 0 0 4px 4px rgba(0, 140, 186, 0.5);
    }
    #empPicIn{
      border-radius: 15px;
      border: 1px solid #ddd;
      padding: 5px;
      max-width: 100%;
      box-shadow: 0 0 4px 4px rgba(0, 140, 186, 0.5);
    }
    #empPicOut{
      border-radius: 15px;
      border: 1px solid #ddd;
      padding: 5px;
      max-width: 100%;
      box-shadow: 0 0 4px 4px rgba(0, 140, 186, 0.5);
    }

    #empImage:hover{
      box-shadow: 0 0 4px 4px rgba(1, 100, 156, 0.5);
    }
    
    
</style>

</head>

<body id="page-top" onafterprint="afterPrint()"> <!--  onbeforeprint="beforePrint()" -->

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

            </nav><!-- End of Topbar -->

             <div class="container-fluid" id="report" >
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4" >
                        <h1 class="h3 mb-2 font-weight-bold text-danger">Employee's Log Viewer</h1>
                         <button id="btnprint" class=" btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                        </button> 
                        <!-- <button id="sample" class="btn btn-sm btn-primary"><i class="fas fa-download fa-sm text-white-50"></i>Generate Report</button> -->
                     
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 id="title" class="m-0 font-weight-bold text-danger" align="center">Employee's Log Report</h6>
                    </div>
              
                    <!-- EMPLOYEE LOGS REPORT -->
                    <div class="card-body" id="divReport">
                        <div class="row">
                            <div class="col-2">
                                <div id="divEmpImage"> 
                                </div> 
                            </div>
                            <div class="col-8">
                                <div class="row">
                                    <div class="col-3">
                                        <label class="text-gray-900">Employee Name:</label>
                                    </div>
                                        <div class="col-7">
                                            <label class="text-gray-900"><strong><?php echo $empLogs_array[0]['empName'];?></strong></label>
                                        </div>
                                </div>
                                    
                                <div class ="row">
                                    <div class="col-3">
                                        <label class="text-gray-900">Work Name:</label>
                                    </div>
                                    <div class="col-5">
                                        <label class="text-gray-900"><strong><?php echo $empLogs_array[0]['workName'];?></strong></label>
                                    </div>
                                </div>

                                <div class ="row">
                                    <div class="col-3">
                                        <label class="text-gray-900">Department:&nbsp;</label>
                                    </div>
                                    <div class="col-5">
                                        <label class="text-gray-900"><strong><?php echo $empLogs_array[0]['dept'];?></strong></label>
                                    </div>
                                </div>

                                <div class ="row">
                                    <div class="col-3">
                                        <label class="text-gray-900">DTR Location:</label>
                                    </div>
                                    <div class="col-5">
                                        <label class="text-gray-900"><strong><?php echo $empLogs_array[0]['location'];?></strong></label>
                                    </div>
                                </div>
                            </div> 
                        </div> <!-- end of upper portion of the report -->

                        <br /> <br />

                        <!-- LOGIN DETAILS -->
                        <div class="row">
                            <div class='col-6'>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-gray-900">LOGIN Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center" id="picActualIn">
                                             <!-- <img class="img-fluid" id="empPicIn" style="width: 25rem;" src="img/bong.jpg" alt=""> -->
                                        </div>
                                        <br>
                                        <div class="row"> 
                                            <div class="col-6">
                                                <label class="text-gray-900">Scheduled login time:</label>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-gray-900"><strong><?php 
                                                                                            $date_ = new DateTime($empLogs_array[0]['dutyIn']);
                                                                                            echo $date_->format('M d, Y  - g:i A');
                                                                                    ?>
                                                                            </strong> 
                                                </label>
                                            </div>
                                        </div>  
                                        <div class="row"> 
                                            <div class="col-6">
                                                <label class="text-gray-900">Actual login time:</label>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-gray-900"><strong><?php 
                                                                                            if($empLogs_array[0]['logStage']==1){
                                                                                                echo "NO LOGGED IN";
                                                                                            }
                                                                                            else{
                                                                                                $time_ = new DateTime($empLogs_array[0]['actualIn']);
                                                                                                echo $time_->format('g:i A');
                                                                                            }
                                                                                            
                                                                                    ?>
                                                                            </strong> 
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row"> 
                                            <div class="col-6">
                                                <label class="text-gray-900">Minutes late:</label>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-gray-900"><strong><?php 
                                                                                            if( (is_null($empLogs_array[0]['minLate'])) || ($empLogs_array[0]['logStage']==1))  { 
                                                                                                echo "N/A";
                                                                                            }
                                                                                            else{
                                                                                               echo $empLogs_array[0]['minLate'];
                                                                                            }
                                                                                    ?>
                                                                            </strong> 
                                                </label>
                                            </div>
                                        </div>
                                        
                                       
                                    </div>
                                </div> 
                            </div>  <!-- /LOGIN DETAILS -->

                            <!-- LOGOUT DETAILS -->
                            <div class='col-6'>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-gray-900">LOGOUT Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center" id="picActualOut">
                                            
                                        </div>
                                        <br>
                                        <div class="row"> 
                                            <div class="col-6">
                                                <label class="text-gray-900">Scheduled logout time:</label>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-gray-900"><strong><?php 
                                                                                        $dateOut = new DateTime($empLogs_array[0]['dutyOut']);
                                                                                        echo $dateOut->format('M d, Y  - g:i A');
                                                                                    ?>
                                                                            </strong> 
                                                </label>
                                            </div>
                                        </div>  
                                        <div class="row"> 
                                            <div class="col-6">
                                                <label class="text-gray-900">Actual logout time:</label>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-gray-900"><strong><?php 
                                                                                            if($empLogs_array[0]['logStage']<3){
                                                                                                echo "NO LOGGED OUT";
                                                                                            }
                                                                                            else{
                                                                                                $timeOut = new DateTime($empLogs_array[0]['actualOut']);
                                                                                                echo  $timeOut ->format('g:i A');
                                                                                            }
                                                                                            
                                                                                    ?>
                                                                            </strong>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row"> 
                                            <div class="col-6">
                                                <label class="text-gray-900">Minutes undertime:</label>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-gray-900"><strong><?php if((is_null($empLogs_array[0]['minUnder'])) || ($empLogs_array[0]['logStage']<3)) {
                                                                                                    echo "N/A";
                                                                                           }
                                                                                           else{
                                                                                                    echo $empLogs_array[0]['minUnder'];
                                                                                           }
                                                                                    ?>
                                                                             </strong>
                                                </label>
                                            </div>
                                        </div>
                                        
                                       
                                    </div>
                                </div>
                            </div> <!-- /LOGOUT DETAILS -->

                        </div>
                        

                    </div> <!-- /EMPLOYEE LIST is ACTIVE-->
                
                </div>
           

            </div>
        <div>
    </div><!-- /.content-wrapper -->

    

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
          <span>Copyright &copy; 2019 Abrie Software Innovations</span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

</div><!-- End of Page Wrapper -->

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
  <script src="js/demo/datatables-demo.js"></script>
  <script src="js/printThis.js"></script>
  
  

   <script>

    
    function afterPrint(){
            $('#accordionSidebar').show();
            $('#btnprint').show();
    }

    
    $(document).ready(function() {
        //set everything after loading the page
        loadEmpImage();
        getUserPic(); 
        
        
       
        $('#sidebarToggle').trigger('click');
    
        //print button click event
        $('#btnprint').click(function(){
            $(this).hide();
            $('#accordionSidebar').hide();
            window.print();
        });

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

        function loadEmpImage(){

            $.post("db/dbEmpImage.php", {phpFile:2
                            }, 
                            function(data){
                              $('#divEmpImage').empty().html(data);
                              loadEmpImage_ActualIn();
                              
                            }
        );

        function loadEmpImage_ActualIn(){
             $.ajax({
                 url:   'db/dbLogImage.php',
                 method:   'post',
                 data:   {empLogFlag : 1},
                 success:   function(data){
                                 $('#picActualIn').empty().html(data);
                                 loadEmpImage_ActualOut();
                            }
             });
        }

        function loadEmpImage_ActualOut(){
            $.ajax({
                 url:   'db/dbLogImage.php',
                 method:   'post',
                 data:   {empLogFlag : 2},
                 success:   function(data){
                                 $('#picActualOut').empty().html(data);
                            }
             });
           
        }
    }

    



    });
  </script>
</body>

</html>