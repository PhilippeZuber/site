<!--Modal window for Register Login-->
<div class="modal fade" id="RegisterModal" tabindex="-1" role="dialog" aria-labelledby="Einloggen / Registrieren">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="container">
          <div class="row">
            <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-default">
              <div class="panel-heading">
                <div class="row">
                  <div class="col-xs-12">
                    <img src="img/wortlab_logo.svg" alt="Logo Wortlab"/>
                  </div>
                </div><!--/row-->
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
                </div><!--/Button group-->
              </div><!--/panel-heading-->
              <div class="panel-body">
                <div class="row">
                  <div class="col-lg-12">
                  <!-- Login-Formular -->
                  <form action="index.php" method="post" id="login-form" role="form">
                    <div class="form-group">
                      <input type="email" name="email" tabindex="1" class="form-control" placeholder="E-Mail-Adresse">
                    </div>
                    <div class="form-group" id="mail">
                      <input type="password" name="password" tabindex="2" class="form-control" placeholder="Passwort">
                    </div>
                    <div class="form-group">
                      <div class="row">
                        <div class="col-xs-6 col-xs-offset-3">
                        <input type="submit" name="login-submit" tabindex="3" class="btn btn-primary btn-block" value="einloggen" id="login">
                        <p class="text-center" id="forgotpw"><small>Passwort vergessen?</small></p>
                      </div>
                    </div>
                  </div> <!--/col-lg-12-->
                </form>
                <!-- /Login-Formular -->
                <!-- Registrieren Formular -->
                <form action="index.php" method="post" id="register-form" role="form">
                  <div class="form-group row">
                    <div class="col-xs-6">                     
                        <select class="form-control form-control-sm" id="gender" name="gender" tabindex="1">
                            <option selected="selected" value="Frau">Frau</option>
                            <option value="Herr">Herr</option>
                        </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="col-xs-6">
                        <input  type="text" class="form-control form-control-sm"
                                id="firstname" placeholder="Vorname" tabindex="2"
                                name="firstname">
                    </div>
                    <div class="col-xs-6">
                        <input  type="text" class="form-control form-control-sm"
                                id="lastname" placeholder="Nachname" tabindex="3"
                                name="lastname">
                    </div>
                  </div>
                  <div class="form-group">
                    <input type="email" name="email" tabindex="4" class="form-control" placeholder="E-Mail-Adresse">
                  </div>
                  <div class="form-group">
                    <input type="password" name="password" tabindex="5" class="form-control" placeholder="Passwort">
                  </div>
                  <div class="form-group">
                    <input type="password" name="confirm-password" tabindex="6" class="form-control" placeholder="Passwort bestätigen">
                  </div>
                  <div class="form-group row">
                    <div class="col-sm-6 col-sm-offset-3">
                      <input type="submit" name="register-submit" tabindex="8" class="btn btn-primary btn-block" value="registrieren" id="register">
                    </div>
                  </div>
                </form>
                <!-- /Registrieren Formular -->
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
          <script>
          $(function() {

          $('#login-form-link').click(function(e) {           
            $("#register-form").fadeOut(100);
            $('#mailsend').attr({
              'name': 'login-submit',
              'value': 'einloggen',
              'id': 'login'
            });                 
            $("#login-form").delay(100).fadeIn(100);
            $("#mail").delay(100).fadeIn(100);          
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

          $('#forgotpw').click(function(e) {
            $("#mail").fadeOut(100);
            $('#login').attr({
              'name': 'mailsend',
              'value': 'Passwort senden',
              'id': 'mailsend'
            });
            e.preventDefault();
          });

          });
          </script>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Schliessen</button>
        </div>
        </div><!-- /modal content-->
    </div><!-- /modal dialog-->
</div><!-- /modal user window-->