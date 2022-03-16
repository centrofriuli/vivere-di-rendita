// JavaScript Document

function AddDx(){
	var dataNow = new Date();
	var Anno = dataNow.getFullYear();
	var Tassoint = '2.5';
	var Costo = '0';
	var Importo = '0';
	
    $("#tblDataDx tbody").append(
        "<tr>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloNumeri(event);' value='" + Anno + "'/></td>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloValuta(event);' value='" + Importo + "'/></td>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloPercentuali(event);' value='" + Tassoint + "'/></td>"+
        "<td><input type='text' class='rowvalue' onkeypress='return soloPercentuali(event);' value='" + Costo + "'/></td>"+
        "<td><img src='img/save.png' class='btnSaveDx'> <img src='img/delete.png' class='btnDeleteDx'/></td>"+
        "</tr>");
     
        $(".btnSaveDx").bind("click", SaveDx);      
        $(".btnDeleteDx").bind("click", DeleteDx);
	
	document.getElementById("anteprima").disabled = true;
	document.getElementById("sviluppa").disabled = true;
}; 

function SaveDx(){
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
    tdButtons.html("<img src='img/delete.png' class='btnDeleteDx'/> <img src='img/pencil.png' class='btnEditDx'/>");
 
    $(".btnEditDx").bind("click", EditDx);
    $(".btnDeleteDx").bind("click", DeleteDx);
		
	document.getElementById("salva").disabled = false;
}; 

function EditDx(){
    var par = $(this).parent().parent(); //tr
    var tdAnni = par.children("td:nth-child(1)");
    var tdImporto = par.children("td:nth-child(2)");
    var tdInter = par.children("td:nth-child(3)");
    var tdCosto = par.children("td:nth-child(4)");
    var tdButtons = par.children("td:nth-child(5)");
 
    tdAnni.html("<input type='text' id='txtAnni' onkeypress='return soloNumeri(event);' style='width:70px;' value='"+tdAnni.html()+"'/>");
    tdImporto.html("<input type='text' id='txtImporto' onkeypress='return soloValuta(event);' style='width:90px;' value='"+tdImporto.html()+"'/>");
    tdInter.html("<input type='text' id='txtInter' onkeypress='return soloPercentuali(event);' style='width:70px;' value='"+tdInter.html()+"'/>");
    tdCosto.html("<input type='text' id='txtCosto' onkeypress='return soloPercentuali(event);' style='width:70px;' value='"+tdCosto.html()+"'/>");
    tdButtons.html("<img src='img/save.png' class='btnSaveDx'/>");
 
    $(".btnSaveDx").bind("click", SaveDx);
    $(".btnEditDx").bind("click", EditDx);
    $(".btnDeleteDx").bind("click", DeleteDx);
		
	document.getElementById("anteprima").disabled = true;
	document.getElementById("sviluppa").disabled = true;
};

function DeleteDx(){
    var par = $(this).parent().parent(); //tr
    par.remove();
	document.getElementById("salva").disabled = false;
}; 

$(function(){
    //Add, Save, Edit and Delete functions code
    $(".btnEditDx").bind("click", EditDx);
    $(".btnDeleteDx").bind("click", DeleteDx);
    $("#btnAddDx").bind("click", AddDx);
});