<!-- FOR v.2.0.0
- include the Work Schedule CRUD
  *index.php ---DataTable--Change nge Column Action into Logs
  *index.php ---Add Column in DataTable, name it Schedules with corresponding button
  *add another page --WorkSchedule
-->




<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Login | DTR LOG VIEWER</title>
  <!-- icon on the title -->
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <style>
    #usa_logo{
      width:100%
      
    }
  </style>
</head>



<body class="bg-gradient-danger">

  <div class="container">
    <br /><br />
    <div class=" text-center text-gray-900">
            <!-- 
              
            -->
           <h1> <label class='label' id="appTitle"><span>DTR LOG VIEWER v.2.0.0</span></label> </h1>
    </div>

    <!-- Outer Row -->
    <div class="row justify-content-center">
      <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            
            <br/>
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block"><img src="img/usa.png" id="usa_logo"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                  </div>
                  <form class="user">
                    <div class="form-group">
                      <input type="text" class="form-control form-control-user" id="txtUserName" placeholder="Enter Username...">
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control form-control-user" id="txtUserPass" placeholder="Enter Password...">
                    </div>
                    
                    <input type="button" class="btn btn-danger btn-user btn-block" value= "Login" id="login_submit" onClick="login()">
                  </form>
                </div>
                
              </div>
            </div>
            
          </div>
        </div>
      </div>

    </div>

  </div>

  <div class="modal fade" id="alertPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">INVALID USERNAME or PASSWORD</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Please review your Username and Password.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin-2.min.js"></script>

  <script>
    
    function login(){
        var calledBy = 'login';
        
        

        $.post("db/dblogin.php",
              {
				        username:document.getElementById("txtUserName").value, 
				        password:document.getElementById("txtUserPass").value,
                fromBy:calledBy
              },
				      function(data){               
				 	      if (data == "Success"){
				 	           location.href = "index.php";
					      }
					      else{
                  $('#alertPopUp').modal('show');
					      }
				      }
        );

    }
  
  $(document).ready(function(){
      $("#txtUserName").focus();
  });
  </script>

</body>

</html>
