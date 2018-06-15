/*
function aprimenu(){
	$("#sidebar").show();
};
function chiudimenu(){
	$("#sidebar").hide();
};
function aprichiudimenu(){
	$("#sidebar").toggle();
};

function caricamento(){
	chiudimenu();
};
*/
function richiestaDatiExt(myFormNameIn,myFormNameOut,myTableOut,opType,myAction,elencoCampiOut,callback){
	/*
	if (callback && typeof callback === 'function') 
		var elencoCampiLista = null;
	else
		 var elencoCampiLista = (typeof arguments[1] == 'undefined')?null:arguments[1];
	*/
	var elencoCampiLista = (typeof arguments[5] == 'undefined')?null:arguments[5];

	 $.post( $("#"+myFormNameIn).attr("action"),
         $("#"+myFormNameIn+" :input").serializeArray(),
         function(risposta){
			// $("#result").html(risposta);
			 
			 //var oper=document.getElementById("myForm");
			 //alert(oper.value);
			 //alert($("#myForm #pag").attr("name"));
			 if (risposta.substr(0,1)=="{" || risposta.substr(0,1)<' ') {
				var info = $.parseJSON(risposta);
				$esito=info["esito"];
				$op=info["richiesta"];
				$dati=info["dati"];
				$pagina=info["pagina"];
				$pagine=info["pagine"];
				if ($esito==true) {
					switch(opType) {
						case "list":
							$("#pag").val($pagina);
							$("#npag").val($pagine);
							var myArr = $.parseJSON($dati);
							scorriRecord(myArr,myTableOut,myAction,elencoCampiLista);
							break;
						case "get":
							var myArr = $.parseJSON($dati);
							scorriRecordGet(myArr,myFormNameOut);
							break;
						default: 
							$("#result"+myFormNameOut).html($dati);
							//clearInput(myFormName);
					}
					
					$("#result"+myFormNameOut).html($esito);
				}
				else
					$("#result"+myFormNameOut).html($dati);

				// invoca myFunction
				if (callback && typeof callback === 'function') 
					callback();
		}
		else {
			$("#result"+myFormNameOut).html(risposta);
			}
			
		 });

};	

function richiestaDati(myFormName,fcallback){
	if (fcallback && typeof fcallback === 'function') {
		var elencoCampiLista = null;
		var callback=fcallback;
	}
	else {
		 var elencoCampiLista = (typeof arguments[1] == 'undefined')?null:arguments[1];
		 var callback = (typeof arguments[2] == 'undefined')?null:arguments[2];
	}	 
	 $.post( $("#"+myFormName).attr("action"),
         $("#"+myFormName+" :input").serializeArray(),
         function(risposta){
			// $("#result").html(risposta);
			 
			 //var oper=document.getElementById("myForm");
			 //alert(oper.value);
			 //alert($("#myForm #pag").attr("name"));
			 if (risposta.substr(0,1)=="{" || risposta.substr(0,1)<' ') {
				var info = $.parseJSON(risposta);
				$esito=info["esito"];
				$op=info["richiesta"];
				$dati=info["dati"];
				$pagina=info["pagina"];
				$pagine=info["pagine"];
				if ($esito==true) {
					switch($op) {
						case "list":
							$("#pag").val($pagina);
							$("#npag").val($pagine);
							var myAction="invocaEdit";
							
							var myArr = $.parseJSON($dati);
							scorriRecord(myArr,"my_table",myAction,elencoCampiLista);
							break;
						case "get":
						case "getExt":
						case "getData":
						case "getDataInizioProduzione":
						case "getDataFineProduzione":
						case "getIstruzioni":
						case "getCNCToolOffset":
						case "getCNCcounter":
						case "getCNCreportmisure":
							var myArr = $.parseJSON($dati);
							scorriRecordGet(myArr,"myFormEdit");
							break;
						case "getCheckAssociazioneArtPN":
						case "getCheckAvanzamentoNC":
							var myArr = $.parseJSON($dati);
							scorriRecordGet(myArr,myFormName);
							break;
					case "setCNCToolOffset":
						case "setCNCcounter":
						case "setCNCprogram":
							$("#result"+myFormName).html($dati);
							break;
						default: 
							$("#result"+myFormName).html($dati);
							//clearInput(myFormName);
					}
					
					$("#result"+myFormName).html($esito);
				}
				else
					$("#result"+myFormName).html($dati);

				// invoca myFunction
				if (callback && typeof callback === 'function') 
					callback();
		}
		else {
			$("#result"+myFormName).html(risposta);
			}
			
		 });

};	
function clearInput(myFormName) {
    $("#"+myFormName+" :input").each( function() {
       $(this).val('');
    });
	$("#sub"+myFormName).val('Invio');
}

