<?php
  session_start();
  require "db/dbconnect.php";

  $is_active = $_SESSION['emp_isActive'];
  
  $dtruser = $_SESSION['login_userId'];
  $empID = $_SESSION['empId'];
  $employeeName = $_SESSION['workSched_empName'];
  echo  $employeeName ;
  $isDateFiltered;
  if(isset($_SESSION['isDateFiltered'])){
    $isDateFiltered = $_SESSION['isDateFiltered'];
  }
  else{
    $isDateFiltered = 0;
  }
  
  
  

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
              WHERE E.id_employee = $empID";

              if($isDateFiltered == 3){
                $filter_dateStarted = "'" . $_SESSION['startDate'] ."'";
                $filter_dateEnd = "'" . $_SESSION['endDate'] ."'";

                $query .= "AND WS.duration_from = '$filter_dateStarted' AND  WS.duration_to = '$filter_dateEnd'";

              
              }

              $query .= " ORDER BY WS.work_name ASC";
             
                      
    
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
                                            <input class="form-control" id="wsStartDate" name="startDate" placeholder="Start Date" type="date" style="width:180px"/>
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
                                            <input class="form-control" id="wsEndDate" name="endDate" placeholder="End Date" type="date" style="width:180px"/>
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
                    </tfoot>
                    <tbody class="text-nowrap">
                      <?php
                        if(isset($query)){  
                          $execute_query = mysqli_query($con, $query);
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

      $('#dataTable_workSchedule').DataTable({
        "oLanguage": {
                       "sSearch": "Search:",
                       "emptyTable": "No data available in table"
                    }
      }); //initialize id=dataTable as DataTable
      
      getUserPic();
      loadEmpImage(); 
      GetCurrentDate();

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


        //filter button click event
        
        $(document).on('click','button[data-role=filter]',function(){
          if((dateStart == null || dateEnd == null))  {
            $('#alertNoselectedDates').modal('show');
          }
          else{
              
              $.ajax({
                   url: 'db/session_empid.php',
                   method: 'post',
                   data:{ empId:<?php echo $empID; ?>,
                          filterDate:3,
                          dateStart:dateStart,
                          dateEnd:dateEnd},
                   success: function(data){
                     location.reload(true);
                   }
              }); 
              //waiting for sir ryan's response

            
              
          }
        }); 
        //*****END OF FILTER DATES */

      
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
        
       
        <?php  
              if($isDateFiltered != 3){
        ?>
                  SetDateFilter_DefaultValue(prevDate, CurDate);
        <?php 
              } 
        ?>
          
               
        
       
       
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

      function SetDateFilter_DefaultValue(fromDate, toDate){
        $('#wsStartDate').val(fromDate);
        $('#wsEndDate').val(toDate);
        dateStart = $('#wsStartDate').val();
        dateEnd = $('#wsEndDate').val();

      }

      
    });
  </script>

  
</body>

</html>

