<?php

require ('../mysql/db_define_mysqli.php');
require('../fpdf.php');
// INCLUDE THE PHPMAiler LIBRARIES
include('../php_mailer/class.phpmailer.php');

//************************************************************************************
//*************************** GESTIONE DATI DI TESTATA *******************************
//************************************************************************************

$id_simulazione = mysqli_real_escape_string($db, $_POST["id_simulazione"]);
$email = mysqli_real_escape_string($db, $_POST["email"]);
$AnnoDiGenerazione = date("Y");
$AltRiga = 4;
$SizeFont = 10;

$data = array();
$dataVers = array();
$dataDec = array();
$dataPA = array();
$dataPU = array();
$TassiTB = array();
$TassiTB2 = array();

$TotOffsetPA = 0;
$TotOffsetPU = 0;

$StatoPrimaTable = 'S';
$StatoSecondaTable = 'S';

$EtaIns = 0;

$sql = "SELECT id_simulazione, ds_nome, ds_cognome, n_eta, n_eta_scad, n_perc_decum, n_inter_decum, ds_importo_decum ";
$sql = $sql. "FROM tb_dati_t WHERE id_simulazione = '". $id_simulazione. "'";

if ($result=mysqli_query($db,$sql)){
	
	while ($obj = $result->fetch_object()) {
		$Cognome=$obj->ds_cognome;	$Nome=$obj->ds_nome;	$Eta=$obj->n_eta;	$EtaScad=$obj->n_eta_scad;
		$NPercDecum=$obj->n_perc_decum;	$NPercInter=$obj->n_inter_decum;	$ImportoDecum=$obj->ds_importo_decum;
	}
	
	if (empty($Cognome) || $Cognome == '') {echo Exit('Non ci sono dati da mostrare!');}
}

//************************************************************************************
//************************** GESTIONE DATI PRIMA TABLE *******************************
//************************************************************************************

	//DEVO REPERIRE LE RIGHE INSERITE PER I PA E I PU
	
	//CARICO I PA
	$sql = "SELECT * FROM tb_tasso_acc_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";
	
	if ($result=mysqli_query($db,$sql)){
		/* fetch associative array */
		while($row = $result->fetch_assoc()) {	
			$dataPA[] = $row;
		}
	}	
	
	//CARICO I PU
	$sql = "SELECT * FROM tb_tasso_dec_r WHERE id_simulazione = '". $id_simulazione. "' ORDER BY id_riga ASC";
	
	if ($result=mysqli_query($db,$sql)){
		/* fetch associative array */
		while($row = $result->fetch_assoc()) {	
			$dataPU[] = $row;
		}
	}

