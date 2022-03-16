// JavaScript Document

function AddSx(){
	var nanni = ($("#etascadenza").val() - $("#eta").val())+1;
	var Tassoint = '2.5';
	var Costo = '0';
	var Importo = '0';
    $("#tblDataSx tbody").append(
        "<tr>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloNumeri(event);' value='" + nanni + "'/></td>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloValuta(event);' value='" + Importo + "'/></td>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloPercentuali(event);' value='" + Tassoint + "'/></td>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloPercentuali(event);' value='" + Costo + "'/></td>"+
        "<td><img src='img/save.png' class='btnSaveSx'> <img src='img/delete.png' class='btnDeleteSx'/></td>"+
        "</tr>");
		
        $(".btnSaveSx").bind("click", SaveSx);      
        $(".btnDeleteSx").bind("click", DeleteSx);
	
	document.getElementById("anteprima").disabled = true;
	document.getElementById("sviluppa").disabled = true;
}; 

function SaveSx(){
    var par = $(this).parent().parent(); //tr
    var tdAnni = par.children("td:nth-child(1)");
    var tdImporto = par.children("td:nth-child(2)");
    var tdInter = par.children("td:nth-child(3)");
    var tdCosto = par.children("td:nth-child(4)");
    var tdButtons = par.children("td:nth-child(5)");
 
    tdAnni.html(tdAnni.children("input[type=text]").val());
    tdImporto.html(tdImporto.children("input[type=text]").val());
    tdInter.html(tdInter.children("input[type=text]").val());
    tdCosto.html(tdCosto.children("input[type=text]").val());
    tdButtons.html("<img src='img/delete.png' class='btnDeleteSx'/> <img src='img/pencil.png' class='btnEditSx'/>");
 
    $(".btnEditSx").bind("click", EditSx);
    $(".btnDeleteSx").bind("click", DeleteSx);
		
	document.getElementById("salva").disabled = false;
}; 

function EditSx(){
    var par = $(this).parent().parent(); //tr
    var tdAnni = par.children("td:nth-child(1)");
    var tdImporto = par.children("td:nth-child(2)");
    var tdInter = par.children("td:nth-child(3)");
    var tdCosto = par.children("td:nth-child(4)");
    var tdButtons = par.children("td:nth-child(5)");
 
    tdAnni.html("<input type='text' class='rowvalue' onkeypress='return soloNumeri(event);' id='txtAnni' style='width:70px;' value='"+tdAnni.html()+"'/>");
    tdImporto.html("<input type='text' class='rowvalue' onkeypress='return soloValuta(event);' id='txtImporto' style='width:90px;' value='"+tdImporto.html()+"'/>");
    tdInter.html("<input type='text' class='rowvalue' onkeypress='return soloPercentuali(event);' id='txtInter' style='width:70px;' value='"+tdInter.html()+"'/>");
    tdCosto.html("<input type='text' class='rowvalue' onkeypress='return soloPercentuali(event);' id='txtCosto' style='width:70px;' value='"+tdCosto.html()+"'/>");
    tdButtons.html("<img src='img/save.png' class='btnSaveSx'/>");
 
    $(".btnSaveSx").bind("click", SaveSx);
    $(".btnEditSx").bind("click", EditSx);
    $(".btnDeleteSx").bind("click", DeleteSx);
	
	document.getElementById("anteprima").disabled = true;
	document.getElementById("sviluppa").disabled = true;
};

function DeleteSx(){
    var par = $(this).parent().parent(); //tr
    par.remove();
	document.getElementById("salva").disabled = false;
}; 

$(function(){
    //Add, Save, Edit and Delete functions code
    $(".btnEditSx").bind("click", EditSx);
    $(".btnDeleteSx").bind("click", DeleteSx);
    $("#btnAddSx").bind("click", AddSx);
});