function scorriRecord(arr,id_table,myAction){
	var elencoCampiLista= (typeof arguments[3] == 'undefined')?null:arguments[3];
	for (var i = 0; i < arr.length; i++){
		var obj = arr[i];
		var newtr = aggiungiRiga(obj,id_table,myAction,elencoCampiLista);
			var att = document.createAttribute("id");       
			att.value = "r"+i;                
			newtr.setAttributeNode(att); 
		
			var att = document.createAttribute("onclick");       
			att.value = "invocaGrafico('r"+i+"')";                 
			newtr.setAttributeNode(att); 
		
	}
}

function aggiungiRiga(objRec,id_table,myAction){
	var elencoCampiLista= (typeof arguments[3] == 'undefined')?null:arguments[3];
	if (elencoCampiLista != null)
		obj=elencoCampiLista;
	else
		obj=objRec;
     //var table = document.getElementById(id_table);
     var tbody = $("#"+id_table).children("tbody");//table.getElementsByTagName('tbody')[0];
     //var colonne = table.getElementsByTagName('th').length;	
     var tr = document.createElement('tr');
		var primo=true;
		var idValue="";
	 	for (var key in obj){
			var attrName = key;
			var attrValue = formatValue(objRec[key],key,elencoCampiLista);
			var attrType = myFieldType(key,elencoCampiLista);
			// format output
			
			if (primo) {idValue=attrValue;
				atttrValue="'"+attrValue+"'";
			}
				
			var td = document.createElement('td');
			var tx = document.createTextNode(attrValue);
			td.appendChild(tx);

			var att = document.createAttribute("onclick");       
			att.value = "invocaEdit('"+idValue+"')";                
			td.setAttributeNode(att); 

			var att = document.createAttribute("name");       
			att.value = attrName;                
			td.setAttributeNode(att); 
			
			var att = document.createAttribute("class");       
			if (attrType == "N") {
				att.value = "campo_numerico";                
			}
			else {
				att.value = "campo_testo";                
			}
			td.setAttributeNode(att); 

			if (attrType != null) // se non presente in elencoCampi non inserisco nella riga
				tr.appendChild(td);
			primo=false;
		  	
		}
	var att = document.createAttribute("data-xref");       // Create a "data-xref" attribute
	att.value = myAction +"("+idValue+")";                // Set the value of the attribute
	tr.setAttributeNode(att); 
    
	tbody.append(tr);

	return tr;
}

function scorriRecordGet(arr,idForm){
	for (var i = 0; i < arr.length; i++){
		var obj = arr[i];
		aggiungiRigaGet(obj,idForm);
		
	}
}

function aggiungiRigaGet(obj,idForm){

	 	for (var key in obj){
			var attrName = key;
			var attrValue = obj[key];
			//$selector="#".idForm." #".attrName;
			//$('input[type!="radio"]')
			if ($("input[name='"+attrName+"']").length){
				$("input[name='"+attrName+"']").val(attrValue);
			}
			else {
				$("textarea[name='"+attrName+"']").val(attrValue);
			}
		  	
		}
}

/*--------------------- carica record con 2 -3 pk --------------------------*/
function scorriRecord2K(arr,id_table,myAction){
	var elencoCampiLista= (typeof arguments[3] == 'undefined')?null:arguments[3];
	var elencoPKs = (typeof arguments[4] == 'undefined')?null:arguments[4];

	for (var i = 0; i < arr.length; i++){
		var obj = arr[i];
		aggiungiRiga2K(obj,id_table,myAction,elencoCampiLista,elencoPKs);
		
	}
}

