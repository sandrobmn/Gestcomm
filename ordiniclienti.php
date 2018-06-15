<?php

include_once('dbGEMINI.php');
include_once('ordiniOPOC.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="ordini_clienti";
	$schema=$schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoPKs=$schema["PK"];
	$campo=array();
	$i=0;
	foreach($campiTab as $x => $x_value) {
		//echo "Key=" . $x . ", Value=" . $x_value;
		$campo[$i]=$x;
		if ($x=="unique_id")
			$indiceTk=$i;
		$i++;
	}	
	$dbhandle=$dbhData;
	
	$valoreCampo = array(6);
	$oper="";
	if(isset($_POST["op"]))
	{
		$oper=$_POST["op"];
	}
	else
	{
		$oper=$_GET["op"];
	}
	//echo "oper:".$oper;

	error_log("ordiniclienti.php"." ".$oper);
	
	$row="";
	$pag=1;
	switch ($oper){
		case "delete":
		case "insert":
		case "edit":
			if(isset($_POST["op"]))
			{
				for ($i=0;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valoreCampo[$i]=$_POST[$nomeCampo];
				}	
			}
			else
			{
				for ($i=0;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valoreCampo[$i]=$_GET[$nomeCampo];
				}	
			}
			if ($oper == "insert")
				$res = $dbGMN->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
			if ($oper == "edit")
				$res = $dbGMN->db_update_valori($dbhandle,$tabella,$valoreCampo,$campo);
			if ($oper == "delete")
				$res = $dbGMN->db_delete_valori($dbhandle,$tabella,$valoreCampo,$campo);
			if($res)
				$msg=$oper." riuscito";
			else
				$msg=$oper." fallito";
		break;
		
		case "get":
		$npag=1;

			$nomeCampo="idordine_cliente";;
			if(isset($_POST["op"]))
			{
				$valoreId=$_POST[$nomeCampo];//first, last,nextfw,nextbw
				$filtro=$_POST["filtro"];//first, last,nextfw,nextbw
				}
			else
			{
				$valoreId=$_GET[$nomeCampo];
				$filtro=$_GET["filtro"];//first, last,nextfw,nextbw
			}
			
			if (strlen($filtro))
				$where="(".$filtro.")";
			else
				$where=$nomeCampo." = '".$valoreId."'";
			$msg= $oper."(".$where.")";
			$res=true;
			$pag=1;
				$campiElenco="idordine_cliente,idcliente,idarticolo,qta_richiesta,data_consegna_prevista,note,qta_prodotta,qta_consegnata,data_consegna_effettiva,idop";
				//$ordinamento="idop";
				$ordinamento="data_consegna_prevista asc";
				$raggruppamento="";
				$pagesize=10;
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$where,$pag,$pagesize,$ordinamento,$raggruppamento,$npag);
		break;
		
		case "getExt":
		$npag=1;

			$nomeCampo="idordine_cliente";;
			if(isset($_POST["op"]))
			{
				$valoreId=$_POST[$nomeCampo];//first, last,nextfw,nextbw
				$filtro=$_POST["filtro"];//first, last,nextfw,nextbw
				}
			else
			{
				$valoreId=$_GET[$nomeCampo];
				$filtro=$_GET["filtro"];//first, last,nextfw,nextbw
			}
			
			if (strlen($filtro))
				$where="(".$filtro.")";
			else
				$where=$nomeCampo." = '".$valoreId."'";
			$msg= $oper."(".$where.")";
			$res=false;
			$pag=1;
			$row=array();
			$rec=array();
			$esito=calcolaDataInizioProduzioneStimata($dbGMN,$dbhandle,$where,$rec);
			if ($esito) {
				$row[0]=$rec;
				$res=true;
			}
				/*
				$campiElenco="max(idordine_cliente) as idordine_cliente,idcliente,idarticolo,sum(qta_richiesta) as qta_richiesta,max(data_consegna_prevista) as data_consegna_prevista";
				$tabellaJoin="ordini_clienti as oc join fasi_x_articolo as fxa on oc.idarticolo=fxa.idarticolo";
				$campiElenco .=",fxa.tempo_attrezzaggio_h,fxa.tempo_ciclo_s";
				$filtro = $where. " and fxa.tempo_ciclo_s > 0";
				$ordinamento="";
				$raggruppamento="idcliente,oc.idarticolo,fxa.tempo_attrezzaggio_h,fxa.tempo_ciclo_s,oc.qta_richiesta";
				$pagesize=1;
				$row=db_get($dbhandle,$tabellaJoin,$campiElenco,$filtro,$pag,$pagesize,$ordinamento,$raggruppamento,$npag);
				*/
		break;
		
		case "report":
		case "list":
		case "listeComplete":
			if(isset($_POST["op"]))
			{
				$pag=$_POST["pag"];//first, last,nextfw,nextbw
				$npag=$_POST["npag"];//first, last,nextfw,nextbw
				//$nrighexpag=$_POST["nrighe"];//n. di righe x pagina
				$filtro=$_POST["filtro"];//id_order > 'C2'

			}
			else
			{
				$pag=$_GET["pag"];
				$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
            }
			$campiElenco="idordine_cliente,idcliente,idarticolo,qta_richiesta,data_consegna_prevista,note,qta_prodotta,qta_consegnata,data_consegna_effettiva,idop";
			$ordinamento="data_consegna_prevista desc , idcliente asc , idordine_cliente asc ";
			if ( $oper == "listeComplete") {
				//$ordinamento="idcliente asc , idordine_cliente asc ";
				$ordinamento="data_consegna_prevista asc , idcliente asc ";
			}

		error_log("filtro:".$filtro);

            //---$row=db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
            $res=true;
            $row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
            if (count($row)>0)
                $res=true;
            else
				$res=false;
			if ($res == true && $oper == "listeComplete") {
				$row1=$row;
				$idarticolo=$row1[0]["idarticolo"];
				$idop=$row1[0]["idop"];
				$qta=calcolaQta($row1);
				$row2=caricaListaMateriali($dbhandle,$idarticolo,$qta,$dbGMN);
				$row3=caricaListaLavorazioni($dbhandle,$idarticolo,$dbGMN);
				$row=array();
				$row["ordini_clienti"]=$row1;
				$row["materiali"]=$row2;
				$row["lavorazioni"]=$row3;

			}
		break;
		
		case "listbox":
			$npag=0;
			$pag=1;
			$filtro="";
			if(isset($_POST["op"]))
			{
				if(isset($_POST["pag"])) $pag=$_POST["pag"];//first, last,nextfw,nextbw
				if(isset($_POST["npag"])) $npag=$_POST["npag"];//first, last,nextfw,nextbw
				//$nrighexpag=$_POST["nrighe"];//n. di righe x pagina
				if(isset($_POST["filtro"]))
					$filtro=$_POST["filtro"];//id_order > 'C2'
				
			}
			else
			{
				if(isset($_GET["pag"])) $pag=$_GET["pag"];
				if(isset($_GET["npag "]))$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				if(isset($_GET["filtro"]))
					$filtro=$_GET["filtro"];//id_order > 'C2'
			}
			
			$campiElenco="idordine_cliente as id,(cast(idordine_cliente as varchar)+' '+convert(varchar,data_consegna_prevista, 105)+' '+cast(qta_richiesta as varchar)) as descr";
			$ordinamento="idordine_cliente asc ";
			$pag=1;
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,1000,$ordinamento);
            if (count($row)>0)
                $res=true;
            else
				$res=false;
			
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	
	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listeComplete" || $oper == "listbox" || $oper == "get" || $oper == "getExt")
		$risposta=preparaRisposta($res,json_encode($row),$oper,$pag,$npag);
	else
		$risposta=preparaRisposta($res,$msg,$oper);
	
	echo json_encode($risposta);
	$dbGMN->db_close($dbhandle);
	}
	else 
	{
		$dbGMN->db_close($dbhandle);

		// filename for download
		$nomeReport = "Report_ordini_clienti";

		exportReport($nomeReport,$filtro,$row);
	}

