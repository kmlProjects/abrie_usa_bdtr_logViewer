<?php
    session_start();
    require "dbconnect.php"; 

    //if $picFlag = 1, meaning pic_in should be the output
    //if $picFlag = 2, meaning pic_out should be the output
    
    $picFlag = $_POST['empLogFlag'];
    $idLog =  $_SESSION['idLogs'];
    
   // echo $idLog;
   // echo  $picFlag;
     
    if(!$con){
        die("Connection Failed:".mysqli_connect_error());
        exit();
    }
    try{
        
        $empImage_ActualIn= mysqli_query($con, "SELECT L.pic_in, L.pic_out
                                                    FROM tbl_logs AS L
                                                    WHERE L.id_logs = $idLog;");
        
        $rowCount = mysqli_num_rows($empImage_ActualIn);
        if( $rowCount = 1){
            while($result_empImage = mysqli_fetch_assoc($empImage_ActualIn)){ 
            
                if($picFlag==1){
                    if(is_null($result_empImage['pic_in'])){
                        echo '<img  id="empPicIn" width:"500" height="300" src="img/noImage.jpg" alt="">';
                    }
                    else{
                        echo '<img  id="empPicIn" width:"500" height="300" src="data:image/jpeg;base64,'.base64_encode($result_empImage["pic_in"]).'"  alt="">';
                    }
                }
                else{
                    if(is_null($result_empImage['pic_out'])){
                        echo '<img  id="empPicOut" width:"500" height="300" src="img/noImage.jpg" alt="">';
                    }
                    else{
                        echo '<img  id="empPicOut" width:"500" height="300" src="data:image/jpeg;base64,'.base64_encode($result_empImage["pic_out"]).'"  alt="">';
                    }

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

?>