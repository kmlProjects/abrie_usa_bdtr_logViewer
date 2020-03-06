<?php

       session_start();
       $_SESSION['empId'] = $_POST['empId'];
       $_SESSION['showOption_logs'] = $_POST['showOption']; 
       $_SESSION['datefilter'] = $_POST['filter'];  
       $_SESSION['def_startDate'] = $_POST['default_dateStart'];
       $_SESSION['def_endDate'] = $_POST['default_dateEnd'];
       $_SESSION['startDate'] = $_POST['dateStart'];
       $_SESSION['endDate'] = $_POST['dateEnd'];
       
?>