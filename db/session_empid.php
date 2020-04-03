<?php

       session_start();
       $_SESSION['empId'] = $_POST['empId'];
       
       
       if($_POST['viewOption']=='viewLogs'){
             
              $_SESSION['showOption_logs'] = $_POST['showOption']; 
              $_SESSION['datefilter'] = $_POST['filter'];  
              $_SESSION['def_startDate'] = $_POST['default_dateStart'];
              $_SESSION['def_endDate'] = $_POST['default_dateEnd'];
              $_SESSION['startDate'] = $_POST['dateStart'];
              $_SESSION['endDate'] = $_POST['dateEnd'];
       }
       else if ($_POST['viewOption']=='viewWorkSched'){ 
              $isDefaultDate = $_POST['isDateFiltered'];
              
              $_SESSION['WS_def_startDate'] = $_POST['dateStart'];
              $_SESSION['WS_def_endDate'] = $_POST['dateEnd'];

              if($isDefaultDate==0){
                     $_SESSION['workSched_empName'] = $_POST['empName'];
                     $_SESSION['WS_DateFiltered'] = 0;
                     
              }
              else{
                     $_SESSION['WS_DateFiltered'] = 1;
                     
              }
       }


    


       
       
?>