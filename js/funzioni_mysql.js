// JavaScript Document

$(document).ready(function() {

	//Disabilito i pulsanti
	DisabilitaPulsanti();

	//Associo l'evento blur agli input box
	AttivaEventiInput();

	//al click sul bottone del form
	$("#salva").click(function(){

		TestSalvaElabora();
		document.getElementById("salva").disabled = true;

	});

	//al click sul bottone del form
	$("#anteprima").click(function(){

		//APRO LA FINESTRA DI GENERAZIONE PDF
		window.open("php_pdf/pdf_pa_result.php?id_simulazione='" + $("#id_simulazione").val() + "'",'_blank');

	});

	//al click sul bottone del form invio automaticamente l'email al cliente
	$("#sviluppa").click(function(){

		GeneraPDF_InviaEmail();

	});

});


// ******************************************************************* FUNZIONI ******************************************************************


function VaiAPagina(Pagina) {
	window.location.href=Pagina;
}

function soloNumeri(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {	//Punto autorizzato
		return false;
	}
	return true;
}

function soloValuta(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 45 || charCode > 57)) {	//Punto autorizzato
		return false;
	}
	return true;
}

function soloLettereMaiuscENumeri(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if ((charCode > 31 && charCode < 48) || (charCode > 57 && charCode < 65) || (charCode > 90 && charCode < 127)) {	//Punto autorizzato
		return false;
	}
	return true;
}
function soloPercentuali(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 46 || charCode > 57 || charCode == 47)) {	//Punto autorizzato
		return false;
	}
	return true;
}

function DisabilitaPulsanti(){

	document.getElementById("salva").disabled = true;
	document.getElementById("anteprima").disabled = true;
	document.getElementById("sviluppa").disabled = true;

}

function AttivaEventiInput(){

	$(document).on ("blur", "#nome", validate);
	$(document).on ("blur", "#cognome", validate);
	$(document).on ("blur", "#email", validate);
	$(document).on ("blur", "#eta", validate);
	$(document).on ("blur", "#percdecum", validate);
	$(document).on ("blur", "#percinterdecum", validate);
	$(document).on ("blur", "#etascadenza", validate);
	$(document).on ("blur", "#importodecum", validate);

}

function GeneraPDF_InviaEmail(){

	var email = $("#email").val();

	if ((email == "") || (email == "undefined")) {alert("Non esiste alcuna email da utilizzare per inviare il documento!");return true;}

	if (confirm('Stai per inviare un email automatica a ' + email + '. Sei sicuro di volere procedere?' )) {

		//INVIO AUTOMATICAMENTE L'EMAIL
		//associo variabili
		var id_simulazione = $("#id_simulazione").val();
		var email = $("#email").val();

		//chiamata ajax
		$.ajax({

			//imposto il tipo di invio dati
			type: "POST",

			//Invio i dati alla pagina php
			url: "php_pdf/pdf_send_mail.php",

			//Dati da salvare
			data: "id_simulazione=" + id_simulazione + "&email=" + email,
			dataType: "html",

			//visualizzazione errori/ok
			success: function(msg)
			{
				alert(msg);
			},
			error: function(msg)
			{
				//alert(msg);
			}
		});

	} else {
		alert('Operazione annullata!');
	}

}

