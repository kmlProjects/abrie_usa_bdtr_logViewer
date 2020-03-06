<?php 
    session_start();
    require("dbconnect.php");

    if(isset($_POST['forActualIn'])){
        $edited_ActualIn =  $_POST['forActualIn'];
    }
    else{
        $edited_ActualIn =null;
    }
        
    //what if null ang forActualOut?
    if(isset($_POST['forActualOut'])){
        $edited_ActualOut = $_POST['forActualOut'];
    }
    else{
        $edited_ActualOut = null;
    }

    if(isset($_POST['wasRemovedLogin'])){
        $isLoginRemove = $_POST['wasRemovedLogin'];
    }
    else{
        $isLoginRemove = null;
    }

    if(isset($_POST['wasRemovedLogout'])){
        $isLogoutRemove = $_POST['wasRemovedLogout'];
    }
    else{
        $isLogoutRemove = null;
    }
    
   $id_logs = $_POST['id_log'];
   $idUser = $_SESSION['login_empId'];
   $update_query="";
   $mode = $_POST['mode'];
   $tbllogs_at_id;

    if(!$con){
        die("Connection Failed:".mysqli_connect_error());
    }
    
    try{
      
        if($mode=='update'){
            $comments = $_POST['editComment'];
            $logstage = $_POST['log_stage'];

            if (($isLoginRemove==1) AND ($isLogoutRemove==1)){           
                $update_query = "UPDATE tbl_logs
                                   SET actual_out = '1750-01-01 00:00:00', 
                                       actual_in = '1750-01-01 00:00:00',
                                        log_stage = 1 
                                   WHERE id_logs =$id_logs;";
    
            } 
            else if(($isLoginRemove==1) AND ($isLogoutRemove==0)){
                 $update_query = "UPDATE tbl_logs
                                     SET actual_in = '1750-01-01 00:00:00',
                                     log_stage = 1
                                     WHERE id_logs =$id_logs;";
             }
            else if (($isLoginRemove==0) AND ($isLogoutRemove==1)){
                 $update_query = "UPDATE tbl_logs
                                     SET actual_out = '1750-01-01 00:00:00',
                                     log_stage = 2
                                     WHERE id_logs = $id_logs;";
            }
           
            else{       
                 $update_query = "UPDATE tbl_logs SET ";
                 
                 
                  if((isset($_POST['forActualIn'])) AND (!isset($_POST['forActualOut']))){
                    $update_query .= "actual_in = '" . $edited_ActualIn . "', min_late=0,";
                  }
                  else if((!isset($_POST['forActualIn'])) AND (isset($_POST['forActualOut']))){
                    $update_query .= "actual_out = '" . $edited_ActualOut . "', min_under=0, ";
                  }
                  else if((isset($_POST['forActualIn'])) AND (isset($_POST['forActualOut']))){
                    $update_query .= "actual_in = '" . $edited_ActualIn . "', min_late=0 ,
                                      actual_out = '".  $edited_ActualOut . "', min_under=0,";
                  } 
                
                  $update_query .= " log_stage = " . $logstage . 
                                " WHERE id_logs = " . $id_logs.";";
             }
             
             $executeUpdate_query = mysqli_query($con, $update_query);
    
           
            if($executeUpdate_query){
              //inserting audit trail is done by trigger in database see after update trigger of tbl_logs table
             
              //select the id_logs_at of the newly added record from tbl_logs_at
              $at_id = "SELECT MAX(id_logs_at) AS id FROM tbl_logs_at;";
             
              $execute_at_id = mysqli_query($con, $at_id);
    
              while($result_max_at_id = mysqli_fetch_assoc($execute_at_id)){
                $tbllogs_at_id = $result_max_at_id['id'];
              }
    
              //after fetching the id_logs_at, need to update some columns of it
    
                $updateAuditTrail = "UPDATE tbl_logs_at 
                                         SET 
                                             remarks = '". $comments ."', 
                                             id_user = $idUser
                                         WHERE id_logs_at = $tbllogs_at_id;";
    
                $execute_updateAuditTrail = mysqli_query($con, $updateAuditTrail);
    
                if($execute_updateAuditTrail){ 
                    echo "success";
                }
                else{
                    echo "error in updating tbl_logs_at";
                }
              
                  
            }
            else{
                echo "error in updating tbl_logs";
                //echo $update_query;

            }
             
        }
        else if($mode=='void'){
            $comments = $_POST['voidComment'];

            $update_query = "UPDATE tbl_logs
                                SET is_voided = 1
                                WHERE id_logs = $id_logs;";

            $executeUpdate_query = mysqli_query($con, $update_query);

            if($executeUpdate_query){
                $at_id = "SELECT MAX(id_logs_at) AS id FROM tbl_logs_at;";
             
                $execute_at_id = mysqli_query($con, $at_id);
                
                while($result_max_at_id = mysqli_fetch_assoc($execute_at_id)){
                    $tbllogs_at_id = $result_max_at_id['id'];
                }

                $updateAuditTrail = "UPDATE tbl_logs_at 
                                        SET id_user = $idUser,
                                            remarks = '". $comments ."'
                                        WHERE id_logs_at = $tbllogs_at_id;";

                $execute_updateAuditTrail = mysqli_query($con, $updateAuditTrail);

                if($execute_updateAuditTrail){
                    echo "success";
                }
                else{
                    echo "error in updating tbl_logs_at";
                }

            }
            else{
                echo "error";
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