function calcolaQta($row){
	$qta=0;
	$nr=count($row);
	for ($i=0;$i<$nr;$i++)
		$qta=$qta+$row[$i]["qta_richiesta"];
	return $qta;
}
function caricaListaMateriali($dbhandle,$idarticolo,$qta,$dbGMN) {
	$pag=1;
	$nrighexpag=1000;
	$npag=1;
	
	$tabellejoin="materiali_x_articolo as mxa join materiali as m on mxa.idmateriale=m.idmateriale";
	$campiElenco="idarticolo,mxa.idmateriale,descrizione,qta_unitaria,(qta_unitaria*".$qta.") as qta_richiesta";
	$ordinamento="idarticolo asc ,idmateriale asc ";
	$filtro="idarticolo='".$idarticolo."'";
	
	error_log("filtro:".$filtro);

	//---$row=db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
	$res=true;
	$row=$dbGMN->db_get($dbhandle,$tabellejoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
	return $row;
}
function caricaListaLavorazioni($dbhandle,$idarticolo,$dbGMN) {
	$pag=1;
	$nrighexpag=1000;
	$npag=1;
	
	$tabellejoin="fasi_x_articolo as fxa join fasi as f on fxa.idfase=f.idfase";
	$tabellejoin=$tabellejoin." join risorse as r on fxa.idrisorsa=r.idrisorsa";
	$campiElenco="idarticolo,fxa.idfase,f.descrizione as descr_fase";
	$campiElenco=$campiElenco.",fxa.idrisorsa,r.descrizione as descr_risorsa";
	$campiElenco=$campiElenco.",tempo_attrezzaggio_h,tempo_ciclo_s,note";
	$ordinamento="idarticolo asc ,idfase asc ";
	$filtro="idarticolo='".$idarticolo."'";

	error_log("filtro:".$filtro);

	//---$row=db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
	$res=true;
	$row=$dbGMN->db_get($dbhandle,$tabellejoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
	return $row;

}
?>
