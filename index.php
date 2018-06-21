<?php
  session_start();
  if(isset($_SESSION['id'])) unset($_SESSION['id']);
  session_destroy();

  require_once('system/data.php');
  require_once('system/security.php');

  $error = false;
  $error_msg = "";
  $success = false;
  $success_msg = "";


  if(isset($_POST['login-submit'])){
    if(!empty($_POST['email']) && !empty($_POST['password'])){
      $email = filter_data($_POST['email']);
      $password = filter_data($_POST['password']);

      $result = login($email, $password);

      $row_count = mysqli_num_rows($result);

      if($row_count == 1){
        $user = mysqli_fetch_assoc($result);
        session_start();
        $_SESSION['id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        header("Location:search.php");
      }else {
        $error = true;
        $error_msg .= "Leider konnten wir ihre E-Mailadresse oder ihr Passwort nicht finden.<br/>";
      }
    }else {
      $error = true;
      $error_msg .= "Bitte füllen Sie beide Felder aus.<br/>";
    }
  }

  if(isset($_POST['register-submit'])){
    if(!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confirm-password'])){
        $email = filter_data($_POST['email']);
        $password = filter_data($_POST['password']);
        $password_confirm = filter_data($_POST['confirm-password']);
      if($password == $password_confirm){
        if(register($email, $password)){
          $success = true;
          $success_msg .= "Sie haben sich erfolgreich registriert.<br/>";
          $success_msg .= "Bitte loggen Sie sich jetzt ein.<br/>";
        }else{
          $error = true;
          $error_msg .= "Es gibt ein Problem mit der Datenbankverbindung.";
        }
      }else{
        $error = true;
        $error_msg .= "Bitte überprüfen Sie die Passworteingabe.<br/>";
      }
    }else {
      $error = true;
      $error_msg .= "Bitte füllen Sie alle Felder aus.<br/>";
    }
  }

?>
<html !DOCTYPE>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<title>Wortlab - Login</title>

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<!-- Custom CSS -->
		<link rel="stylesheet" href="css/custom.css">
	</head>
	<body>
	  <div class="container">
		<div class="row">
		  <div class="col-md-6 col-md-offset-3">
			<div class="panel panel-default">
			  <div class="panel-heading">
				<div class="row">
				  <div class="col-xs-12">
					<img src="images/wortlab_logo.svg" alt="Logo Wortlab"/>
				  </div>
				</div>
				<div class="btn-group btn-group-justified" role="group">
				  <div class="btn-group" role="group">
					<button type="button" class="btn btn-info" id="login-form-link">
					  <span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Login
					</button>
				  </div>
				  <div class="btn-group" role="group">
					<button type="button" class="btn btn-default" id="register-form-link">
					  <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Registrierung
					</button>
				  </div>
				</div>
			  </div><!--/panel-->
			  <div class="panel-body">
				<div class="row">
				  <div class="col-lg-12">
					<!-- Login-Formular -->
					<form action="index.php" method="post" id="login-form" role="form">
					  <div class="form-group">
						<input type="email" name="email" tabindex="1" class="form-control" placeholder="E-Mail-Adresse">
					  </div>
					  <div class="form-group">
						<input type="password" name="password" tabindex="2" class="form-control" placeholder="Passwort">
					  </div>

					  <div class="form-group">
						<div class="row">
						  <div class="col-xs-6 col-xs-offset-3">
							<input type="submit" name="login-submit" tabindex="3" class="btn btn-primary btn-block" value="einloggen">
						  </div>
						</div>
					  </div>
					</form>
					<!-- /Login-Formular -->

					<form action="index.php" method="post" id="register-form" role="form">
					  <div class="form-group">
						<input type="email" name="email" tabindex="1" class="form-control" placeholder="E-Mail-Adresse">
					  </div>
					  <div class="form-group">
						<input type="password" name="password" tabindex="2" class="form-control" placeholder="Passwort">
					  </div>
					  <div class="form-group">
						<input type="password" name="confirm-password" tabindex="3" class="form-control" placeholder="Passwort bestätigen">
					  </div>
					  <div class="form-group">
						<div class="row">
						  <div class="col-sm-6 col-sm-offset-3">
							<input type="submit" name="register-submit" tabindex="4" class="btn btn-primary btn-block" value="registrieren">
						  </div>
						</div>
					  </div>
					</form>
				  </div>
				</div>
			  </div>
			</div><!--/panel-->
		  </div><!--/column-->
		</div><!--/row-->
		<?php
		  if($success == true){
		?>
			  <div class="alert alert-success" role="alert"><?php echo $success_msg; ?></div>
		<?php
		  }
		  if($error == true){
		?>
			  <div class="alert alert-danger" role="alert"><?php echo $error_msg; ?></div>
		<?php
		  }
		?>
	  </div><!--container-->

	  <!-- jQuery -->
	  <script src="js/jquery-3.3.1.min.js"></script>
	  <!-- Bootstrap Js -->
	  <script src="js/bootstrap.min.js"></script>
	  <script>
	  $(function() {

		$('#login-form-link').click(function(e) {           
		  $("#register-form").fadeOut(100);                 
		  $("#login-form").delay(100).fadeIn(100);          
		  $('#register-form-link').removeClass('btn-info'); 
		  $('#register-form-link').addClass('btn-default'); 
		  $(this).removeClass('btn-default');               
		  $(this).addClass('btn-info');                     
		  e.preventDefault();                               
		});

		$('#register-form-link').click(function(e) {
		  $("#login-form").fadeOut(100);
		  $("#register-form").delay(100).fadeIn(100);
		  $('#login-form-link').removeClass('btn-info');
		  $('#login-form-link').addClass('btn-default');
		  $(this).removeClass('btn-default');
		  $(this).addClass('btn-info');
		  e.preventDefault();
		});

	  });
	  </script>
	</body>
</html>