function aggiungiRiga2K(objRec,id_table,myAction){
	var elencoCampiLista= (typeof arguments[3] == 'undefined')?null:arguments[3];
	var elencoPKs = (typeof arguments[4] == 'undefined')?null:arguments[4];

	if (elencoCampiLista != null)
		obj=elencoCampiLista;
	else
		obj=objRec;
	

		//var table = document.getElementById(id_table);
     var tbody = $("#"+id_table).children("tbody");//table.getElementsByTagName('tbody')[0];
     //var colonne = table.getElementsByTagName('th').length;	
     var tr = document.createElement('tr');
	//var primo=true;
	var idValue="";
	var idValue1="";
	var idValue2="";
	
	// recupera PK
	if (myAction != null && myAction != undefined  ) {
		if (elencoPKs == null){
			elencoPKs=Array();
			elencoPKs["1"]="id_order";
			elencoPKs["2"]="unique_id";
		}
		for (pk in elencoPKs){
			var pkName = elencoPKs[pk];
			switch(pk){
				case "1":
				idValue1=objRec[pkName];
				break;
				case "2":
				idValue2=objRec[pkName];
				break;
				case "3":
				idValue3=objRec[pkName];
				break;
			}
		}	
	}
	
	// componi riga
	for (var key in obj){
		var attrName = key;

		var attrValue = formatValue(objRec[key],key,elencoCampiLista);
		var attrType = myFieldType(key,elencoCampiLista);


//		if (myAction != null && myAction != undefined ) {
				var td = document.createElement('td');
				var tx = document.createTextNode(attrValue);
				td.appendChild(tx);

				var att = document.createAttribute("class");       
				if (attrType == "N") {
					att.value = "campo_numerico";                
				}
				else {
					att.value = "campo_testo";                
				}
				td.setAttributeNode(att); 

				if (attrType != null) 
					tr.appendChild(td);
//		}
	}

	if (myAction != null && myAction != undefined  ) {
		var att = document.createAttribute("onclick");       
		att.value = myAction+"('"+idValue1+";"+idValue2+"')";                
		tr.setAttributeNode(att); 

		var att = document.createAttribute("data-xref");       // Create a "data-xref" attribute
		att.value = myAction +"("+idValue+")";                // Set the value of the attribute
		tr.setAttributeNode(att); 
	}

	tbody.append(tr);

};

/*--------------------------------------------------------------------------*/

function myFieldType(key,elencoCampiLista){
	var res=null;
	if (elencoCampiLista == null)
		res="T";
	else
	{
		var attrKey=elencoCampiLista[key];
		if (attrKey !== undefined) {
			switch(attrKey["tipo"]) {
				case "F":// numerico float
				case "N":// numerico intero
				case "%":// percentuale
					res = "N";
					break;
				default:
					res="T";
					break;
			}
		}
		else {
			res=null;
		}			
	}
	return res;

}
/*
script/numberFormat154.js
per i valori numerici vedi:
http://www.mredkj.com/javascript/numberFormatPage2.html
*/
function formatValue(attrValue,key,elencoCampiLista) {
	var res=null;
	if (elencoCampiLista == null || attrValue==null)
		res=attrValue;
	else
	{
		var attrKey=elencoCampiLista[key];
		if (attrKey !== undefined) {
		
			switch(attrKey["tipo"]) {
				case "F":// numerico float
					var num = new NumberFormat();
					num.setInputDecimal('.');
					num.setNumber(attrValue); // obj.value is '-1000.247'
					num.setPlaces('3', false);
					num.setCurrencyValue('$');
					num.setCurrency(false);
					num.setCurrencyPosition(num.LEFT_OUTSIDE);
					num.setNegativeFormat(num.LEFT_DASH);
					num.setNegativeRed(false);
					num.setSeparators(true, '.', '.');
					res = num.toFormatted();
					break;
				case "N":// numerico intero
					var num = new NumberFormat();
					num.setInputDecimal('.');
					num.setNumber(attrValue); // obj.value is '-1000.247'
					num.setPlaces('0', false);
					num.setCurrencyValue('$');
					num.setCurrency(false);
					num.setCurrencyPosition(num.LEFT_OUTSIDE);
					num.setNegativeFormat(num.LEFT_DASH);
					num.setNegativeRed(false);
					num.setSeparators(true, '.', '.');
					res = num.toFormatted();
					break;
				case "%":// percentuale
					var num = new NumberFormat();
					num.setInputDecimal('.');
					num.setNumber(attrValue); // obj.value is '-1000.247'
					num.setPlaces('2', false);
					num.setCurrencyValue('$');
					num.setCurrency(false);
					num.setCurrencyPosition(num.LEFT_OUTSIDE);
					num.setNegativeFormat(num.LEFT_DASH);
					num.setNegativeRed(false);
					num.setSeparators(false, ',', ',');
					res = num.toFormatted();
					break;
				
				case "H":// tempo (secondi) come ore 
					if (attrValue>0)
					res=timeFromSecs(attrValue);
					break;
				case "D": // dataora come sola data
					partiDataOra=attrValue.split(" ");
					if (partiDataOra.length<2)
						partiDataOra=attrValue.split("T");
					if (partiDataOra.length>1)
						res=partiDataOra[0];
					else
						res=attrValue;
					break;
				default:
					res=attrValue;
					break;
			}
		}
		else {
			res=attrValue;
		}			
	}
	return res;
}

