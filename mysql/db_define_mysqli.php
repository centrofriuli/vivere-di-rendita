<?php

	define('MYSQL_host','localhost');
	define('MYSQL_db_user','centrofriuli');
	define('MYSQL_db_psw','LcGhv2ArPzGW9uyK');
	define('MYSQL_db_name','db_invest');
	
	//Collegamento al database
	$db = mysqli_connect(MYSQL_host, MYSQL_db_user, MYSQL_db_psw) or die ('Problema di collegamento al database');
	//Seleziono il database per sicurezza
	mysqli_select_db($db, MYSQL_db_name) or die (mysqli_error($db));
	//Attivo la codifica corretta
	mysqli_query($db, "set names 'utf8'");
	
?>