//************************************************************************************
//************************** GESTIONE DATI SECONDA TABLE *****************************
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
//************************* GESTIONE DATI TERZA TABLE ********************************
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
			$indice = 1; $EtaIns = $Eta - 1;
			while ($indice <= $MaxAnni) {	
				$queryINS = "INSERT INTO tb_temp_vers(id_simulazione, id_progressivo, n_eta, ds_importo_1, ds_importo_2, ds_importo_3, ds_importo_4, ds_importo_5, ds_importo_6) ";
				$queryINS = $queryINS. "VALUES ";
				$queryINS = $queryINS. "('". $id_simulazione. "','". $indice. "','". ($EtaIns+$indice). "','0','0','0','0','0','0')";
	
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
//************************** GESTIONE DATI QUARTA TABLE ******************************
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

//*****************************************************************************************************************************************************
//************************************************************* FUNZIONI E PROCEDURE ******************************************************************
//*****************************************************************************************************************************************************

class PDF extends FPDF
{	

//************************************************************************************
//*************************** HEADER E FOOTER STANDARD *******************************
//************************************************************************************
	// Page header
	function Header()
	{
		//Inserisco il testo solo per la prima pagina
		if ($this->PageNo() == 1) {	
			// Logo Generali
			$this->Image('../img/gen.png',10,8,60);
			// Logo Centrofriuli
			$this->Image('../img/cf.png',135,8,60);
			// Line break
			$this->Ln(22);
			
			$str='"Nessun vento è favorevole per chi non sa dove andare"

Per poter vivere di rendita abbiamo bisogno di costruire un piano che nel tempo ci porti dove vogliamo andare. Questo studio ci fornisce una cornice puramente teorica che, se poi realizzata mediante la sottoscrizione di contratti che rispettino i vincoli teorici, ci permette di raggiungere gli stessi risultati. Bisogna considerare che lo studio può essere utilizzato per poter inglobare sia le scelte fatte finora che quelle future.
Quando si "gioca" con il futuro si debbono necessariamente fare delle ipotesi di andamento e queste decidono i risultati e anche il fabbisogno di investimento per raggiungere l’obiettivo; Quindi le ipotesi adottate debbono essere in equilibrio tra il bisogno di sicurezza (eccessivamente restrittive) e la realtà pluriennale (rendimenti storici); ognuno è comunque libero di variare le ipotesi a piacimento capendo come cambierebbe la propria vita futura!

Ora costruite il vostro futuro migliore!';		
			//Decodifico la stringa in modo che i caratteri vengano interpretati nel modo corretto	
			$value = iconv('UTF-8', 'CP1252//TRANSLIT', $str);
			$this->SetFont('CORSIVO','',14);
			$this->MultiCell(0, 6,$value,0,'C',false);
			// Arial bold 15
			$this->SetFont('GOTHIC','B',22);
			// Move to the right
			$this->SetY(32);
			// Title
			$this->Cell(190,170,'"VIVERE DI RENDITA"',0,0,'C');
			// Line break
			$this->Ln(95);		
		} else {
			// Logo Generali
			$this->Image('../img/gen.png',10,8,60);
			// Logo Centrofriuli
			$this->Image('../img/cf.png',135,8,60);
			// Arial bold 15
			$this->SetFont('GOTHIC','B',22);
			// Move to the right
			$this->SetY(32);
			// Title
			$this->Cell(190,12,'"VIVERE DI RENDITA"',0,0,'C');
			// Line break
			$this->Ln(16);			
		}
	}

	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Linea
		$this->SetDrawColor(193, 30, 13);
		$this->Line( 10,283,200,283);
		// Arial italic 8
		$this->SetFont('GOTHIC','I',8);
		$this->Cell(40,10,'Agenzia Generale Codroipo Giardini - Via C. Battisti n. 5 - 33033 Codroipo (UD)');
		// Page number
		$this->Cell(285,10,'Pagina '.$this->PageNo().' di {nb}',0,0,'C');
	}	

//************************************************************************************
//**************************** TABELLA PRIMA PAGINA **********************************
//************************************************************************************
	
	// Colored table
	function CompilaTablePA($dataPA)
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont; global $TotOffsetPA;
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255); //BIANCO
		$this->SetLineWidth(.2);
		$this->SetFont('GOTHIC','B',$SizeFont);
	
		// Header
		$w = array(20, 42, 24, 24); $header = array('N. ANNI','IMPORTO','INTER %','COSTO %');  
		
		$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
		
		for($i=0;$i<count($header);$i++){
			$this->Cell($w[$i],$AltRiga,$header[$i],1,0,'C',true);
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
		
		// Data
		$r=0;
		$fill = false;
		foreach($dataPA as $row)
		{						
			$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
			$this->Cell(20,$AltRiga,$row['n_anni'],'LR',0,'C',$fill);
			$this->Cell(42,$AltRiga,number_format($row['ds_importo'], 2, ',', '.'),'LR',0,'C',$fill);
			$this->Cell(24,$AltRiga,number_format($row['n_perc_inter'], 2, ',', '.'),'LR',0,'C',$fill);
			$this->Cell(24,$AltRiga,number_format($row['n_costo_perc'], 2, ',', '.'),'LR',0,'C',$fill);
			
			$this->Ln();
			$fill = !$fill;
			$r=$r+1;
		}
		
		// Calcolo spazio righe
		$TotOffsetPA = ($r * $AltRiga);
		// Closing line
		$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
		$this->Cell(110,0,'','T');		
	}

	// Colored table
	function CompilaTablePU($dataPU)
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont; global $TotOffsetPU;
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255); //BIANCO
		$this->SetLineWidth(.2);
		$this->SetFont('GOTHIC','B',$SizeFont);
	
		// Header
		$w = array(20, 42, 24, 24); $header = array('ANNO','IMPORTO','INTER %','COSTO %');  
		
		$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
		
		for($i=0;$i<count($header);$i++){
			$this->Cell($w[$i],$AltRiga,$header[$i],1,0,'C',true);
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
		
		// Data
		$r=0;
		$fill = false;
		foreach($dataPU as $row)
		{						
			$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
			$this->Cell(20,$AltRiga,$row['n_anno'],'LR',0,'C',$fill);
			$this->Cell(42,$AltRiga,number_format($row['ds_importo'], 2, ',', '.'),'LR',0,'C',$fill);
			$this->Cell(24,$AltRiga,number_format($row['n_perc_inter'], 2, ',', '.'),'LR',0,'C',$fill);
			$this->Cell(24,$AltRiga,number_format($row['n_costo_perc'], 2, ',', '.'),'LR',0,'C',$fill);
			
			$this->Ln();
			$fill = !$fill;
			$r=$r+1;
		}
		
		// Calcolo spazio righe
		$TotOffsetPU = ($r * $AltRiga);
		// Closing line
		$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
		$this->Cell(110,0,'','T');
		
	}
	
	// Colored table
	function CompilaTableTotali()
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont;
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255); //BIANCO
		$this->SetLineWidth(.2);
		$this->SetFont('GOTHIC','B',$SizeFont);
	
		// Header
		$w = array(45, 45); $header = array('PA','PU');  
		
		$this->Cell(30,$AltRiga,'',0,0,'C',false); //SPAZIO PER CENTRARE
		
		for($i=0;$i<count($header);$i++){
			$this->Cell($w[$i],$AltRiga,$header[$i],1,0,'C',true);
			$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','B',$SizeFont);
		
		global $TotalePA;global $TotalePU;
		$this->Cell(30,$AltRiga,'',0,0,'C',false); //SPAZIO
		$this->Cell(45,$AltRiga,number_format($TotalePA, 2, ',', '.'),'LRB',0,'C');
		$this->Cell(40,$AltRiga,'',0,0,'C',false); //SPAZIO
		$this->Cell(45,$AltRiga,number_format($TotalePU, 2, ',', '.'),'LRB',0,'C');
		
		$this->Ln(15);
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255); //BIANCO
		$this->SetLineWidth(.2);
		$this->SetFont('GOTHIC','B',$SizeFont);
		
		$this->Cell(60,$AltRiga,'',0,0,'C',false); //SPAZIO
		$this->Cell(70,$AltRiga,'TOTALE COMPLESSIVO',1,0,'C',true);
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','B',$SizeFont);
		
		global $TotaleFinale;
		$this->Cell(60,$AltRiga,'',0,0,'C',false); //SPAZIO
		$this->Cell(70,$AltRiga,number_format($TotaleFinale, 2, ',', '.'),'LRB',0,'C');
		
		$this->Ln(5);
	
	}
	
	// Colored table
	function CompilaTableRendita()
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont;
	
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','B',$SizeFont);
		
		global $ImportoDecum;
		$this->Cell(60,$AltRiga,'',0,0,'C',false); //SPAZIO
		$this->Cell(70,$AltRiga,number_format($ImportoDecum, 2, ',', '.'),1,0,'C');
		$this->Ln();
		
		$this->Ln(5);
	
	}

