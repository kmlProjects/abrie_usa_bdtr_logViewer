<?php
   
    
    session_start();
    require("dbconnect.php");
    

    if(!$con){
        die("Connection Failed:".mysqli_connect_error()); 
    }
    else{
        
            
            try{
                $workSched_id;
                $empId;
                $workName;
                $durationFrom;
                $durationTo;
                $log_start;
                $log_end;
                $is_sun;
                $is_mon;
                $is_tue;
                $is_wed;
                $is_thu;
                $is_fri;
                $is_sat;
                $id_terminal;
                $id_dept;
                $id_emp_dept;
                $update_next;
                $update_last;
                $is_approved;
                $is_voided;
                $is_outnextday;
                $is_perpetual;
                $roomCode;
                $id_program;
               

                $mode = $_POST['phpMode'];

                if($mode == 'add'){


                    $empId = $_POST['php_empId'];
                    $workName = $_POST['php_workName'];
                    $durationFrom = $_POST['php_durationFrom'];
                    $durationTo = $_POST['php_durationTo'];
                    $is_perpetual = $_POST['php_chkPerpetual'];
                    $log_start = $_POST['php_logStart'];
                    $log_end = $_POST['php_logEnd'];
                    $is_outnextday = $_POST['php_chkNextDayLogOut'];
                    $is_sun = $_POST['php_isSun'];
                    $is_mon = $_POST['php_isMon'];
                    $is_tue = $_POST['php_isTue'];
                    $is_wed = $_POST['php_isWed'];
                    $is_thu = $_POST['php_isThu'];
                    $is_fri = $_POST['php_isFri'];
                    $is_sat = $_POST['php_isSat'];
                    $id_terminal = $_POST['php_idTerminal'];
                    $id_dept = $_SESSION['login_deptId'];
                    $id_emp_dept = $_SESSION['emp_deptID'];
                    $update_last = $_POST['php_updateLast'];
                    $update_next = $_POST['php_updateNext'];
                    $is_approved = 1;
                    $is_voided = 0;
                    $is_outnextday = $_POST['php_isOutNextDay'];
                    $is_perpetual = $_POST['php_isPerpetual'];
                    $roomCode = $_POST['php_roomCode'];
                    $id_program = $_POST['php_idProgram'];


                    //check conflict schedules
                    Conflict_Checker($durationFrom,$durationTo,$log_start,$log_end,$empId,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat, $con);
                   
                    //if there is no conflict, flow of data will continue from here
                    $sql_insert = "INSERT INTO tbl_work_schedule 
                                                    VALUES(null,$empId,'$workName','$durationFrom', '$durationTo', 
                                                            TIME('$log_start'),TIME('$log_end'),
                                                            $is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,
                                                            $id_terminal, $id_dept, $id_emp_dept, '$update_next', '$update_last',
                                                            $is_approved, $is_voided, $is_outnextday,$is_perpetual, '$roomCode', $id_program, 0)";




                        $execute_insert = mysqli_query($con, $sql_insert);

                        if(!$execute_insert){
                                echo "00";//ERROR in saving work schedule details!
                        }
                        else{
                                
                                //get the latest primary key
                                 $last_id = $con->insert_id;

                                //code to add same data in its audit trail table
                                WorkSched_AuditTrail($mode,$last_id, $empId, $workName, $durationFrom, $durationTo, $log_start, $log_end,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,$id_terminal, $is_perpetual, $id_program, $con);

                        }
                } 
                else{
                    echo "why?";
                }
                

            }
            catch(Exception $e){
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
            finally{
                mysqli_close($con);
            }

            
            
            
    }

    function WorkSched_AuditTrail($transMode, $latest_id, $employeeID, $workName, $durationFrom, $durationTo, $log_start, $log_end,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,$id_terminal,$is_perpetual, $id_program, $conn){
        //need to create audit trail in database 
            $username = $_SESSION['username'];
            $id_user = $_SESSION['login_empId'];
         if($transMode=='add'){
             
            $sql_at = "INSERT INTO tbl_work_schedule_at 
                                VALUES (null, $latest_id, $employeeID, '$workName', 0, 0, 
                                        '$durationFrom', '$durationTo',
                                         TIME('$log_start'), TIME('$log_end'),
                                         $is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,
                                         $id_terminal, NOW(), '$username',  $id_user, 'Create', '', 
                                         1, 0, $is_perpetual, $id_program,'')";
            
            // echo $sql_at;
            
            try{
                $execute_insert_at = mysqli_query($conn,$sql_at);

                if(!$execute_insert_at){
                    echo '0000';
                    exit();
                }
                else{
                    echo "1"; //all transaction was successfully executed
                }
            }
            catch(Exception $e){
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        
         }
        
    }

    function Conflict_Checker($durationFrom, $durationTo, $logStart, $logEnd, $employeeID, $sun, $mon, $tue, $wed, $thu, $fri, $sat, $conn){

        $specificDay;
        $isConflict;
        $days_array = array();
        $days_array[0]=(int)$sun;
        $days_array[1]=(int)$mon;
        $days_array[2]=(int)$tue;
        $days_array[3]=(int)$wed;
        $days_array[4]=(int)$thu;
        $days_array[5]=(int)$fri;
        $days_array[6]=(int)$sat;

        $i=0;
        $specificDay='';

        
        while($i<=6){
            switch ($i){
                case 0:
                    if($days_array[$i]==1){
                        $specificDay = 'is_sunday=1';
                    }
                    break;
                case 1:
                    if($days_array[$i]==1){
                        $specificDay = 'is_monday=1';
                    }
                    break;
                case 2:
                    if($days_array[$i]==1){
                        $specificDay = 'is_tuesday=1';
                    }
                    break;
                case 3:
                    if($days_array[$i]==1){
                        $specificDay = 'is_wednesday=1';
                    }
                    break;
                case 4:
                    if($days_array[$i]==1){
                        $specificDay = 'is_thursday=1';
                    }
                    break;
                case 5:
                    if($days_array[$i]==1){
                        $specificDay = 'is_friday=1';
                    }
                    break;
                case 6:
                    if($days_array[$i]==1){
                        $specificDay = 'is_saturday=1';
                    }
                    break;

            }
            
            try{
                if($specificDay!=''){
                    $sql_checkSched = "SELECT work_name, duration_from, duration_to, log_start, log_end
                                            FROM tbl_work_schedule
                                            WHERE   (('$durationFrom' BETWEEN duration_from AND duration_to OR 
                                                    '$durationTo' BETWEEN duration_from AND duration_to) OR 
                                                    (duration_from > '$durationFrom'  AND duration_to < '$durationTo')) AND
                                                    ((TIME('$logStart') BETWEEN log_start AND log_end OR TIME('$logEnd') BETWEEN log_start AND log_end) OR (log_start > TIME('$logStart') AND log_end < TIME('$logEnd'))) 
                                                    AND  id_employee = $employeeID  AND $specificDay;";

                    //echo $sql_checkSched;
                    
                   
                    //execute the $sql_checkSched
                    $execute_sql_checkSched = mysqli_query($conn,  $sql_checkSched);

                    if(!$execute_sql_checkSched){
                        $isConflict = '000'; //error in executing checking of schedules
                    }
                    else{
                        $resultCount = mysqli_num_rows($execute_sql_checkSched);

                       
                        
                        if($resultCount>0){
                            $isConflict = '0'; //with conflict;
                        }
                        else{
                            if($i==6){
                               $isConflict = 'noConflict'; //nocnflict
                                //$isConflict = 'noconflict';
                                //echo 'noconflict';
                            }
                            else{
                                $isConflict='';
                            }
                        }

                        
                        
                    }
                    if(($isConflict == '000') || ($isConflict == '0')){
                        echo $isConflict;
                        exit();
                    }
                    $specificDay='';
                }
                $i++;
            }
            catch(Exception $e){
                    echo 'Caught exception: ', $e->getMessage(), "\n";
            }
                
            
            
        }

        
        
    }

?>