function validate() {

	// -----------------------------------------------------------------------------------------------------------------------------------------
	//														VALIDAZIONE CAMPI OBBLIGATORI

	document.getElementById("salva").disabled = true;

	//associo variabili
	var id_simulazione = $("#id_simulazione").val();
	var nome = $("#nome").val();
	var cognome = $("#cognome").val();
	var email = $("#email").val();
	var eta = $("#eta").val();
	var etascadenza = $("#etascadenza").val();
	var percdecum = $("#percdecum").val();
	var percinterdecum = $("#percinterdecum").val();
	var importodecum = $("#importodecum").val();

	//--------------------- TEST CAMPI -----------------------------------

	if ((id_simulazione == "") || (id_simulazione == "undefined")) {
		//alert("Il campo id_simulazione è obbligatorio.");
		//$("#id_simulazione").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((nome == "") || (nome == "undefined")) {
		//alert("Il campo nome è obbligatorio.");
		//$("#nome").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((cognome == "") || (cognome == "undefined")) {
		//alert("Il campo cognome è obbligatorio.");
		//$("#cognome").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((email == "") || (email == "undefined")) {
		//alert("Il campo email è obbligatorio.");
		//$("#email").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((eta == "") || (eta == "undefined")) {
		//alert("Il campo eta è obbligatorio.");
		//$("#eta").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((etascadenza == "") || (etascadenza == "undefined")) {
		//alert("Il campo etascadenza è obbligatorio.");
		//$("#etascadenza").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((percdecum == "") || (percdecum == "undefined")) {
		//alert("Il campo percentuale interesse decumulo è obbligatorio.");
		//$("#percinterdecum").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((percdecum == "") || (percdecum == "undefined")) {
		//alert("Il campo percentuale di decumulo è obbligatorio.");
		//$("#percdecum").focus();
		return false;
	}
	//----------------------------------------------------------------

	if ((importodecum == "") || (importodecum == "undefined")) {
		//alert("Il campo importo decumulo è obbligatorio.");
		//$("#importodecum").focus();
		return false;
	}
	//----------------------------------------------------------------

	document.getElementById("salva").disabled = false;
	document.getElementById("anteprima").disabled = true;
	document.getElementById("sviluppa").disabled = true;

}

// ---------------------------- GESTIONE CALCOLO AUTOMATICO % - IMPORTO -----------------------------

function ElaboraImpPerc(richiesta) {

	var id_simulazione = $("#id_simulazione").val();
	var tiporichiesta = richiesta;
	var percdecum = $("#percdecum").val();
	var importodecum = $("#importodecum").val();

	if (id_simulazione == "" || (id_simulazione.length) != 9){
		return;
	}

	$.ajax({   //------------------------------ REPERISCO L'EVENTUALE TOTALE SE PRESENTE ----------------

		//imposto il tipo di invio dati
		type: "POST",
		//Invio i dati alla pagina php
		url: "php_db/estraiSomma.php",
		//Dati da salvare
		data: "id_simulazione=" + id_simulazione + "&tiporichiesta=" + tiporichiesta + "&percdecum=" + percdecum + "&importodecum=" + importodecum,
		dataType: "html",

		//visualizzazione errori/ok
		success: function(ValSomma)
		{
			if (ValSomma == 0) {//alert('Eseguire un anteprima prima di poter gestire percentuale ed importo di decumulo');
			}

			else

			{
				if (richiesta == 'percentuale'){
					$("input[type=text][id=importodecum]").val(ValSomma);
				}
				if (richiesta == 'importo'){
					$("input[type=text][id=percdecum]").val(ValSomma);
				}
			}
		},
		error: function(ValSomma)
		{
			alert('Errore nel reprrimento dati');
		}
	});

}

// --------------------------- FUNZIONI GESTIONE CARICAMENTO DOC SALVATO ----------------------------

