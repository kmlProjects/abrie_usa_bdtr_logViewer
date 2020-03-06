<?php


$calledBy= $_POST['fromBy'];


//$securePass = md5($loginpassword);

//$dateNow = date("Y-m-d");

require("dbconnect.php");

if(!$con){
	die("Connection Failed:".mysqli_connect_error());
}

if($con)
{
	if($calledBy=='login'){
		$loginusername =  $_POST['username'];
		$loginpassword =  $_POST['password'];
		
		if ($loginusername != "" && $loginpassword != ""){

				//before
				//$sanitize_loginUsername = filter_var($loginusername, FILTER_SANITIZE_STRING);
				//$sanitize_loginPassowrd = filter_var($loginpassword, FILTER_SANITIZE_STRING);

				//sanitized input
				$sanitize_loginUsername = mysqli_real_escape_string($con, $loginusername);
				$sanitize_loginPassowrd = mysqli_real_escape_string($con, $loginpassword);

				
				try{

					$query = "SELECT U.id_dtrviewer_user, U.dtrviewer_username, U.id_employee,
								concat(E.fname, ' ', left(E.midname,1), '. ', E.lname) AS loginUser
									FROM tbl_dtrviewer_user AS U
										INNER JOIN tbl_employee AS E on E.id_employee = U.id_employee
									WHERE U.dtrviewer_username = ? AND U.dtrviewer_password = ?	 and U.is_active = 1;";
				
					//prepared statement 
					$stmt = mysqli_prepare($con, $query);

					//bind parameters to the placeholders
					mysqli_stmt_bind_param($stmt, "ss", $sanitize_loginUsername, $sanitize_loginPassowrd);

					//execute the sql statement
					$execute_sql = mysqli_stmt_execute($stmt);

					if($execute_sql){
						mysqli_stmt_store_result($stmt);
				
                		if(mysqli_stmt_num_rows($stmt)<=0){
                    		echo "01";
                		}
                		else if (mysqli_stmt_num_rows($stmt)==1){
                    		//bind the result
							mysqli_stmt_bind_result($stmt,$result_dtrviewerID, $resultUserName, $result_idEmp, $result_realName);
                    		session_start();
                    		while (mysqli_stmt_fetch($stmt)) {
								$_SESSION['empName'] = $result_realName;
								$_SESSION['username'] = $resultUserName;
								$_SESSION['login_empId'] = $result_idEmp;
								$_SESSION['login_userId'] = $result_dtrviewerID;
								$_SESSION['emp_isActive'] = 1;
                    		}
    		                mysqli_stmt_close($stmt);
                    		echo "Success";
						}
				
					}	
				}	
				catch(Exception $e){
					echo 'Caught exception: ', $e->getMessage(), "\n";
				}
				
				
		}
	}
	else{
		if($calledBy=='index0'){
			session_start();
			$_SESSION['emp_isActive'] = 0;
			//$GLOBAL['emp_isActive'] =0;
		}
		else{
			session_start();
			$_SESSION['emp_isActive'] = 1;
			//$GLOBAL['emp_isActive'] =1;
		}
		echo "Success";
	}
	
}
  
  
 mysqli_close($con);

?>