<?php

       session_start();
       $_SESSION['empId'] = $_POST['empId'];
       
       if(($_POST['viewOption']=='viewLogs') || ($_POST['filter']==2)){
             
              $_SESSION['showOption_logs'] = $_POST['showOption']; 
              $_SESSION['datefilter'] = $_POST['filter'];  
              $_SESSION['def_startDate'] = $_POST['default_dateStart'];
              $_SESSION['def_endDate'] = $_POST['default_dateEnd'];
              $_SESSION['startDate'] = $_POST['dateStart'];
              $_SESSION['endDate'] = $_POST['dateEnd'];
       }
       else{ //for workSchedule.php default settings
              $_SESSION['workSched_empName'] = $_POST['empName'];

              if($_POST['filterDate']==3){//for workSchedule.php with filter Date
                     $_SESSION['isDateFiltered'] = 3;
                     $_SESSION['def_startDate'] = $_POST['dateStart'];
                     $_SESSION['def_endDate'] = $_POST['dateEnd'];
              }
       }


       
       
?>