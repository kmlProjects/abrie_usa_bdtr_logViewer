<?php
    session_start();
    require "dbconnect.php"; 

    //if $trigger = 1, meaning dbEmpImage.php was called from empLogs.php
    //if $trigger = 2, meaning dbEmpImage.php was called from empLogReport.php\
    //if $trigger = 3, meaning dbEmpImage.php was called to load user's pic for login purposes
    //if $trigger = 4, meaning dbEmpImage.php was called to load employee's pic in editModal Form.
    
    if(!$con){
        die("Connection Failed:".mysqli_connect_error());
    }

    try{

        if(isset($_POST['phpFile'])){
            $trigger = $_POST['phpFile'];
            

            if($trigger == 3){
                $empID = $_SESSION['login_empId'];
            }
            else{
                $empID = $_SESSION['empId'];
            }
        }

        
         $empImage= mysqli_query($con, "SELECT E.picture
                                                     FROM tbl_employee AS E
                                                     WHERE E.is_active = 1 and E.id_employee = $empID;");

         $rowCount = mysqli_num_rows($empImage);

         if( $rowCount = 1){
             while($result_empImage = mysqli_fetch_assoc($empImage)){ 

                 if(is_null($result_empImage['picture'])){
                     if($trigger==1){
                         echo '<img id="empImage" src="img/noPhoto.png" width="200" height="200" border-radius="8">';
                     }
                     elseif ($trigger==2) {
                         echo '<img id="empImage" src="img/noPhoto.png" width="150" height="150" border-radius="8">';
                     }
                     elseif ($trigger==3) {
                         echo '<img class="img-profile rounded-circle" src="img/noPhoto.png">';
                     }
                     else{
                        echo '<img id="empImage" src="img/noPhoto.png" width="150" height="150" border-radius="8">';
                     }
                    
                   
                 }
                 else{
                     if($trigger==1){
                       
                         echo '<img id="empImage" src="data:image/jpeg;base64,'.base64_encode($result_empImage["picture"]).'" width="200" height="200" border-radius="8">';
                     }
                     elseif ($trigger == 2){
                         echo '<img id="empImage" src="data:image/jpeg;base64,'.base64_encode($result_empImage["picture"]).'" width="150" height="150" border-radius="8">';
                     }
                     elseif ($trigger==3) {
                         echo '<img class="img-profile rounded-circle" src="data:image/jpeg;base64,'.base64_encode($result_empImage["picture"]).'">';
                     }
                     else{
                        echo '<img id="empImage" src="data:image/jpeg;base64,'.base64_encode($result_empImage["picture"]).'" width="150" height="150" border-radius="8">';
                     }
                    
                 }
             }
            
         }
         else{
             echo "error in getting picture";
         }
    }
    catch(Exception $e){
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }
    finally{
        mysqli_close($con);
    }
    

  
    
?>