function timeFromSecs2(seconds)
{
    return(
    Math.floor(seconds/86400)+'d :'+
    Math.floor(((seconds/86400)%1)*24)+'h : '+
    Math.floor(((seconds/3600)%1)*60)+'m : '+
    Math.round(((seconds/60)%1)*60)+'s');
}

function timeFromSecs(seconds)
{
    return(
    //Math.floor(seconds/86400)+'d :'+
    Math.floor((seconds/3600))+'h : '+
    Math.floor(((seconds/3600)%1)*60)+'m : '+
    Math.round(((seconds/60)%1)*60)+'s');
}

/* -------------------- menu --------------------------*/
function costruisciMenu() {
	
	var menuItems = {
	"menu-0": {
		"id": "menu-0",
		"title": "Operazioni Principali",
		"parentId": null,
		"url": "#",
		"childCount": 3
	},
	"menu-01": {
		"id": "menu-01",
		"title": "Ordini di Produzione",
		"parentId": "menu-0",
		"url": "ordiniproduzione.html",
		"childCount": 0
	},
	"menu-02": {
		"id": "menu-02",
		"title": "Associazione Macchine a Ordine di Produzione",
		"parentId": "menu-0",
		"url": "macchinexOP.html",
		"childCount": 0
	},
	"menu-03": {
		"id": "menu-03",
		"title": "Avanzamento Lotti di produzione Macchine Plurimandrino",
		"parentId": "menu-0",
		"url": "pcsPLURI.html",
		"childCount": 0
	},
	"menu-4": {
		"id": "menu-4",
		"title": "CNC",
		"parentId": null,
		"url": "#",
		"childCount": 3
	},
	"menu-41": {
		"id": "menu-41",
		"title": "Setup CNC",
		"parentId": "menu-4",
		"url": "setupCNC.html",
		"childCount": 0
	},
	"menu-42": {
		"id": "menu-42",
		"title": "Correzione Utensili CNC",
		"parentId": "menu-4",
		"url": "setupCNCcorrettori.html",
		"childCount": 0
	},
	"menu-43": {
		"id": "menu-43",
		"title": "Grafico Stato CNC",
		"parentId": "menu-4",
		"url": "statusCNC.html",
		"childCount": 0
	},
	"menu-1": {
		"id": "menu-1",
		"title": "Statistiche e Report",
		"parentId": null,
		"url": "#",
		"childCount": 4
	},
	"menu-11": {
		"id": "menu-11",
		"title": "Produzione totale per OP",
		"parentId": "menu-1",
		"url": "produzione.html",
		"childCount": 0
	},
	"menu-12": {
		"id": "menu-12",
		"title": "Produzione per Macchina con Oee",
		"parentId": "menu-1",
		"url": "impieghi.html",
		"childCount": 0
	},
	"menu-13": {
		"id": "menu-13",
		"title": "Produzione totale per OP con Oee",
		"parentId": "menu-1",
		"url": "impieghixcommessa.html",
		"childCount": 0
	},
	"menu-14": {
		"id": "menu-13",
		"title": "Produzione oraria e giornaliera",
		"parentId": "menu-1",
		"url": "impieghixpartnumber.html",
		"childCount": 0
	},
	"menu-2": {
		"id": "menu-2",
		"title": "Manutenzione",
		"parentId": null,
		"url": "#",
		"childCount": 7
	},
	"menu-21": {
		"id": "menu-21",
		"title": "Lotti di Produzione",
		"parentId": "menu-2",
		"url": "commesse.html",
		"childCount": 0
	},
	"menu-22": {
		"id": "menu-22",
		"title": "Macchine per Lotto di Produzione",
		"parentId": "menu-2",
		"url": "macchinexcommesse.html",
		"childCount": 0
	},
	"menu-23": {
		"id": "menu-23",
		"title": "Avanzamento Lotti di produzione",
		"parentId": "menu-2",
		"url": "pcs.html#",
		"childCount": 0
	},
	"menu-24": {
		"id": "menu-24",
		"title": "Macchine",
		"parentId": "menu-2",
		"url": "macchine.html",
		"childCount": 0
	},
	"menu-25": {
		"id": "menu-24",
		"title": "Tipi di Macchina",
		"parentId": "menu-2",
		"url": "tipimacchina.html",
		"childCount": 0
	},
	"menu-26": {
		"id": "menu-24",
		"title": "Part Numbers",
		"parentId": "menu-2",
		"url": "partnumbers.html",
		"childCount": 0
	},
	"menu-27": {
		"id": "menu-24",
		"title": "Part Programs",
		"parentId": "menu-2",
		"url": "partprograms.html",
		"childCount": 0
	},
	"menu-3": {
		"id": "menu-3",
		"title": "Impostazioni",
		"parentId": null,
		"url": "#",
		"childCount": 1
	}, 
	"menu-31": {
		"id": "menu-31",
		"title": "Preferenze",
		"parentId": "menu-3",
		"url": "opzioni.html",
		"childCount": 0
	} 
  };
		var htmlTxt = "";
	var cat = [];
	var htmlStrTxt = "";
	var url = "#";

	function recurseMenu(parent, level) {
		htmlStrTxt = '<ul>';
		for (var x in menuItems) {
			if (menuItems[x].parentId == parent) {
				var menuClass="menu-figlio";
				if (menuItems[x].childCount > 0)
					menuClass="menu-padre";
				htmlStrTxt += '<li><a class="'+menuClass+'" href="' + menuItems[x].url + '">' + menuItems[x].title + '</a>';

				if (menuItems[x].childCount > 0) {
					htmlStrTxt += recurseMenu(menuItems[x].id, level + 1);
				}
				htmlStrTxt += '</li>';
			}
		}
		return htmlStrTxt + '</ul>';
	}

	var htmlTxt = recurseMenu(null, 0);
	$(".nestedsidemenu").html(htmlTxt);

}

