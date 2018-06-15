function richiestaDati(myFormName){
	 $.post( $("#"+myFormName).attr("action"),
         $("#"+myFormName+" :input").serializeArray(),
         function(risposta){
			// $("#result").html(risposta);
			 
			 //var oper=document.getElementById("myForm");
			 //alert(oper.value);
			 //alert($("#myForm #pag").attr("name"));
			 if (risposta.substr(0,1)=="{") {
			 var info = $.parseJSON(risposta);
			 $esito=info["esito"];
			 $op=info["richiesta"];
			 $dati=info["dati"];
			 $pagina=info["pagina"];
		 switch($op) {
		 case "list":
			 $("#pag").val($pagina);
			 var myAction="invocaEdit";
			 
			 var myArr = $.parseJSON($dati);
			 scorriRecord(myArr,"my_table",myAction);
			 break;
		 case "get":
			 var myArr = $.parseJSON($dati);
			 scorriRecordGet(myArr,"myFormEdit");
			break;
		 default: 
			 $("#result"+myFormName).html($dati);
			clearInput(myFormName);
		 }
		$("#result"+myFormName).html($esito);
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
	for (var i = 0; i < arr.length; i++){
		var obj = arr[i];
		aggiungiRiga(obj,id_table);
		
	}
}

function aggiungiRiga(obj,id_table,myAction){
     //var table = document.getElementById(id_table);
     var tbody = $("#"+id_table).children("tbody");//table.getElementsByTagName('tbody')[0];
     //var colonne = table.getElementsByTagName('th').length;	
     var tr = document.createElement('tr');
		var primo=true;
		var idValue="";
	 	for (var key in obj){
			var attrName = key;
			var attrValue = obj[key];
			if (primo) {idValue=attrValue;
				atttrValue="'"+attrValue+"'";
			}
				
			var td = document.createElement('td');
			var tx = document.createTextNode(attrValue);
			td.appendChild(tx);

			var att = document.createAttribute("onclick");       
			att.value = "invocaEdit('"+idValue+"')";                
			td.setAttributeNode(att); 
			
			tr.appendChild(td);
			primo=false;
		  	
		}
	var att = document.createAttribute("data-xref");       // Create a "data-xref" attribute
	att.value = myAction +"("+idValue+")";                // Set the value of the attribute
	tr.setAttributeNode(att); 
    
	tbody.append(tr);

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
			$("input[name='"+attrName+"']").val(attrValue);
		  	
		}
}
