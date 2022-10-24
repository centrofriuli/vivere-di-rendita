<?php

	define('MYSQL_host','localhost');
	define('MYSQL_db_user','root');
	define('MYSQL_db_psw','Inf0r,4t1c1');
	define('MYSQL_db_name','produzione_cf');
	
	//Collegamento al database
	$db = mysqli_connect(MYSQL_host, MYSQL_db_user, MYSQL_db_psw) or die ('Problema di collegamento al database');
	//Seleziono il database per sicurezza
	mysqli_select_db($db, MYSQL_db_name) or die (mysqli_error($db));
	//Attivo la codifica corretta
	mysqli_query($db, "set names 'utf8'");
	
?>