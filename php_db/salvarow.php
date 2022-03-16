<?php

require ('../mysql/db_define_mysqli.php');

//Recupero l'array
$RigheTable = json_decode($_POST['RigheTable']);
//Recupero alcuni dati necessari
$TbChiamante = $RigheTable[0][0];
$id_simulazione = $RigheTable[0][1];

//Devo cancellare le righe presenti perchè le voglio riscrivere da zero
// preparo la query
if($TbChiamante == 'tblDataSx') {
	$query = "DELETE FROM tb_tasso_acc_r WHERE id_simulazione = '". $id_simulazione. "'";
}
if($TbChiamante == 'tblDataDx') {
	$query = "DELETE FROM tb_tasso_dec_r WHERE id_simulazione = '". $id_simulazione. "'";
}

//Eseguo la query
if ($db->query($query) === TRUE) {
	//echo "Eliminazione avvenuta correttamente";
} else {
	echo "Errore durante l'eliminazione dei records" . $db->error;
}

//Procedo all'inserimento multiplo delle righe
$query = "";
$nrighe = count($RigheTable);

if($TbChiamante == 'tblDataSx') {
	
	for ($i = 0; $i < $nrighe; ++$i) {
		
		if($nrighe == ($i+1)) {	
			$query = $query. "INSERT INTO tb_tasso_acc_r(id_simulazione, id_riga, n_anni, ds_importo, n_perc_inter, n_costo_perc) ";
			$query = $query. "VALUES ";
			$query = $query. "('". $RigheTable[$i][1]. "','". $RigheTable[$i][2]. "','". $RigheTable[$i][3]. "','". $RigheTable[$i][4]. "','". $RigheTable[$i][5]. "','". $RigheTable[$i][6]. "')";
		} else {
			$query = $query. "INSERT INTO tb_tasso_acc_r(id_simulazione, id_riga, n_anni, ds_importo, n_perc_inter, n_costo_perc) ";
			$query = $query. "VALUES ";
			$query = $query. "('". $RigheTable[$i][1]. "','". $RigheTable[$i][2]. "','". $RigheTable[$i][3]. "','". $RigheTable[$i][4]. "','". $RigheTable[$i][5]. "','". $RigheTable[$i][6]. "');";		
		}	
		
	}	

}

if($TbChiamante == 'tblDataDx') {	
	
	for ($i = 0; $i < $nrighe; ++$i) {
		
		if($nrighe == ($i+1)) {	
			$query = $query. "INSERT INTO tb_tasso_dec_r(id_simulazione, id_riga, n_anno, ds_importo, n_perc_inter, n_costo_perc) ";
			$query = $query. "VALUES ";
			$query = $query. "('". $RigheTable[$i][1]. "','". $RigheTable[$i][2]. "','". $RigheTable[$i][3]. "','". $RigheTable[$i][4]. "','". $RigheTable[$i][5]. "','". $RigheTable[$i][6]. "')";
		} else {
			$query = $query. "INSERT INTO tb_tasso_dec_r(id_simulazione, id_riga, n_anno, ds_importo, n_perc_inter, n_costo_perc) ";
			$query = $query. "VALUES ";
			$query = $query. "('". $RigheTable[$i][1]. "','". $RigheTable[$i][2]. "','". $RigheTable[$i][3]. "','". $RigheTable[$i][4]. "','". $RigheTable[$i][5]. "','". $RigheTable[$i][6]. "');";		
		}	
		
	}	

}

//Salvataggio multiriga
if ($db->multi_query($query) === TRUE) {
    //echo "Nuovi records creati correttamente";
} else {
    echo "Error: " . $query . "<br>" . $db->error;
}

//Chiudo la connessione
mysqli_close($db); 
	
?>