function caricaDoc() {

	var id_nuovo = $("#optionNuovoId").val();
	var id_simulazione = $("#id_simulazione").val();

	if (id_simulazione == "" || (id_simulazione.length) != 9){
		return;
	}
	if (id_simulazione == id_nuovo){
		location.reload();
		return false;
	}

	$.ajax({   //------------------------------ CARICAMENTO AUTOMATICO DATI TESTATA ----------------

		//imposto il tipo di invio dati
		type: "POST",
		//Invio i dati alla pagina php
		url: "php_db/caricaDoc.php",
		//Dati da salvare
		data: "id_simulazione=" + id_simulazione,
		dataType: "html",

		//visualizzazione errori/ok
		success: function(DataRows)
		{
			if (DataRows != "NoData"){
				//Inserisco i dati di testata
				var obj = JSON.parse(DataRows, function (key, value) {

					if (key == "ds_nome") {
						$("input[type=text][id=nome]").val(value);
					}
					if (key == "ds_cognome") {
						$("input[type=text][id=cognome]").val(value);
					}
					if (key == "ds_email") {
						$("input[type=text][id=email]").val(value);
					}
					if (key == "n_eta") {
						$("input[type=text][id=eta]").val(value);
					}
					if (key == "n_eta_scad") {
						$("input[type=text][id=etascadenza]").val(value);
					}
					if (key == "n_perc_decum") {
						$("input[type=text][id=percdecum]").val(Math.round(value*100)/100);
					}
					if (key == "n_inter_decum") {
						$("input[type=text][id=percinterdecum]").val(Math.round(value*100)/100);
					}
					if (key == "ds_importo_decum") {
						$("input[type=text][id=importodecum]").val(Math.round(value*100)/100);
					}

				});
			} else {
				alert("Non esiste alcun dato memorizzato per il codice documento inserito.");
			}

			document.getElementById("anteprima").disabled = false;
			document.getElementById("sviluppa").disabled = false;

		},
		error: function(DataRows)
		{
			alert(DataRows);
		}
	});

	$.ajax({   //------------------------------ CARICAMENTO AUTOMATICO DATI RIGHE TABELLA PA ----------------

		//imposto il tipo di invio dati
		type: "POST",
		//Invio i dati alla pagina php
		url: "php_db/caricaDocRAcc.php",
		//Dati da salvare
		data: "id_simulazione=" + id_simulazione,
		dataType: "html",

		success: function(DataRows)
		{
			if (DataRows != "NoData"){

				//Rimuovo le righe della tabella
				$("#tblDataSx td").remove();

				var listaRighe = eval(DataRows);

				for (i=0; i<listaRighe.length; i++)//scorriamo tutto la lunghezza dell'array
				{
					$('#tblDataSx').append('<tr><td>' + listaRighe[i].n_anni
						+ '</td><td>' + listaRighe[i].ds_importo
						+ '</td><td>' + listaRighe[i].n_perc_inter
						+ '</td><td>' + listaRighe[i].n_costo_perc
						+ '</td><td>' + '<img src="img/delete.png" class="btnDeleteSx"> <img src="img/pencil.png" class="btnEditSx">'
						+ '</td></tr>');

					$(".btnEditSx").bind("click", EditSx);
					$(".btnDeleteSx").bind("click", DeleteSx);
				}

			} else {
				//Rimuovo le righe della tabella
				$("#tblDataSx td").remove();
			}

			//RIMUOVO LE RIGHE VUOTE SE PRESENTI
			$('#tblDataSx').each(function () {
				$(this).find('tr').each(function () {
					if ($(this).text().trim() == "") {
						$(this).closest("tr").remove();
					};
				});
			});
		},
		error: function(DataRows)
		{
			alert(DataRows);
		}
	});

	$.ajax({   //------------------------------ CARICAMENTO AUTOMATICO DATI RIGHE TABELLA PU ----------------

		//imposto il tipo di invio dati
		type: "POST",
		//Invio i dati alla pagina php
		url: "php_db/caricaDocRDec.php",
		//Dati da salvare
		data: "id_simulazione=" + id_simulazione,
		dataType: "html",

		success: function(DataRows)
		{
			if (DataRows != "NoData"){

				//Rimuovo le righe della tabella
				$("#tblDataDx td").remove();

				var listaRighe = eval(DataRows);

				for (i=0; i<listaRighe.length; i++)//scorriamo tutto la lunghezza dell'array
				{
					$('#tblDataDx').append('<tr><td>' + listaRighe[i].n_anno
						+ '</td><td>' + listaRighe[i].ds_importo
						+ '</td><td>' + listaRighe[i].n_perc_inter
						+ '</td><td>' + listaRighe[i].n_costo_perc
						+ '</td><td>' + '<img src="img/delete.png" class="btnDeleteDx"> <img src="img/pencil.png" class="btnEditDx">'
						+ '</td></tr>');

					$(".btnEditDx").bind("click", EditDx);
					$(".btnDeleteDx").bind("click", DeleteDx);
				}

			} else {
				//Rimuovo le righe della tabella
				$("#tblDataDx td").remove();
			}
			//RIMUOVO LE RIGHE VUOTE SE PRESENTI
			$('#tblDataDx').each(function () {
				$(this).find('tr').each(function () {
					if ($(this).text().trim() == "") {
						$(this).closest("tr").remove();
					};
				});
			});
		},
		error: function(DataRows)
		{
			alert(DataRows);
		}

	});

	alert('Dati caricati correttamente');
}


