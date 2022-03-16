<?php

require ('../mysql/db_define_mysqli.php');
require('../fpdf.php');

//************************************************************************************
//*************************** GESTIONE DATI DI TESTATA *******************************
//************************************************************************************

$id_simulazione = mysqli_real_escape_string($db, $_POST["id_simulazione"]);
$AnnoDiGenerazione = date("Y");

$data = array();
$dataVers = array();
$dataDec = array();
$TassiTB = array();
$TassiTB2 = array();

$StatoPrimaTable = 'S';
$StatoSecondaTable = 'S';

$sql = "SELECT id_simulazione, ds_nome, ds_cognome, n_eta, n_eta_scad, n_perc_decum, n_inter_decum, ds_importo_decum ";
$sql = $sql. "FROM tb_dati_t WHERE id_simulazione = '". $id_simulazione. "'";

if ($result=mysqli_query($db,$sql)){
	
	while ($obj = $result->fetch_object()) {
		$Cognome=$obj->ds_cognome;	$Nome=$obj->ds_nome;	$Eta=$obj->n_eta;	$EtaScad=$obj->n_eta_scad;
		$NPercDecum=$obj->n_perc_decum;	$NPercInter=$obj->n_inter_decum;	$ImportoDecum=$obj->ds_importo_decum;
	}
	
	if (empty($Cognome) || $Cognome == '') {echo Exit('Non ci sono dati da mostrare!'. ' - '. $id_simulazione);}
}

//************************************************************************************
//************************** GESTIONE DATI PRIMA TABLE *******************************
//************************************************************************************

	//SE CI SONO RECORD, RILEVO IL NUMERO MASSIMO DI ANNI -------------------------
	$sql = "SELECT * FROM tb_tasso_acc_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";
		
	if ($result=mysqli_query($db,$sql)){
		if ($result->num_rows > 0) {
			
			//CALCOLO IL MASSIMO NUMERO DI ANNI DI SVILUPPO
			$MaxAnni = ($EtaScad - $Eta) + 1;
			
			//SE IL NUMERO MASSIMO DI ANNI E' MAGGIORE DI ZERO, PROCEDO ALLA GENERAZIONE DEI DATI PER LA TABELLA PDF
			if ($MaxAnni>0){
				//CANCELLO TUTTI I VECCHI RECORDS EVENTUALMENTE PRESENTI
				$query = "DELETE FROM tb_temp_acc WHERE id_simulazione = '". $id_simulazione. "'";
				if ($db->query($query) === TRUE) {
					//echo "Record deleted successfully";
				} else {
					echo "Errore nella cancellazione dei records: " . $conn->error;
				}
				//CREO TUTTI I RECORD NECESSARI E SUCCESSIVAMENTE PROCEDO CON L'UPDATE
				$indice = 1; 
				while ($indice <= $MaxAnni) {	
					$queryINS = "INSERT INTO tb_temp_acc(id_simulazione, id_progressivo, n_eta, ds_importo_1, ds_importo_2, ds_importo_3, ds_importo_4, ds_importo_5, ds_importo_6) ";
					$queryINS = $queryINS. "VALUES ";
					$queryINS = $queryINS. "('". $id_simulazione. "','". $indice. "','". (($Eta-1)+$indice). "','0','0','0','0','0','0')";
		
					if ($db->query($queryINS) === TRUE) {} else {echo "Errore nell inserimento dei records: " . $conn->error. ' - '. $queryINS;}			
					$indice++;
				}	
				//--------------------------------------------------------------------------------------------------------
				
				//AGGIORNO IN MYSQL I DATI PER POI ESTRARLI NEL PDF
				$sql = "SELECT id_simulazione, id_riga, n_anni, ds_importo, n_perc_inter, n_costo_perc ";
				$sql = $sql. "FROM tb_tasso_acc_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";
		
				if ($result=mysqli_query($db,$sql)){
					
				  $NumRigheTab=mysqli_num_rows($result);
					
					if ($result->num_rows > 0) {
						$indicerow = 1;	//Indice per il numero delle righe della table
						while ($obj = $result->fetch_object()) {
							$queryUP = '';
							$indice = 1; //Indice per contatore del ciclo riferito a n_anni
							$Anni = $obj->n_anni;
							$PercInter = $obj->n_perc_inter;
							$TassiTB[$indicerow] = $obj->n_perc_inter;
							$PerCosto = $obj->n_costo_perc;
							$ImportoDB = $obj->ds_importo;
							$moltiplicatore = ((100-$PerCosto)/100);	
							$ImportoPrecedente = 0;
							while ($indice <= $MaxAnni) {	
								if($indice == 1) {
									$CalcImporto=($ImportoDB * $moltiplicatore); //Calcolo importo da inserire 
								} else {
									if ($indice <= $Anni){
										$CalcImporto=($ImportoPrecedente * (1 + ($PercInter / 100))) + ($ImportoDB * $moltiplicatore); //Calcolo importo da inserire
									} else {
										$CalcImporto=($ImportoPrecedente * (1 + ($PercInter / 100))); //Calcolo importo da inserire
									}
								}	
								$ImportoPrecedente = $CalcImporto;
								$queryUP = "UPDATE tb_temp_acc SET ds_importo_". $indicerow. " = '". Round($CalcImporto,2). "' ";
								$queryUP = $queryUP. "WHERE id_simulazione = '". $id_simulazione. "' AND id_progressivo = '". $indice. "'";
								
								if ($db->query($queryUP) === TRUE) {} else {echo "Errore nell aggiornamento dei records: " . $conn->error. ' - '. $queryUP;}							
								$indice++;
							}
							$indicerow++;					
						}						
					}
				}
				$query = "SELECT id_simulazione, id_progressivo, n_eta, ds_importo_1, ds_importo_2, ds_importo_3, ds_importo_4, ds_importo_5, ds_importo_6 ";
				$query .= "FROM tb_temp_acc WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_progressivo ASC";
				if ($result=mysqli_query($db,$query)) {
					/* fetch associative array */
					while($row = $result->fetch_assoc()) {	
						$data[] = $row;
					}
				}				
			} 		
		
		
		} else {$StatoPrimaTable = 'N';}
	}

	
