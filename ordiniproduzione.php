<?php

include_once('dbGEMINI.php');
include_once('ordiniOPOC.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="op";
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

	error_log("ordiniproduzione.php"." ".$oper);
	
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

			$nomeCampo=$campo[0];
			if(isset($_POST["op"]))
			{
				$valoreId=$_POST[$nomeCampo];//first, last,nextfw,nextbw
			}
			else
			{
				$valoreId=$_GET[$nomeCampo];
			}
			
			$nomeCampo=$campo[0];
			$where=$nomeCampo." = '".$valoreId."'";
			$msg= $oper."(".$where.")";
			$res=true;
			$pag=1;
			$campiElenco="idop,data,idarticolo,qta_richiesta,qta_prodotta";
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$where,$pag,1,"","",$npag);
		break;
		
		case "getExt":
			$npag=1;
			$res=false;
			$nomeCampo=$campo[0];
			if(isset($_POST["op"]))
			{
				$valoreId=$_POST[$nomeCampo];//first, last,nextfw,nextbw
			}
			else
			{
				$valoreId=$_GET[$nomeCampo];
			}
			
			$nomeCampo=$campo[0];
			$where=$nomeCampo." = '".$valoreId."'";
			$msg= $oper."(".$where.")";
			$res=true;
			$pag=1;
			$campiElenco="idop,data,op.idarticolo,qta_richiesta,qta_prodotta";
			$tabella="op";
			$filtro = $where;
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,1,"","",$npag);
			error_log("1:".json_encode($row));
			$rec=$row[0];
			$esito=calcolaDataInizioProduzioneStimata($dbGMN,$dbhandle,$where,$rec);
			if ($esito) {
				$row[0]=$rec;
				$res=true;
				error_log("2:".json_encode($row));
			}

		break;
		
		case "getData":
		case "getDataInizioProduzione":
		case "getDataFineProduzione":
			$npag=1;
			$res=false;
			$msg="";

			if(isset($_POST["op"]))
			{
				$idop=$_POST['idop'];//first, last,nextfw,nextbw
				$idarticolo=$_POST['idarticolo'];//first, last,nextfw,nextbw
				$qta_richiesta=$_POST['qta_richiesta'];//first, last,nextfw,nextbw
				$data_consegna_prevista=$_POST['data_consegna_prevista'];//first, last,nextfw,nextbw
				$data_inizio_prevista=$_POST['data'];//first, last,nextfw,nextbw
			}
			else
			{
				$idop=$_GET['idop'];//first, last,nextfw,nextbw
				$idarticolo=$_GET['idarticolo'];//first, last,nextfw,nextbw
				$qta_richiesta=$_GET['qta_richiesta'];//first, last,nextfw,nextbw
				$data_consegna_prevista=$_GET['data_consegna_prevista'];//first, last,nextfw,nextbw
				$data_inizio_prevista=$_GET['data'];//first, last,nextfw,nextbw
			}
			error_log("prm:$idarticolo - $qta_richiesta - $data_inizio_prevista - $data_consegna_prevista");

			$res=true;
			$pag=1;
			$esito=true;
			$row=array();
			$rec=array();

			if ($oper == "getDataInizioProduzione" || $oper == "getData") {
				$dataInizioStimata=ricalcolaDataInizioProduzioneStimata($dbGMN,$dbhandle,$idarticolo,$qta_richiesta,$data_consegna_prevista);
				$esito=true;
				$rec["idop"]=$idop;
				$rec["data"]=$dataInizioStimata;
			}
			else {
				$dataFineStimata=ricalcolaDataFineProduzioneStimata($dbGMN,$dbhandle,$idarticolo,$qta_richiesta,$data_inizio_prevista);
				$esito=true;
				$rec["idop"]=$idop;
				$rec["data_consegna_prevista"]=$dataFineStimata;
			}
			
			if ($esito) {
				$row[0]=$rec;
				$res=true;
				error_log("2:".json_encode($row));
			}

		break;
		
		case "getIstruzioni":
			$npag=1;

			$nomeCampo=$campo[0];
			if(isset($_POST["op"]))
			{
				$valoreIdOP=$_POST["idop"];//first, last,nextfw,nextbw
				$valoreIdOC=$_POST["idordine_cliente"];//first, last,nextfw,nextbw
				$valoreDataOC=$_POST["data_consegna_prevista"];//first, last,nextfw,nextbw
				$valoreQtaOC=$_POST["qta_richiesta"];//first, last,nextfw,nextbw
			}
			else
			{
				$valoreIdOP=$_GET["idop"];
				$valoreIdOC=$_GET["idordine_cliente"];//first, last,nextfw,nextbw
				$valoreDataOC=$_GET["data_consegna_prevista"];//first, last,nextfw,nextbw
				$valoreQtaOC=$_GET["qta_richiesta"];//first, last,nextfw,nextbw
			}
			
			$res=false;
			$pag=1;
			$istruzioni=array();
			$testoIstruzioni="";	
			$row=array();

			//$testoIstruzioni=componiTestoIstruzioni($dbhandle,$valoreIdOP,$valoreIdOC);
			$testoIstruzioni=componiTestoIstruzioni($dbGMN,$dbhandle,$valoreIdOP,$valoreIdOC,$valoreDataOC,$valoreQtaOC);

			if (strlen($testoIstruzioni)>0) {
				
				$istruzioni["part_program_istruzioni"]=$testoIstruzioni;
				$row[0]=$istruzioni;
				$res=true;
			}
		break;
		
		case "report":
			if(isset($_POST["op"]))
			{
				$nrighexpag=$_POST["nrighe"];//n. di righe x pagina
			}
			else
			{
				$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
			}
		case "list":
			if(isset($_POST["op"]))
			{
				$pag=$_POST["pag"];//first, last,nextfw,nextbw
				$npag=$_POST["npag"];//first, last,nextfw,nextbw
				//$nrighexpag=$_POST["nrighe"];//n. di righe x pagina
				$filtro=$_POST["filtro"];//id_order > 'C2'
				$sort=$_POST["ordinamento"];//first, last,nextfw,nextbw

			}
			else
			{
				$pag=$_GET["pag"];
				$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
				$sort=$_GET["ordinamento"];//first, last,nextfw,nextbw
            }
			$campiElenco="idop,data,idarticolo,qta_richiesta,qta_prodotta";
			$raggruppamento="idop";
			$ordinamento="idop desc , data desc ";

			if ($sort>"")
				$ordinamento=$sort;


		error_log("filtro:".$filtro);

            //---$row=$dbGMN->db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
            $res=true;
            $row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
            if (count($row)>0)
                $res=true;
            else
                $res=false;
		break;
		
		case "listbox":
		$npag=0;

			$campiElenco="idop as id,(cast(idop as varchar) + ' ' + idarticolo) as descr";
			$ordinamento="idop desc ";
			$pag=1;
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,"",$pag,1000,$ordinamento);
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
	
	if ( $oper == "list" || $oper == "listbox" || $oper == "get"  || $oper == "getData" || $oper == "getDataInizioProduzione" || $oper == "getDataFineProduzione" || $oper == "getExt" || $oper == "getIstruzioni")
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
		$nomeReport = "Report_ordini_produzione";

		exportReport($nomeReport,$filtro,$row);
	}