//********************* O ********************* O ********************* O ********************* O ********************* O ********************* O

function TestSalvaElabora(){

	//associo variabili
	var id_simulazione = $("#id_simulazione").val();
	var nome = $("#nome").val();
	var cognome = $("#cognome").val();
	var email = $("#email").val();
	var eta = $("#eta").val();
	var etascadenza = $("#etascadenza").val();
	var percdecum = $("#percdecum").val();
	var percinterdecum = $("#percinterdecum").val();
	var importodecum = $("#importodecum").val();

	//chiamata ajax
	$.ajax({

		//imposto il tipo di invio dati
		type: "POST",

		//Invio i dati alla pagina php
		url: "php_db/salva.php",

		//Dati da salvare
		data: "id_simulazione=" + id_simulazione + "&nome=" + nome + "&cognome=" + cognome + "&email=" + email + "&eta=" + eta + "&etascadenza=" + etascadenza +
			"&percdecum=" + percdecum + "&percinterdecum=" + percinterdecum + "&importodecum=" + importodecum,
		dataType: "html",

		//visualizzazione errori/ok
		success: function(msg)  // ************************** SUCCESS TESTATA *************************
		{
			SalvaTabellaSx();
		},
		error: function(msg)
		{
			alert(msg);
		}
	});

}

function SalvaTestata() { //************************************** SALVATAGGIO DATI TESTATA **********************************************

	//associo variabili
	var id_simulazione = $("#id_simulazione").val();
	var nome = $("#nome").val();
	var cognome = $("#cognome").val();
	var email = $("#email").val();
	var eta = $("#eta").val();
	var etascadenza = $("#etascadenza").val();
	var percdecum = $("#percdecum").val();
	var percinterdecum = $("#percinterdecum").val();
	var importodecum = $("#importodecum").val();

	//chiamata ajax
	$.ajax({

		//imposto il tipo di invio dati
		type: "POST",

		//Invio i dati alla pagina php
		url: "php_db/salva.php",

		//Dati da salvare
		data: "id_simulazione=" + id_simulazione + "&nome=" + nome + "&cognome=" + cognome + "&email=" + email + "&eta=" + eta + "&etascadenza=" + etascadenza +
			"&percdecum=" + percdecum + "&percinterdecum=" + percinterdecum + "&importodecum=" + importodecum,
		dataType: "html",

		//visualizzazione errori/ok
		success: function(msg)
		{
			//alert(msg);
		},
		error: function(msg)
		{
			alert(msg);
		}
	});

}

function SalvaTabellaSx(){ //******************************** SALVATAGGIO DATI RIGHE TABELLA DI SINISTRA ****************************************

	//associo variabili
	var id_simulazione = $("#id_simulazione").val();

	var tableSx = document.getElementById('tblDataSx');
	var righe = tableSx.getElementsByTagName('tr');

	if (righe.length > 1) {

		var RigheTable = new Array();
		RigheTable[0] = new Array();

		//CON J=1 SCARTO LE INTESTAZIONI
		for(var j=1; j<(righe.length); j++){

			RigheTable[j-1] = [];

			RigheTable[j-1][0] = tableSx.id; //tipotbl
			RigheTable[j-1][1] = id_simulazione; //id_simulazione
			RigheTable[j-1][2] = "0000" + j; //Riga
			RigheTable[j-1][3] = righe[j].cells[0].innerHTML;	//anni
			RigheTable[j-1][4] = righe[j].cells[1].innerHTML;	//importo
			RigheTable[j-1][5] = righe[j].cells[2].innerHTML;	//interesse
			RigheTable[j-1][6] = righe[j].cells[3].innerHTML;	//costo

		}

		var st = JSON.stringify(RigheTable);

		//Effettuo la chiamata
		$.ajax({

			//imposto il tipo di invio dati
			type: "POST",

			//Invio i dati alla pagina php
			url: "php_db/salvarow.php",

			//Dati da salvare
			data: "RigheTable="+st,

			//visualizzazione errori/ok
			success: function(msg)
			{
				SalvaTabellaDx();
			},
			error: function(msg)
			{
				alert(msg);
			}
		});

	} else {

		//SE NON CI SONO RIGHE, PER SICUREZZA ELIMINO LE RIGHE SE PRESENTI NEL DATABASE
		//chiamata ajax
		$.ajax({

			//imposto il tipo di invio dati
			type: "POST",

			//Invio i dati alla pagina php
			url: "php_db/eliminarows.php",

			//Dati da salvare
			data: "id_simulazione=" + id_simulazione + "&TbChiamante=" + tableSx.id,
			dataType: "html",

			//visualizzazione errori/ok
			success: function(msg)
			{
				SalvaTabellaDx();
			},
			error: function(msg)
			{
				alert(msg);
			}
		});
	}

}