/* ------ gestione tab ------- */

function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

/* ------ gestione ordinamento colonne lista ------- */
//
// utilizza la struttura elencoCampiLista
//
function setOrdinamento(campo) {
	var obj=elencoCampiLista;
	for (var key in obj){
		var attrName = key;
		if (attrName==campo){
		var attrValueObj = obj[key];
			var key2='sort';
			var attrValue2 = attrValueObj[key2];
			var newValue="";
			switch(attrValue2){
				case '':
					newValue="asc";
					break;
				case 'asc':
					newValue="desc";
					break;
				case 'desc':
					newValue="";
					break;
			}
			attrValueObj[key2]=newValue;
			if (newValue >""){
				var sort_descr=campo+ " "+newValue+" ";
				$("#th_"+campo).attr("class","sorting_"+newValue);
			}
			else {
				$("#th_"+campo).attr("class","sorting");
				var sort_descr="";
			}
			var old_sorting=$("#ordinamento").val();
			var new_sorting="";
			var n= old_sorting.indexOf(campo);
			// se non contiene campo va aggiunto
			if (n <0) {
				new_sorting=old_sorting;
				if (new_sorting.length > 0)
					new_sorting += ",";
				new_sorting += sort_descr;
			}
			else {
			// se contiene il campo, va sostituito il valore corrente se il nuovo non è vuoto
				var partsOfStr = old_sorting.split(',');
				for (i=0;i<partsOfStr.length;i++) {
					
					n=partsOfStr[i].indexOf(campo);
					if (n>=0) {
						if (sort_descr.length>0) {
							if (new_sorting.length > 0)
								new_sorting +=  ",";
							new_sorting += sort_descr;
						}
					}
					else {
						if (new_sorting.length > 0)
							new_sorting += ",";
						new_sorting += partsOfStr[i];
					}
				}

			}
			$("#ordinamento").val(new_sorting);
			
			// aggiorna href per download report : 	"produzione.php?op=report&pag=1&nrighe=1000&filtro=''"
			sCerca="&ordinamento=";
			aggiornaParametriExport(sCerca, new_sorting);

			aggiornaLista();
		}
	}
}

/* ------ gestione filtro di ricerca ------- */

function valutaCriterioFiltro(nomeForm, nomeCampo) {
    criterio="";
    sel="#"+nomeForm+" input[name="+'"'+nomeCampo+'"'+"]";
    valore=$(sel).val();
    if (valore !='' && valore != undefined) {
		tipo=$(sel).attr('type');
		// se input di tipo datetime elimina il prefisso T davanti al tempo e aggiunge i secondi
		if (tipo=="datetime-local") {
			valore=valore.replace('T',' ')+":00";
		}
        sel="#"+nomeForm+" select[name="+'"'+nomeCampo+"_oper"+'"'+"]";
		operatore=$(sel).val();
		if (operatore == "in")
			criterio=nomeCampo+" "+operatore+" "+valore+" ";
		else
        	criterio=nomeCampo+" "+operatore+" '"+valore+"' ";
    }
    return criterio;
};
    
