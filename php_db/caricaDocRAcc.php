<?php

require ('../mysql/db_define_mysqli.php');

	$id_simulazione = mysqli_real_escape_string($db, $_POST["id_simulazione"]);
	$risultati = array();
	
	$sql = "SELECT id_simulazione, id_riga, n_anni, ds_importo, n_perc_inter, n_costo_perc ";
	$sql = $sql. "FROM tb_tasso_acc_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";
	
	
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