function SalvaTabellaDx(){ //******************************** SALVATAGGIO DATI RIGHE TABELLA DI DESTRA ****************************************

	//associo variabili
	var id_simulazione = $("#id_simulazione").val();

	var tableDx = document.getElementById('tblDataDx');
	var righe = tableDx.getElementsByTagName('tr');

	if (righe.length > 1) {

		var RigheTable = new Array();
		RigheTable[0] = new Array();

		//CON J=1 SCARTO LE INTESTAZIONI
		for(var j=1; j<(righe.length); j++){

			RigheTable[j-1] = [];

			RigheTable[j-1][0] = tableDx.id; //tipotbl
			RigheTable[j-1][1] = id_simulazione; //id_simulazione
			RigheTable[j-1][2] = "0000" + j; //Riga
			RigheTable[j-1][3] = righe[j].cells[0].innerHTML;	//anno
			RigheTable[j-1][4] = righe[j].cells[1].innerHTML;	//importo
			RigheTable[j-1][5] = righe[j].cells[2].innerHTML;	//costo
			RigheTable[j-1][6] = righe[j].cells[3].innerHTML;	//costo

		}

		var st = JSON.stringify(RigheTable);

		//Effettuo la chiamata
		$.ajax({

			//imposto il tipo di invio dati
			type: "POST",

			//Invio i dati alla pagina php
			url: "php_db/salvarow.php",

			//Dati da salvare
			data: "RigheTable="+st,

			//visualizzazione errori/ok
			success: function(msg)
			{
				AggiornaAccumulo();
			},
			error: function(msg)
			{
				alert(msg);
			}
		});
	} else {

		//SE NON CI SONO RIGHE, PER SICUREZZA ELIMINO LE RIGHE SE PRESENTI NEL DATABASE
		//chiamata ajax
		$.ajax({

			//imposto il tipo di invio dati
			type: "POST",

			//Invio i dati alla pagina php
			url: "php_db/eliminarows.php",

			//Dati da salvare
			data: "id_simulazione=" + id_simulazione + "&TbChiamante=" + tableDx.id,
			dataType: "html",

			//visualizzazione errori/ok
			success: function(msg)
			{
				AggiornaAccumulo();
			},
			error: function(msg)
			{
				alert(msg);
			}
		});
	}

}

function AggiornaAccumulo() {

	//SVILUPPO SILENZIOSAMENTE I RISULTATI
	//associo variabili
	var id_simulazione = $("#id_simulazione").val();
	var importo_acc =  $("#importodecum").val();

	//chiamata ajax
	$.ajax({

		//imposto il tipo di invio dati
		type: "POST",

		//Invio i dati alla pagina php
		url: "php_pdf/sviluppoAuto.php",

		//Dati da salvare
		data: "id_simulazione=" + id_simulazione,
		dataType: "html",

		//visualizzazione errori/ok
		success: function(msg)
		{
			if(importo_acc == 0){
				ElaboraImpPerc('percentuale');
				document.getElementById("anteprima").disabled = false;
				document.getElementById("sviluppa").disabled = false;
			} else {
				document.getElementById("anteprima").disabled = false;
				document.getElementById("sviluppa").disabled = false;
			}
		},
		error: function(msg)
		{
			//alert(msg);
		}
	});

}
