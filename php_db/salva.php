<?php

require ('../mysql/db_define_mysqli.php');

	$id_simulazione = mysqli_real_escape_string($db, $_POST["id_simulazione"]);
	$nome = mysqli_real_escape_string($db, $_POST["nome"]);
	$cognome = mysqli_real_escape_string($db, $_POST["cognome"]);
	$email = mysqli_real_escape_string($db, $_POST["email"]);
	$eta = mysqli_real_escape_string($db, $_POST["eta"]);
	$etascadenza = mysqli_real_escape_string($db, $_POST["etascadenza"]);
	$percdecum = mysqli_real_escape_string($db, $_POST["percdecum"]);
	$percinterdecum = mysqli_real_escape_string($db, $_POST["percinterdecum"]);
	$importodecum = mysqli_real_escape_string($db, $_POST["importodecum"]);
	
	//Faccio una select per vedere se devo aggiornare il record oppure crearlo nuovo
	$sql = "SELECT id_simulazione FROM tb_dati_t ";
	$sql = $sql. "WHERE id_simulazione = '". $id_simulazione. "'";
	
	$result = $db->query($sql);
	
	if ($result->num_rows > 0) { //Se trovo i dati devo fare l'UPDATE
		
		$query = "UPDATE tb_dati_t SET id_simulazione='$id_simulazione', ds_nome='$nome', ds_cognome='$cognome', ";
		$query = $query. "ds_email='$email', n_eta='$eta', n_eta_scad='$etascadenza', n_perc_decum='$percdecum', ";
		$query = $query. "n_inter_decum='$percinterdecum', ds_importo_decum='$importodecum' ";
		$query = $query. "WHERE id_simulazione='$id_simulazione'";
		
		if ($db->query($query) === TRUE) 
			{
			echo "Aggiornamento dati avvenuto correttamente";
			} 
		else {
			echo "Errore durante aggiornamento del record: " . $db->error;
			}
				
	} else { //Se non trovo i dati faccio l'inserimento
	
		$query = "INSERT INTO tb_dati_t(id_simulazione, ds_nome, ds_cognome, ds_email, n_eta, n_eta_scad, n_perc_decum, n_inter_decum, ds_importo_decum) 
				VALUES
				('$id_simulazione', '$nome', '$cognome', '$email', '$eta', '$etascadenza', '$percdecum', '$percinterdecum', '$importodecum')";
		
		if(mysqli_query($db, $query))
			{
			echo $msg='Salvataggio eseguito correttamente.';
			}
		else {
			echo $msg='Errore durante il salvataggio. Operazione non riuscita.';
			}
		
	}
	
	$db->close();
	
?>