<?php

       session_start();
     
       
       
       if($_POST['viewOption']=='viewLogs'){
              $_SESSION['empId'] = $_POST['empId'];
              $_SESSION['showOption_logs'] = $_POST['showOption']; 
              $_SESSION['datefilter'] = $_POST['filter'];  
              $_SESSION['def_startDate'] = $_POST['default_dateStart'];
              $_SESSION['def_endDate'] = $_POST['default_dateEnd'];
              $_SESSION['startDate'] = $_POST['dateStart'];
              $_SESSION['endDate'] = $_POST['dateEnd'];
       }
       else if ($_POST['viewOption']=='viewWorkSched'){ 
              //$isDefaultDate = $_POST['isDateFiltered'];
             // $hideExpiredSched = $_POST['hideExpiredSched'];

             $calledFrom = $_POST['calledFrom'];

             if($calledFrom== 'index.php'){
                     $_SESSION['empId'] = $_POST['empId'];
                     $_SESSION['workSched_empName'] = $_POST['empName'];
                     $_SESSION['WS_def_startDate'] = $_POST['dateStart'];
                     $_SESSION['WS_def_endDate'] = $_POST['dateEnd'];
                     $_SESSION['WS_hideExpiredSched'] = $_POST['hideExpiredSched'];
                     $_SESSION['WS_DateFiltered'] = $_POST['isDateFiltered'];
             }
             else{ //worksched.php
                     $_SESSION['WS_hideExpiredSched'] = $_POST['hideExpiredSched'];
                     $_SESSION['WS_DateFiltered'] = $_POST['isDateFiltered'];

                     if($_POST['isDateFiltered'] == 1){
                            $_SESSION['WS_def_startDate'] = $_POST['dateStart'];
                            $_SESSION['WS_def_endDate'] = $_POST['dateEnd'];
                     }

             }

              
            
              
              
       }


    


       
       
?>