//************************************************************************************
//************************* GESTIONE DATI SECONDA TABLE ******************************
//************************************************************************************
	//SE CI SONO RECORD, PROCEDO -------------------------
	$sql = "SELECT * FROM tb_tasso_dec_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";	
	
	$result = $db->query($sql);	
	
	if ($result->num_rows > 0) {

		//CALCOLO IL MASSIMO NUMERO DI ANNI DI SVILUPPO
		$MaxAnni = ($EtaScad - $Eta) + 1;
		
		//SE IL NUMERO MASSIMO DI ANNI E' MAGGIORE DI ZERO, PROCEDO ALLA GENERAZIONE DEI DATI PER LA TABELLA PDF
		if ($MaxAnni>0){
			//CANCELLO TUTTI I VECCHI RECORDS EVENTUALMENTE PRESENTI
			$query = "DELETE FROM tb_temp_vers WHERE id_simulazione = '". $id_simulazione. "'";
			if ($db->query($query) === TRUE) {
				//echo "Record deleted successfully";
			} else {
				echo "Errore nella cancellazione dei records: " . $conn->error;
			}
			//CREO TUTTI I RECORD NECESSARI E SUCCESSIVAMENTE PROCEDO CON L'UPDATE
			$indice = 1; $Eta = $Eta - 1;
			while ($indice <= $MaxAnni) {	
				$queryINS = "INSERT INTO tb_temp_vers(id_simulazione, id_progressivo, n_eta, ds_importo_1, ds_importo_2, ds_importo_3, ds_importo_4, ds_importo_5, ds_importo_6) ";
				$queryINS = $queryINS. "VALUES ";
				$queryINS = $queryINS. "('". $id_simulazione. "','". $indice. "','". ($Eta+$indice). "','0','0','0','0','0','0')";
	
				if ($db->query($queryINS) === TRUE) {} else {echo "Errore nell inserimento dei records: " . $conn->error. ' - '. $queryINS;}			
				$indice++;
			}	
			//--------------------------------------------------------------------------------------------------------
			
			//AGGIORNO IN MYSQL I DATI PER POI ESTRARLI NEL PDF
			$sql = "SELECT id_simulazione, id_riga, n_anno, ds_importo, n_perc_inter, n_costo_perc ";
			$sql = $sql. "FROM tb_tasso_dec_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";
	
			if ($result=mysqli_query($db,$sql)){
				
			  $NumRigheTabVers=mysqli_num_rows($result);
				
				if ($result->num_rows > 0) {
					$indicerow = 1;	//Indice per il numero delle righe della table
					while ($obj = $result->fetch_object()) {
						$queryUP = '';
						$Anno = $obj->n_anno;					
						$indice = ($Anno-$AnnoDiGenerazione)+1; //Indice per contatore del ciclo riferito a n_anno
						$PercInter = $obj->n_perc_inter;
						$TassiTB2[$indicerow] = $obj->n_perc_inter;
						$PerCosto = $obj->n_costo_perc;
						$ImportoDB = $obj->ds_importo;
						$moltiplicatore = ((100-$PerCosto)/100);	
						$ImportoPrecedente = 0;
						while ($indice <= $MaxAnni) {	
							if($ImportoPrecedente == 0) {
								$CalcImporto=($ImportoDB * $moltiplicatore); //Calcolo importo da inserire 
							} else {
								$CalcImporto=($ImportoPrecedente * (1 + ($PercInter / 100))); //Calcolo importo da inserire
							}	
							$ImportoPrecedente = $CalcImporto;
							$queryUP = "UPDATE tb_temp_vers SET ds_importo_". $indicerow. " = '". Round($CalcImporto,2). "' ";
							$queryUP = $queryUP. "WHERE id_simulazione = '". $id_simulazione. "' AND id_progressivo = '". $indice. "'";
							
							if ($db->query($queryUP) === TRUE) {} else {echo "Errore nell aggiornamento dei records: " . $conn->error. ' - '. $queryUP;}							
							$indice++;
						}
						$indicerow++;					
					}						
				}
			}
			$query = "SELECT id_simulazione, id_progressivo, n_eta, ds_importo_1, ds_importo_2, ds_importo_3, ds_importo_4, ds_importo_5, ds_importo_6 ";
			$query .= "FROM tb_temp_vers WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_progressivo ASC";
			if ($result=mysqli_query($db,$query)) {
				while($row = $result->fetch_assoc()) {	
					$dataVers[] = $row;
				}
			}			
		}
	
	} else {$StatoSecondaTable = 'N';}

