<?php
   
    
    session_start();
    require("dbconnect.php");
    

    if(!$con){
        die("Connection Failed:".mysqli_connect_error()); 
    }
    else{

            try{

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
                    $workschedId = 0;
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
                    Conflict_Checker($mode, $workschedId, $durationFrom,$durationTo,$log_start,$log_end,$empId,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat, $con);
                   
                    
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
                                WorkSched_AuditTrail($mode,$last_id, $empId, $workName, $durationFrom, $durationTo, $log_start, $log_end,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,$id_terminal, $is_perpetual,$is_voided, $id_program, $con);

                        }
                        
                }                 
                else if($mode == 'edit'){
                    $workschedId = $_POST['phpWorkSchedId'];
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
                    $is_approved = 0;
                    $is_voided = $_POST['php_isVoid'];
                    $is_outnextday = $_POST['php_isOutNextDay'];
                    $is_perpetual = $_POST['php_isPerpetual'];
                    $roomCode = $_POST['php_roomCode'];
                    $id_program = $_POST['php_idProgram'];
                    

                    //check conflict schedules
                    Conflict_Checker($mode, $workschedId, $durationFrom, $durationTo,$log_start,$log_end,$empId,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat, $con);
                    
                    //if there is no conflict, flow of data will continue from here
                    $sql_updateWS = "UPDATE tbl_work_schedule SET work_name = '$workName', duration_from = '$durationFrom', duration_to = '$durationTo', log_start =  TIME('$log_start'), log_end = TIME('$log_end'), is_sunday = $is_sun, is_monday = $is_mon, is_tuesday = $is_tue, is_wednesday = $is_wed, is_thursday = $is_thu, is_friday = $is_fri, is_saturday = $is_sat, id_terminal = $id_terminal, id_department = $id_dept, id_emp_dept = $id_emp_dept, update_next = '$update_next', update_last = '$update_last', is_approved = $is_approved, is_voided = $is_voided, is_outnextday = $is_outnextday, is_perpetual = $is_perpetual, room_code = '$roomCode', id_program = $id_program WHERE id_work_schedule = $workschedId;";
                    
                  
                    
                    $execute_update = mysqli_query($con, $sql_updateWS);
                   
                    
                    if(!$execute_update){
                        echo "00000";
                    }
                    else{
                        //run the needed sql syntax from sir Ryan's note
                        $update_tblLogs = "UPDATE tbl_logs SET is_voided = 1, update_log = NOW(6) WHERE id_work_schedule = $workschedId AND actual_in > NOW();";
                        
                        $execute_updateLogs = mysqli_query($con,$update_tblLogs);

                        if(!$execute_updateLogs){
                            echo "000";
                        }
                        else{
                            WorkSched_AuditTrail($mode,$workschedId, $empId, $workName, $durationFrom, $durationTo, $log_start, $log_end,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,$id_terminal, $is_perpetual, $is_voided, $id_program, $con);
                        }
                        
                                                 
                    }
                    
                    
                    
                    
                }
                else if($mode == 'void'){
                    $workschedId = $_POST['phpWorkSchedId'];
                    $empId = $_POST['php_empId'];
                    $update_last = '2100-12-01';
                    $update_next = '2100-12-01';

                    $void_tblWS = "UPDATE tbl_work_schedule SET is_voided = 1, update_next =  '$update_next', update_last = '$update_last' WHERE id_work_schedule = $workschedId;";

                    //echo $void_tblWS;
                    
                    $execute_void = mysqli_query($con, $void_tblWS);

                    if(!$execute_void){
                        echo "2"; //error in voiding  record
                    }
                    else{
                        $update_tblLogs = "UPDATE tbl_logs SET is_voided = 1, update_log = NOW(6) WHERE id_work_schedule = $workschedId;";

                        $execute_updateLogs = mysqli_query($con,$update_tblLogs);
                        
                        if(!$execute_updateLogs){
                            echo "22"; //error in voiding tbl_logs
                        }
                        else{

                            //record in audit trail
                            WorkSched_AuditTrail($mode, $workschedId, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $con);
                            
                            echo "1";
                        }
                        

                    }
                    
                }
            }

            catch(Exception $e){
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
            finally{
                mysqli_close($con);
            }

            
            
            
    }

    function WorkSched_AuditTrail($transMode, $latest_id, $employeeID, $workName, $durationFrom, $durationTo, $log_start, $log_end,$is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,$id_terminal,$is_perpetual, $isVoided, $id_program, $conn){
       
            $username = $_SESSION['username'];
            $id_user = $_SESSION['login_userId'];
        if($transMode=='add'){
             
            $sql_at = "INSERT INTO tbl_work_schedule_at 
                                VALUES (null, $latest_id, $employeeID, '$workName', 0, 0, 
                                        '$durationFrom', '$durationTo',
                                         TIME('$log_start'), TIME('$log_end'),
                                         $is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,
                                         $id_terminal, NOW(), '$username',  $id_user, 'Create', '', 
                                         1, $isVoided, $is_perpetual, $id_program,'')";
        }
        else if ($transMode=='edit'){
            $sql_at = "INSERT INTO tbl_work_schedule_at 
                                VALUES (null, $latest_id, $employeeID, '$workName', 0, 0, 
                                        '$durationFrom', '$durationTo',
                                         TIME('$log_start'), TIME('$log_end'),
                                         $is_sun, $is_mon, $is_tue, $is_wed, $is_thu, $is_fri, $is_sat,
                                         $id_terminal, NOW(), '$username',  $id_user, 'Modify', '', 
                                         0, 0, $is_perpetual, $id_program,'')";

                            //need to ask sir Ryan re approver and is_approved during edit mode...
        }
        else if ($transMode=='void'){
            $sql_at = "INSERT INTO tbl_work_schedule_at (id_work_schedule_at, id_work_schedule, date_time, username, id_user, type, is_voided) VALUES (null, $latest_id, NOW(), '$username',  $id_user, 'Modify', 1);";
        }        
        
        
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

    function Conflict_Checker($transMode, $workSchedId, $durationFrom, $durationTo, $logStart, $logEnd, $employeeID, $sun, $mon, $tue, $wed, $thu, $fri, $sat, $conn){

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
                    if($transMode == 'add'){
                        $sql_checkSched = "SELECT work_name, DATE_FORMAT(duration_from, '%b. %d,%Y') As duration_from, DATE_FORMAT(duration_to, '%b. %d,%Y') As duration_to, TIME_FORMAT(log_start, '%h:%i %p') As log_start, TIME_FORMAT(log_end, '%h:%i %p') As log_end FROM tbl_work_schedule WHERE (('$durationFrom' BETWEEN duration_from AND duration_to OR '$durationTo' BETWEEN duration_from AND duration_to) OR (duration_from > '$durationFrom'  AND duration_to < '$durationTo')) AND ((TIME('$logStart') BETWEEN log_start AND log_end OR TIME('$logEnd') BETWEEN log_start AND log_end) OR (log_start > TIME('$logStart') AND log_end < TIME('$logEnd'))) AND  id_employee = $employeeID  AND $specificDay;";
                    }
                    else if ($transMode == 'edit'){
                        $sql_checkSched = "SELECT work_name, DATE_FORMAT(duration_from, '%b. %d,%Y') As duration_from, DATE_FORMAT(duration_to, '%b. %d,%Y') As duration_to, TIME_FORMAT(log_start, '%h:%i %p') As log_start, TIME_FORMAT(log_end, '%h:%i %p') As log_end FROM tbl_work_schedule WHERE   (('$durationFrom' BETWEEN duration_from AND duration_to OR '$durationTo' BETWEEN duration_from AND duration_to) OR (duration_from > '$durationFrom'  AND duration_to < '$durationTo')) AND ((TIME('$logStart') BETWEEN log_start AND log_end OR TIME('$logEnd') BETWEEN log_start AND log_end) OR (log_start > TIME('$logStart') AND log_end < TIME('$logEnd'))) AND  id_employee = $employeeID  AND id_work_schedule != $workSchedId AND $specificDay;";
                    }
                    

                   
                    
                  //  echo $sql_checkSched;
                    
                    //execute the $sql_checkSched
                    $execute_sql_checkSched = mysqli_query($conn,  $sql_checkSched);

                    if(!$execute_sql_checkSched){
                        $isConflict = '000'; //error in executing checking of schedules
                    }
                    else{
                        $resultCount = mysqli_num_rows($execute_sql_checkSched);

                    
                        if($resultCount>0){
                            $isConflict = '0'; //with conflict;
                            while($result=mysqli_fetch_assoc($execute_sql_checkSched)){
                                $_SESSION['workNameConflict'] = $result['work_name'];
                                $_SESSION['durationFrom'] = $result['duration_from'];
                                $_SESSION['durationTo'] = $result['duration_to'];
                                $_SESSION['log_start'] = $result['log_start'];
                                $_SESSION['log_end'] = $result['log_end'];            
                            }
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