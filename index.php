  <?php
  session_start();
  require "db/dbconnect.php";

  $is_active = $_SESSION['emp_isActive'];
  
  $dtruser = $_SESSION['login_userId'];
 // echo $GLOBAL['emp_isActive'];



  /*old query
    $query = "SELECT E.id_employee, E.rfid_no, concat(E.lname, ', ', E.fname, ' ', left(E.midname,1), '.') AS EName 
                      FROM tbl_employee AS E
                        INNER JOIN tbl_department AS D on D.id_department = E.id_department
                        INNER JOIN tbl_program AS P on D.id_department = P.id_dept
                        INNER JOIN tbl_userprogram AS UP on P.id_program = UP.id_program
                      WHERE  UP.id_dtrviewer_user = $dtruser";
  */

  //nov.19, 2019 updated query
  $query = "SELECT E.id_employee, E.rfid_no, concat(E.lname, ', ', E.fname, ' ', left(E.midname,1), '.') AS EName,
                    P.program_name
                      FROM tbl_employee AS E
                        INNER JOIN tbl_department AS D on D.id_department = E.id_department
                        INNER JOIN tbl_program AS P on D.id_department = P.id_dept
                        INNER JOIN tbl_userprogram AS UP on P.id_program = UP.id_program
                      WHERE  UP.id_dtrviewer_user = $dtruser";
                      
    if($is_active==1){
   // if($GLOBAL['emp_isActive']==1){
        $query .= " and E.is_active = 1";
    }
    else{
      $query .= " and E.is_active = 0";
    }
     

      $query .= " GROUP BY E.id_employee 
                  ORDER BY EName;";
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
		if (!isset ($_SESSION['login_userId']))
		{
			header("Location:login.php");
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
    input[type=checkbox] + label {
            color: #ccc;
            font-style: italic;
    } 
    input[type=checkbox]:checked + label {
            color: #f00;
            font-style: normal; 
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
        <a class="nav-link" id="sideBar-EmpList" href="#">
        <i class="fas fa-user"></i>
          
          <span>Employee List</span></a>
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
          <h1 class="h3 mb-2 font-weight-bold text-danger">Employee's Log Viewer</h1>
          <div class="card shadow mb-4">
              <div class="card-header py-3">
                  <h6 id="title" class="m-0 font-weight-bold text-danger" align="center">Search by Employees' Last Name</h6>
              </div>
              
              <!-- EMPLOYEE LIST is ACTIVE-->
              <div class="card-body" id="divEmployee">
                <!-- below are codes for displaying list of employees where is_active = 1 -->
                <div class="table-responsive text-gray-900" id="div_empListActive">
                  <?php 
                    
                    $employeeList = mysqli_query($con, $query);
                    
                    try{

                      if($employeeList){

                        $rowCount = mysqli_num_rows($employeeList);
                        
                        if($rowCount >= 1){

                          echo  "<table class='table table-bordered text-dark' id='dataTable_' width='100%' cellspacing='0'>
                                   <thead class='text-danger'>
                                      <tr>
                                        <th><strong>Employee Name</strong></th>
                                        <th><strong>RFID No.</strong></th>
                                        <th><strong>Program Name</strong></th>
                                        <th><strong>Action</strong></th>
                                      </tr>
                                    </thead>
                                    <tfoot>
                                      <tr class='text-danger'>
                                        <th colspan='4'>
                                          <div class='custom-control custom-checkbox small text-right text-danger'>
                                            <input type='checkbox'  data-role='emplist_toggle' name='chkBx_empAll' id='chkEmpNotAll' value='Show Active Employees Only'>
                                            <label class='custom-label'><strong>Show Active Employees Only</strong></label>
                                          </div>
                                        </th>                   
                                      </tr>  
                                    </tfoot>
                                    <tbody class='text-dark'>"; 

                                        while($row = mysqli_fetch_assoc($employeeList)){

                                          echo "<tr id=".$row['id_employee'].">";
                                            echo "<td data-target='eName'>".$row['EName']."</td>";
                                            echo "<td data-target='rfidNo'>".$row['rfid_no']."</td>";
                                            echo "<td data-target='progName'>".$row['program_name']."</td>";
                                            echo "<td id='viewButton'>";
                                                echo "<button data-role='view' class='btn btn-danger btn-sm'  data-empid='".$row['id_employee']."' style='width:100%' name='btnViewLogs'>";
                                                    echo "<span class='icon text-white-50'>";
                                                      echo "<i class='fas'>&#xf4fd; </i>";
                                                    echo "</span>";
                                                    echo "<span class='text'>VIEW</span>";
                                                echo "</button>";
                                            echo "</td>";
                                          echo "</tr>";
                                        }
                                    echo "</tbody>";
                                  echo "</table>";

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
                </div>
              </div>
              <!-- /EMPLOYEE LIST is ACTIVE-->

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

  <script>
   
    $(document).ready(function() {
      $('#sidebarToggle').trigger('click');
      var checkBox_status = <?php echo $is_active; ?>;
      var CurDate = null;
      var prevDate = null;
      $('#dataTable_').DataTable({
        "oLanguage": {
                       "sSearch": "Search by Last Name:",
                       "emptyTable": "No data available in table"
                    }
      }); //initialize id=dataTable as DataTable
      
      getUserPic();
      GetCurrentDate();

      if(checkBox_status==1){
        $('#chkEmpNotAll').attr("checked", true);
      }
      else{
        
        $('#chkEmpNotAll').attr("checked", false);
      }
      
      $('#sideBar-EmpList').on('click', function(){
        var from = 'index1';
            $.ajax({
                url:'db/dblogin.php',
                method:'post',
               data:{fromBy:from},
               success: function(data){
                  location.reload(true);
               }

            });
      });
     

      $(document).on('click','button[data-role=view]',function(){  
        var empId = $(this).data('empid');
        
        $.post("db/session_empid.php",{
                                          empId:empId, 
                                          showOption:1, 
                                          filter:1,
                                          default_dateStart:prevDate,
                                          default_dateEnd:CurDate },
          function(data){
            location.replace("empLogs.php");
          }
        );
      });


      $(document).on('click','input:checkbox[data-role=emplist_toggle]',function(){   
       
        
        if(checkBox_status==1){
          var from = 'index0';
            $.ajax({
                url:'db/dblogin.php',
                method:'post',
                data:{fromBy:from},
                success: function(data){
                  location.reload(true);
               }

            });
        }
        else{
          var from = 'index1';
            $.ajax({
                url:'db/dblogin.php',
                method:'post',
                data:{fromBy:from},
                success: function(data){
                  location.reload(true);
                }
            });
        } 
        
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
        var GetDate;
        var GetMonth;
        var prevMonth;
        

        //to get the equivalent number of month
        if(String(fullDate.getMonth()).length == 1){
          GetMonth = fullDate.getMonth()+1;
          prevMonth = "0" + fullDate.getMonth();
          GetMonth = "0" + GetMonth;
          
        }
        else{
          GetMonth = fullDate.getMonth()+1;
          prevMonth = fullDate.getMonth();
        }
        
        //to get the equivalent number of month
        if(String(fullDate.getDate()).length == 1){
         GetDate = fullDate.getDate();
         GetDate = "0" + GetDate;
        }
        else{
          GetDate = fullDate.getDate();
        }
       
        CurDate = fullDate.getFullYear() + "-" +  GetMonth + "-"  + GetDate;
        prevDate = fullDate.getFullYear() + "-" +  prevMonth   + "-"  + "01";
        
       
      }

      
    });
  </script>

  
</body>

</html>