function valutaFiltro(nomeForm) {
	/*
	var nomiCampi = ["id_order","posizione","product","requested_pieces","CNCprogram","planned_start_date","planned_end_date","status"];
	
	for (var i=0;i<nomiCampi.length;i++){
		nomeCampo=nomiCampi[i];
		criterio=valutaCriterioFiltro(nomeForm, nomeCampo)
		if (criterio !="") {
			if (criteri !="") criteri=criteri+" and "
			criteri=criteri+criterio;
		}
	}
    */  
	criteri="";

	$('#'+nomeForm).find('input').each(function () {
		//alert(this.value);
		
		var class_a=$(this).attr("class");
		if (class_a != "operator") {
			nomeCampo=$(this).attr("name");
			criterio=valutaCriterioFiltro(nomeForm, nomeCampo)
			if (criterio !="") {
				if (criteri !="") criteri=criteri+" and "
				criteri=criteri+criterio;
			}
		}
		
	});
    return criteri;
};
    
function applicaFiltro(){
	criteri=valutaFiltro("myFormFiltro");
    
    chiudiFiltro();
    $("#valFiltro").val(criteri);

	return criteri;
	/*
	// aggiorna href per download report : 	"produzione.php?op=report&pag=1&nrighe=1000&filtro=''"
	sCerca="&filtro=";
	aggiornaParametriExport(sCerca, criteri)	
	// aggiorna dati
	aggiornaLista();
	*/
};


/* ------ gestione report ------- */
function aggiornaPrmOrderByExport(valore){
	// aggiorna href per download report : 	"produzione.php?op=report&pag=1&nrighe=1000&filtro=''"
	sCerca="&ordinamento=";
	aggiornaParametriExport(sCerca, valore);
}

/* ------ gestione report ------- */
function aggiornaPrmFiltroExport(valore){
	// aggiorna href per download report : 	"produzione.php?op=report&pag=1&nrighe=1000&filtro=''"
	sCerca="&filtro=";
	aggiornaParametriExport(sCerca, valore);
}

function aggiornaParametriExport(sCerca, valore){
	// aggiorna href per download report : 	"produzione.php?op=report&pag=1&nrighe=1000&filtro=''"
	sHref=$("a#cmdReport").prop("href");
	//sCerca="&filtro=";
	if (sHref != null) {
		n=sHref.indexOf(sCerca);
		m=sHref.indexOf("&",n+sCerca.length);
		sHref1=sHref.substring(0,n+sCerca.length);
		if (m<0) sHref2="";
		else
			sHref2=sHref.substring(m,sHref.length);
		//alert(sHref1+":"+sHref2);
		sHref=sHref1+valore+sHref2;
		$("a#cmdReport").prop("href",sHref);
	}
};

/**************** paginazione dati ***********************/
/* per aggiornare il campo span pagina/pagine occorre richiamare impostaNpag da pagination e da aggiornaLista come callback */

function pagination(pagina) {
	npag=parseInt($("#pag").val());
	npagTot=parseInt($("#npag").val());
	if (isNaN(npagTot) || npagTot <= 0) npagTot=1000;

	if (pagina=='first') npag = 1;
	if (pagina=='last') npag = npagTot;
	if (pagina=='next') npag = npag+1;
	if (pagina=='prev') npag = npag-1;

	if (npag<1) npag = 1;
	if (npag > npagTot) npag = npagTot;

	$("#pag").val(npag);
	impostaNpag();
	aggiornaLista();
}

function impostaNpag() {
	var sMsg=$('#pag').val()+'/'+$('#npag').val();
	$("#paginf").html(sMsg);
	$("#pagsup").html(sMsg);

}

/************** condiziona date *****************/
// trasforma dataora std in ISO e copia da campo in campo_ISO
function condizionaData(e){
	v=$('input[name='+e+']').val();
	v=v.replace('T',' ');
	e1=e.replace('_ISO','');
	$('input[name='+e1+']').val(v);
}

// trasforma dataora ISO in std e copia da campo_ISO in campo
function condizionaDataISO(e){
	v=$('input[name='+e+']').val();
	v=v.replace(' ','T');
	e1=e+'_ISO';
	$('input[name='+e1+']').val(v);
}



