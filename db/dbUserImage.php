<?php
    session_start();
    require "dbconnect.php"; 

    $userId = $_SESSION['login_empId'];

    if(!$con){
        die("Connection Failed:".mysqli_connect_error());
    }

    try{
        $userImage = mysqli_query($con, "SELECT E.picture 
                                                FROM tbl_enployee AS E
                                                WHERE E.id_employee = $userId;");
        
        $rowCount = mysqli_num_rows($userImage);

        if( $rowCount = 1){
            while($result_empImage = mysqli_fetch_assoc($userImage)){ 
                if(is_null($result_empImage['picture'])){
                    echo '<img class="img-profile rounded-circle" src="img/noImage.jpg" alt="">';
                }
                else{
                    echo '<img class="img-profile rounded-circle" src="data:image/jpeg;base64,'.base64_encode($result_empImage["picture"]).'" alt="">';
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