//************************************************************************************
//************************** GESTIONE DATI TERZA TABLE *******************************
//************************************************************************************

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


	//CANCELLO TUTTI I VECCHI RECORDS EVENTUALMENTE PRESENTI
	$query = "DELETE FROM tb_temp_decum WHERE id_simulazione = '". $id_simulazione. "'";
	if ($db->query($query) === TRUE) {
		
		if ($ImportoDecum <> 0) { //CONTINUO SOLO SE LA VARIABILE RENDIM HA UN IMPORTO
			//DEVO DINAMICAMENTE GENERARE LE RIGHE PER RIEMPIRE LA TABELLA
			$Progressivo = 1;
			$moltiplicatore = (1+($NPercInter/100));
			$ImportoDec = $ImportoDecum;
			$ImportoPrec = 0;
			
			while ($Progressivo <= 100) {	
				if ($Progressivo == 1) {
					$Rendim = ($TotaleFinale * $moltiplicatore); $ImportoPrec = $Rendim;
					$queryINS = "INSERT INTO tb_temp_decum(id_simulazione, id_progressivo, n_eta, ds_capitale, ds_rendimento, ds_decumulo ) ";
					$queryINS .= "VALUES ";
					$queryINS .= "('". $id_simulazione. "','". $Progressivo. "','". (($Eta + $MaxAnni)+$Progressivo). "','". Round($TotaleFinale,2). "','". Round($Rendim,2). "','". Round($ImportoDec,2). "')";
			
					if ($db->query($queryINS) === TRUE) {} else {echo "Errore nell aggiornamento dei records: " . $conn->error;}	
				} else {			
					$Rendim = (($ImportoPrec * $moltiplicatore)-$ImportoDec); $ImportoPrec = $Rendim;
					$queryINS = "INSERT INTO tb_temp_decum(id_simulazione, id_progressivo, n_eta, ds_capitale, ds_rendimento, ds_decumulo ) ";
					$queryINS .= "VALUES ";
					$queryINS .= "('". $id_simulazione. "','". $Progressivo. "','". (($Eta + $MaxAnni)+$Progressivo). "','0','". Round($Rendim,2). "','". Round($ImportoDec,2). "')";
			
					if ($db->query($queryINS) === TRUE) {} else {echo "Errore nell inserimento dei records: " . $conn->error. ' - '. $queryINS;}	
				}
				
				if ($Rendim < 0) {break;}; //FERMO IL CICLO WHILE APPENA IL VALORE DEL RENDIMENTO SCENDE SOTTO LO ZERO
				$Progressivo++;
			}			
	
			$query = "SELECT id_simulazione, id_progressivo, n_eta, ds_capitale, ds_rendimento, ds_decumulo ";
			$query .= "FROM tb_temp_decum WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_progressivo ASC";
			if ($result=mysqli_query($db,$query)) {
				$NumRigheTabDec=mysqli_num_rows($result);
				while($row = $result->fetch_assoc()) {	
					$dataDec[] = $row;
				}
			}
		
		} else {$NumRigheTabDec = 0;}		

	} else {
		echo "Errore nella cancellazione dei records: " . $conn->error;
	}
	



//CHIUDO LA CONNESSIONE	
$db->close();

?>