//************************************************************************************
//**************************** TABELLA SECONDA PAGINA ********************************
//************************************************************************************
	
	// Colored table
	function CompilaTable($NumRigheTab,$data)
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont;
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255);
		$this->SetLineWidth(.3);
		$this->SetFont('GOTHIC','B',$SizeFont);
	
		// Header
		if ($NumRigheTab==1) {$w = array(15, 15, 26);$header = array('ETA', 'PROG.', 'PIANO 1');} 
		if ($NumRigheTab==2) {$w = array(15, 15, 26, 26);$header = array('ETA', 'PROG.', 'PIANO 1', 'PIANO 2');} 
		if ($NumRigheTab==3) {$w = array(15, 15, 26, 26, 26);$header = array('ETA', 'PROG.', 'PIANO 1', 'PIANO 2', 'PIANO 3');} 
		if ($NumRigheTab==4) {$w = array(15, 15, 26, 26, 26, 26);$header = array('ETA', 'PROG.', 'PIANO 1', 'PIANO 2', 'PIANO 3', 'PIANO 4');} 
		if ($NumRigheTab==5) {$w = array(15, 15, 26, 26, 26, 26, 26);$header = array('ETA', 'PROG.', 'PIANO 1', 'PIANO 2', 'PIANO 3', 'PIANO 4', 'PIANO 5');} 
		if ($NumRigheTab==6) {$w = array(15, 15, 26, 26, 26, 26, 26, 26);$header = array('ETA', 'PROG.', 'PIANO 1', 'PIANO 2', 'PIANO 3', 'PIANO 4', 'PIANO 5', 'PIANO 6');}

		for($i=0;$i<count($header);$i++){
			$this->Cell($w[$i],$AltRiga,$header[$i],1,0,'C',true);
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
		
		// Data
		$r=0;
		$fill = false;
		foreach($data as $row)
		{			
			$this->Cell($w[0],$AltRiga,$row['n_eta'],'LR',0,'C',$fill);
			$this->Cell($w[1],$AltRiga,$row['id_progressivo'],'LR',0,'C',$fill);
			if ($row['ds_importo_1'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_1'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}
			
			if ($NumRigheTab >= 2){if ($row['ds_importo_2'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_2'], 2, ',','.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTab >= 3){if ($row['ds_importo_3'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_3'], 2, ',','.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTab >= 4){if ($row['ds_importo_4'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_4'], 2, ',','.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTab >= 5){if ($row['ds_importo_5'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_5'], 2, ',','.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTab >= 6){if ($row['ds_importo_6'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_6'], 2, ',','.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			
			$this->Ln();
			$fill = !$fill;
			$r=$r+1;
		}
		// Closing line
		$this->Cell(array_sum($w),0,'','T');
		// Gestione totale
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255);
		$this->SetFont('GOTHIC','B',$SizeFont);
		
		$this->Ln(1);
			$this->Cell(50,$AltRiga,'TOTALE COMPLESSIVO: ',1,'LTB','L',true);
			global $TotalePA;
			$this->Cell(32,$AltRiga,number_format($TotalePA, 2, ',','.'),1,'LTB','R',true);
		//Ripristino il colore per il footee e l'header	
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
		
	}

//************************************************************************************
//**************************** TABELLA TERZA PAGINA ********************************
//************************************************************************************

	// Colored table
	function CompilaTableVers($NumRigheTabVers,$dataVers)
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont;
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255);
		$this->SetLineWidth(.3);
		$this->SetFont('GOTHIC','B',$SizeFont);
				
		// Header
		if ($NumRigheTabVers==1) {$w = array(15, 15, 26);$header = array('ETA', 'PROG.', 'P. UNICO 1');} 
		if ($NumRigheTabVers==2) {$w = array(15, 15, 26, 26);$header = array('ETA', 'PROG.', 'P. UNICO 1', 'P. UNICO 2');} 
		if ($NumRigheTabVers==3) {$w = array(15, 15, 26, 26, 26);$header = array('ETA', 'PROG.', 'P. UNICO 1', 'P. UNICO 2', 'P. UNICO 3');} 
		if ($NumRigheTabVers==4) {$w = array(15, 15, 26, 26, 26, 26);$header = array('ETA', 'PROG.', 'P. UNICO 1', 'P. UNICO 2', 'P. UNICO 3', 'P. UNICO 4');} 
		if ($NumRigheTabVers==5) {$w = array(15, 15, 26, 26, 26, 26, 26);$header = array('ETA', 'PROG.', 'P. UNICO 1', 'P. UNICO 2', 'P. UNICO 3', 'P. UNICO 4', 'P. UNICO 5');} 
		if ($NumRigheTabVers==6) {$w = array(15, 15, 26, 26, 26, 26, 26, 26);$header = array('ETA', 'PROG.', 'PIANO 1', 'P. UNICO 2', 'P. UNICO 3', 'P. UNICO 4', 'P. UNICO 5', 'P. UNICO 6');}

		for($i=0;$i<count($header);$i++){
			$this->Cell($w[$i],5,$header[$i],1,0,'C',true);
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
		
		// Data
		$r=0;
		$fill = false;
		foreach($dataVers as $row)
		{			
			$this->Cell($w[0],$AltRiga,$row['n_eta'],'LR',0,'C',$fill);
			$this->Cell($w[1],$AltRiga,$row['id_progressivo'],'LR',0,'C',$fill);
			if ($row['ds_importo_1'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_1'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}
			
			if ($NumRigheTabVers >= 2){if ($row['ds_importo_2'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_2'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTabVers >= 3){if ($row['ds_importo_3'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_3'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTabVers >= 4){if ($row['ds_importo_4'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_4'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTabVers >= 5){if ($row['ds_importo_5'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_5'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			if ($NumRigheTabVers >= 6){if ($row['ds_importo_6'] <> 0){$this->Cell($w[2],$AltRiga,number_format($row['ds_importo_6'], 2, ',', '.'),'LR',0,'R',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'R',$fill);}}
			
			$this->Ln();
			$fill = !$fill;
			$r=$r+1;
		}
		// Closing line
		$this->Cell(array_sum($w),0,'','T');
		// Gestione totale
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255);
		$this->SetFont('GOTHIC','B',$SizeFont);
		
		$this->Ln(1);
			$this->Cell(50,$AltRiga,'TOTALE COMPLESSIVO: ',1,'LTB','L',true);
			global $TotalePU;
			$this->Cell(32,$AltRiga,number_format($TotalePU, 2, ',','.'),1,'LTB','R',true);
		//Ripristino il colore per il footee e l'header	
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
	}

//************************************************************************************
//***************************** TABELLA QUARTA PAGINA *********************************
//************************************************************************************

	// Colored table
	function CompilaTableDec($NumRigheTabDec,$dataDec)
	{
		//Recupero la variabile per l'altezza riga
		global $AltRiga; global $SizeFont;
		
		// Colors, line width and bold font
		$this->SetFillColor(193,30,13);
		$this->SetTextColor(255);
		$this->SetLineWidth(.3);
		$this->SetFont('GOTHIC','B',$SizeFont);
	
		// Header
		$w = array(24, 22, 48, 48, 48);$header = array('ETA', 'PROG.', 'CAPITALE', 'RENDIMENTO', 'DECUMULO');

		for($i=0;$i<count($header);$i++)
			$this->Cell($w[$i],$AltRiga,$header[$i],1,0,'C',true);
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('GOTHIC','',$SizeFont);
		
		// Data
		$r=0;
		$fill = false;
		foreach($dataDec as $row)
		{					
			if ($row['ds_rendimento'] < 0){$fill = true;$this->SetFillColor(255,0,0);$this->SetFont('GOTHIC','B',$SizeFont);}; //EVIDENZIO LA RIGA QUANDO IL VALORE E' NEGATIVO
		
			$this->Cell($w[0],$AltRiga,$row['n_eta']-1,'LR',0,'C',$fill);
			$this->Cell($w[1],$AltRiga,$row['id_progressivo'],'LR',0,'C',$fill);
			if ($row['ds_capitale'] <> 0) {$this->Cell($w[2],$AltRiga,number_format($row['ds_capitale'], 2, ',', '.'),'LR',0,'C',$fill);} else {$this->Cell($w[2],$AltRiga,'','LR',0,'C',$fill);};
			$this->Cell($w[2],$AltRiga,number_format($row['ds_rendimento'], 2, ',', '.'),'LR',0,'C',$fill);
			$this->Cell($w[2],$AltRiga,number_format($row['ds_decumulo'], 2, ',', '.'),'LR',0,'C',$fill);

			$this->Ln();
			$fill = !$fill;
			$r=$r+1;
		}
		// Closing line
		$this->Cell(array_sum($w),0,'','T');
	}
	
}


//*****************************************************************************************************************************************************
//************************************************* PROCEDURA SETTING CLASSE E GESTIONE PAGINE ********************************************************
//*****************************************************************************************************************************************************

// iSTANZIO LA CLASSE
$pdf = new PDF();

//AGGIUNTA NUOVI FONTS
$pdf->AddFont('GOTHIC','','GOTHIC.php');
$pdf->AddFont('GOTHIC','B','GOTHICB.php');
$pdf->AddFont('GOTHIC','I','GOTHICI.php');

$pdf->AddFont('CORSIVO','','SignPainter-HouseScript.php');

$pdf->AliasNbPages();

//-------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------- PRIMA PAGINA --------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------
if ($StatoPrimaTable == 'S'){
	$pdf->AddPage();
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->Write(0,'STUDIO SU: '. $id_simulazione. '                                       ETA: '. ($Eta). '     ETA SCAD. ACCUM.: '. $EtaScad);
	$pdf->Ln(6);
	
	// TITOLO		
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->Cell(190,0,'RIEPILOGO DATI INSERITI',0,0,'C');
	$pdf->Ln(8);	
	
	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.3);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(0,$AltRiga,'PREMIO ANNUO (PA)',1,0,'C',true);
	$pdf->SetTextColor(0); //NERO	
						
	$pdf->Ln(8);
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTablePA($dataPA);
	$pdf->Ln(8);
	
	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(0,$AltRiga,'PREMIO UNICO (PU)',1,0,'C',true);
	$pdf->SetTextColor(0); //NERO	
	$pdf->Ln(8);	
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTablePU($dataPU);
	$pdf->Ln(10);
	
	// Creo il rettangolo per PA e PU
	$pdf->SetDrawColor(27,74,247); //BLU	
	$pdf->SetLineWidth(.5);
		$pdf->Rect(8, 139, 194, 40+($TotOffsetPA+$TotOffsetPU), 'D');	
	$pdf->SetDrawColor(0,0,0); //NERO	
	
	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(0,$AltRiga,'TOTALI',1,0,'C',true);
	$pdf->SetTextColor(0); //NERO	
	$pdf->Ln(8);	
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTableTotali();
	$pdf->Ln(10);
	
	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(0,$AltRiga,'RENDITA',1,0,'C',true);
	$pdf->SetTextColor(0); //NERO	
	$pdf->Ln(8);
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTableRendita();
	$pdf->Ln(10);
	
	// Creo il rettangolo per i totali
	$pdf->SetDrawColor(0,232,22); //VERDE	
	$pdf->SetLineWidth(.5);
		$pdf->Rect(8, 181+($TotOffsetPA+$TotOffsetPU), 194, 64, 'D');
	$pdf->SetDrawColor(0,0,0); //NERO	
	
}

//-------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------- SECONDA PAGINA ------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------
if ($StatoPrimaTable == 'S'){
	$pdf->AddPage();
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->Write(0,'STUDIO SU: '. $id_simulazione. '                                       ETA: '. ($Eta). '     ETA SCAD. ACCUM.: '. $EtaScad);
	$pdf->Ln(6);
	
	// TITOLO		
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->Cell(190,0,'FASE DI ACCUMULO',0,0,'C');
	$pdf->Ln(5);

	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(0,$AltRiga,'SVILUPPO (PA)',1,0,'C',true);
	$pdf->SetTextColor(0); //NERO	
	$pdf->Ln(5);
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
	// Colors, line width and bold font
	$pdf->SetFillColor(193,30,13);
	$pdf->SetTextColor(255);
	$pdf->SetLineWidth(.3);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(30,$AltRiga,'% INTER. -->',1,0,'R',true);	
	$pdf->SetTextColor(246, 143, 15);	
		if (count($TassiTB) >= 1){$pdf->Cell(26, 6,number_format($TassiTB[1], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB) >= 2){$pdf->Cell(26, 6,number_format($TassiTB[2], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB) >= 3){$pdf->Cell(26, 6,number_format($TassiTB[3], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB) >= 4){$pdf->Cell(26, 6,number_format($TassiTB[4], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB) >= 5){$pdf->Cell(26, 6,number_format($TassiTB[5], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB) >= 6){$pdf->Cell(26, 6,number_format($TassiTB[6], 2, ',', '.'). '%',0,0,'C',false);}
	$pdf->Ln(5);
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTable($NumRigheTab,$data);
	$pdf->Ln(5);
}
//-------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------- TERZA PAGINA (tolta) --------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------
if ($StatoSecondaTable == 'S'){
	
	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(0,$AltRiga,'SVILUPPO (PU)',1,0,'C',true);
	$pdf->SetTextColor(0); //NERO	
	$pdf->Ln(5);
	
	// Colors, line width and bold font
	$pdf->SetFillColor(193,30,13);
	$pdf->SetTextColor(255);
	$pdf->SetLineWidth(.3);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
		$pdf->Cell(30,$AltRiga,'% INTER. -->',1,0,'R',true);	
	$pdf->SetTextColor(246, 143, 15);	
		if (count($TassiTB2) >= 1){$pdf->Cell(26, 6,number_format($TassiTB2[1], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB2) >= 2){$pdf->Cell(26, 6,number_format($TassiTB2[2], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB2) >= 3){$pdf->Cell(26, 6,number_format($TassiTB2[3], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB2) >= 4){$pdf->Cell(26, 6,number_format($TassiTB2[4], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB2) >= 5){$pdf->Cell(26, 6,number_format($TassiTB2[5], 2, ',', '.'). '%',0,0,'C',false);}
		if (count($TassiTB2) >= 6){$pdf->Cell(26, 6,number_format($TassiTB2[6], 2, ',', '.'). '%',0,0,'C',false);}
	$pdf->Ln(5);
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTableVers($NumRigheTabVers,$dataVers);
	$pdf->Ln(10);
}
//-------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------- QUARTA PAGINA --------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------
if ($StatoPrimaTable == 'S' || $StatoSecondaTable == 'S'){
	$pdf->AddPage();
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->Write(0,'STUDIO SU: '. $id_simulazione);
	$pdf->Ln(6);
	
	// TITOLO		
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->Cell(190,0,'FASE DI DECUMULO',0,0,'C');
	$pdf->Ln(10);
	
	// Colors, line width and bold font
	$pdf->SetFillColor(246,143,15);
	$pdf->SetTextColor(255); //BIANCO
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('GOTHIC','B',$SizeFont);
	
	$pdf->Cell(8,$AltRiga,'',0,0,'C',false); //SPAZIO
		$pdf->Cell(34,$AltRiga,'% INTER. DEC.',1,0,'R',true);
	$pdf->SetTextColor(0); //NERO
		$pdf->Cell(16,$AltRiga,number_format($NPercInter, 2, ',', '.'),1,0,'C',false);	
	$pdf->Cell(12,$AltRiga,'',0,0,'C',false); //SPAZIO
	$pdf->SetTextColor(255); //BIANCO
		$pdf->Cell(25,$AltRiga,'% DECUM.',1,0,'R',true);	
	$pdf->SetTextColor(0); //NERO	
		$pdf->Cell(16,$AltRiga,number_format($NPercDecum, 2, ',', '.'),1,0,'C',false);	
	$pdf->SetTextColor(255); //BIANCO
		$pdf->Cell(36,$AltRiga,'IMPORTO DEC.',1,0,'R',true);	
	$pdf->SetTextColor(0); //NERO	
		$pdf->Cell(35,$AltRiga,number_format($ImportoDecum, 2, ',', '.'),1,0,'C',false);
						
	$pdf->Ln(15);
	
	$pdf->SetFont('GOTHIC','B',$SizeFont);
		$pdf->CompilaTableDec($NumRigheTabDec,$dataDec);
	$pdf->Ln(10);
}
//-------------------------------------------------------------------------------------------------------------------------
 
//*********************************************************************************************
//***************************** CONFIGURAZIONE INVIO AUTO EMAIL *******************************
//*********************************************************************************************

$doc = $pdf->Output('', 'S');

$oggetto = "Invio email automatico simulazione 'VIVERE DI RENDITA' doc. num. ". $id_simulazione;
$messaggio = 'Gentile cliente, Le inviamo la sua simulazione personalizzata.';

$mail = new PHPMailer();
$mail->From     = "marketingcentrofriuli@gmail.com";
$mail->FromName = "Marketing Centrofriuli";
$mail->AddAddress($email);
$mail->IsHTML(true); 
$mail->Subject  =  $oggetto;
$mail->Body     =  $messaggio;
$mail->AltBody  =  "";
$mail->AddStringAttachment($doc, $id_simulazione. '-'. $Cognome. '-'. $Nome. '.pdf', 'base64', 'application/pdf');

if(!$mail->Send()){
    echo "ERRORE nell'invio della mail.";
}else{
    echo "SUCCESSO! l'email è stata inviata!";
}

?>