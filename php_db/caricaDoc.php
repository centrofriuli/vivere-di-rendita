<?php

require ('../mysql/db_define_mysqli.php');

	$id_simulazione = mysqli_real_escape_string($db, $_POST["id_simulazione"]);
	$risultati = array();
	
	$sql = "SELECT id_simulazione, ds_nome, ds_cognome, ds_email, n_eta, n_eta_scad, n_perc_decum, n_inter_decum, ds_importo_decum ";
	$sql = $sql. "FROM tb_dati_t WHERE id_simulazione = '". $id_simulazione. "'";
	
	
	$result = $db->query($sql);	
	
	if ($result->num_rows > 0) {
		
		while($row = $result->fetch_assoc()) {
			$risultati[] = $row;
		}
		
		echo json_encode($risultati);
		
	} else {
		echo "NoData";
	}
	
	$db->close();

?>