<?php

require ('../mysql/db_define_mysqli.php');

	$id_simulazione = mysqli_real_escape_string($db, $_POST["id_simulazione"]);
	$tiporichiesta = mysqli_real_escape_string($db, $_POST["tiporichiesta"]);
	$percdecum = mysqli_real_escape_string($db, $_POST["percdecum"]);
	$importodecum = mysqli_real_escape_string($db, $_POST["importodecum"]);

	//RILEVO LA SOMMATORIA, SE PRESENTE, NEI VARI IMPORTI -------------------------
	$sql = "SELECT Max(ds_importo_1) as SumImp_1, Max(ds_importo_2) as SumImp_2, Max(ds_importo_3) as SumImp_3, Max(ds_importo_4) as SumImp_4, ";
	$sql .= "Max(ds_importo_5) as SumImp_5, Max(ds_importo_6) as SumImp_6 FROM tb_temp_acc WHERE id_simulazione = '". $id_simulazione. "'";
		
	if ($result=mysqli_query($db,$sql)){
		if ($result->num_rows > 0) {
			while ($obj = $result->fetch_object()) {
				$SumImp_1 = $obj->SumImp_1;
				$SumImp_2 = $obj->SumImp_2;
				$SumImp_3 = $obj->SumImp_3;
				$SumImp_4 = $obj->SumImp_4;
				$SumImp_5 = $obj->SumImp_5;
				$SumImp_6 = $obj->SumImp_6;
			}	
		}
	}
	
	//Sommo tutti i valori dei PA
	$TotalePA = ($SumImp_1 + $SumImp_2 + $SumImp_3 + $SumImp_4 + $SumImp_5 + $SumImp_6);
	
	//---------------------------------------------------------------------------------------------
	
	//RILEVO LA SOMMATORIA, SE PRESENTE, NEI VARI IMPORTI -------------------------
	$sql = "SELECT Max(ds_importo_1) as SumImp_1, Max(ds_importo_2) as SumImp_2, Max(ds_importo_3) as SumImp_3, Max(ds_importo_4) as SumImp_4, ";
	$sql .= "Max(ds_importo_5) as SumImp_5, Max(ds_importo_6) as SumImp_6 FROM tb_temp_vers WHERE id_simulazione = '". $id_simulazione. "'";
		
	if ($result=mysqli_query($db,$sql)){
		if ($result->num_rows > 0) {
			while ($obj = $result->fetch_object()) {
				$SumImp_1 = $obj->SumImp_1;
				$SumImp_2 = $obj->SumImp_2;
				$SumImp_3 = $obj->SumImp_3;
				$SumImp_4 = $obj->SumImp_4;
				$SumImp_5 = $obj->SumImp_5;
				$SumImp_6 = $obj->SumImp_6;
			}	
		}
	}
	
	//Sommo tutti i valori dei PU
	$TotalePU = ($SumImp_1 + $SumImp_2 + $SumImp_3 + $SumImp_4 + $SumImp_5 + $SumImp_6);
	
//******************* TOTALE PA + PU ********************
	$TotaleFinale = ($TotalePA + $TotalePU);
//*******************************************************

	//FACCIO IL CALCOLO DELL'IMPORTO O DELLA PERCENTUALE -------------------------
			
	if($TotaleFinale == 0){
		$valImporto = 0;
		$ValoreFinale = $valImporto;
	} else {
		
		if ($tiporichiesta == 'percentuale'){  
			$valImporto = round((($TotaleFinale / 100) * $percdecum),2);
			$ValoreFinale = $valImporto;
		} 
		if ($tiporichiesta == 'importo'){
			$valPerc = round((($importodecum / $TotaleFinale) * 100),2);
			$ValoreFinale = $valPerc;
		}
		
	}
	
	//SALVO ORA I VALORI NELLA TESTATA COSI POI TUTTO IL CALCOLO PROCEDE -------------------------
	
	if ($tiporichiesta == 'percentuale'){  	
		$query = "UPDATE tb_dati_t SET ds_importo_decum='$valImporto' ";
		$query = $query. "WHERE id_simulazione='$id_simulazione'";
	}
	if ($tiporichiesta == 'importo'){  	
		$query = "UPDATE tb_dati_t SET n_perc_decum='$valPerc' ";
		$query = $query. "WHERE id_simulazione='$id_simulazione'";
	}
	
	if ($db->query($query) === TRUE) 
	{
		//echo "Aggiornamento dati avvenuto correttamente";
	} else {
		echo "Errore durante aggiornamento del record: " . $db->error;
	}
		
	
	echo $ValoreFinale;	
	
	$db->close();

?>