function componiTestoIstruzioni($dbGMN,$dbhandle,$idOP,$idOC,$dataOraOC,$qtaOC){
	$testoIstruzioni="";
	$res=false;
	//error_log("componiTestoIstruzioni($idOP,$idOC,$dataOraOC,$qtaOC)");
	// legge op e oc
	$where="op.idop = '".$idOP."'";
	//$where=$where." and ";
	//$where=$where." oc.idordine_cliente = '".$idOC."'";
	$dataOC=explode(" ",$dataOraOC);
	$campi="op.idop,op.data,op.idarticolo, $qtaOC as qta_richiesta,'".$dataOC[0]."' as data_consegna_prevista";
	$tabelleJoin="op";
	$row=$dbGMN->db_get($dbhandle,$tabelleJoin,$campi,$where,$pag,1,"","",$npag);
	if (count($row)>0) {
		$res=true;
		$record=$row[0];
		$testoIstruzioni="O0777 (istruzioni)";
		$testoIstruzioni=$testoIstruzioni."\n";
		$testoIstruzioni=$testoIstruzioni."(OP ".$record["idop"]." ".$record["data"].")";
		$testoIstruzioni=$testoIstruzioni."\n";
		$testoIstruzioni=$testoIstruzioni."(".$record["idarticolo"].")";
		$testoIstruzioni=$testoIstruzioni."\n";
		/*
		$testoIstruzioni=$testoIstruzioni."(PZ ".$record["qta_richiesta"]." X ".$record["data_consegna_prevista"].")";
		$testoIstruzioni=$testoIstruzioni."\n";
		*/
	}
	// legge commesse x articolo
	if ($res) {
		// legge oc
		$where="idop = '".$idOP."'";
		//$where=$where." and ";
		//$where=$where." oc.idordine_cliente = '".$idOC."'";
		$dataOC=explode(" ",$dataOraOC);
		$campi="idordine_cliente,idarticolo, qta_richiesta,data_consegna_prevista";
		$ord="data_consegna_prevista asc ";
		$raggr="idordine_cliente,idarticolo, qta_richiesta,data_consegna_prevista";
		$tabelleJoin="ordini_clienti";
		$rowOC=$dbGMN->db_get($dbhandle,$tabelleJoin,$campi,$where,$pag,1,$ord,$raggr,$npag);
		if (count($rowOC)>0) {
			$res=true;
			for ($i=0;$i<count($rowOC);$i++){
				$rec=$rowOC[$i];
				$testoIstruzioni=$testoIstruzioni."(PZ ".$rec["qta_richiesta"]." X ".$rec["data_consegna_prevista"].")";
				$testoIstruzioni=$testoIstruzioni."\n";
			}
		}
	}
	// legge materiali x articolo
	if ($res) {
		$idarticolo=$record["idarticolo"];
		$qr=$record["qta_richiesta"];

		$where="idarticolo = '".$idarticolo."'";

		$campi="mxa.idarticolo,mxa.idmateriale,mxa.qta_unitaria,m.descrizione";
		$tabelleJoin="materiali_x_articolo as mxa join materiali as m on mxa.idmateriale=m.idmateriale";
		$row2=$dbGMN->db_get($dbhandle,$tabelleJoin,$campi,$where,$pag,1,"","",$npag);
		$res=false;
		if (count($row2)>0) {
			$res=true;
			$record=$row2[0];
			$qu=$record["qta_unitaria"];
			$qt=$qu*$qr;
			$testoIstruzioni=$testoIstruzioni."(OT ".$record["descrizione"]."  KG".$qt.")";
			$testoIstruzioni=$testoIstruzioni."\n";
		}
	}
	// legge fasi x articolo
	if ($res) {
		$idarticolo=$record["idarticolo"];

		$where="idarticolo = '".$idarticolo."'";
		$ordinamento="idfase asc ";
		$raggruppamento="";
		$campi="TOP (1) idarticolo,idfase,idrisorsa,tempo_attrezzaggio_h,tempo_ciclo_s";
		$tabelleJoin="fasi_x_articolo";
		$row3=$dbGMN->db_get($dbhandle,$tabelleJoin,$campi,$where,$pag,1,$ordinamento,"",$npag);
		$res=false;
		if (count($row2)>0) {
			$res=true;
			for ($i=0;$i<count($row3);$i++) {
				$record=$row3[$i];
				$ta=$record["tempo_attrezzaggio_h"];
				$tc=$record["tempo_ciclo_s"];
				if ($ta!== null && $ta>0) {
					$testoIstruzioni=$testoIstruzioni."(TA ".$ta."H , TC ".$tc."S)";
					$testoIstruzioni=$testoIstruzioni."\n";
				}
			}
		}
	}

	return $testoIstruzioni;
}
?>
