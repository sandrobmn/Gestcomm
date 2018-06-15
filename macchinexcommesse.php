<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="macchinexcommessa";
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
	$row="";
	$pag=1;
	if(isset($_POST["op"]))
	{
		$oper=$_POST["op"];
	}
	else
	{
		$oper=$_GET["op"];
	}
	//echo "oper:".$oper;
	
	error_log("macchinexcommesse.php"." ".$oper);

	switch ($oper){
		case "insertOP":
				$campoOP=array(
					"idop","idordine_cliente","unique_id","idarticolo","qta_richiesta","data",
					"data_consegna_prevista","CNCprogram","status"
				);
				$campoCommessa=array(
					"id_order","descrizione","data_inizio","data_fine","prodotto","qta"
				);

				$valorecampoOP=array();
				$valoreCampoCommessa=array();

				if(isset($_POST["op"]))
				{
					for ($i=0;$i<count($campoOP);$i++)
					{
						$nomeCampo=$campoOP[$i];
						$valoreCampoOP[$nomeCampo]=$_POST[$nomeCampo];
					}	
				}
				else
				{
					for ($i=0;$i<count($campo);$i++)
					{
						$nomeCampo=$campoOP[$i];
						$valoreCampoOP[$nomeCampo]=$_GET[$nomeCampo];
					}	
				}
				// valorizza i campi di commesse e macchinexcommesse
				$res=valorizzaCommessa($dbTH,$dbhandle,$valoreCampoOP,$campoCommessa,$valoreCampoCommessa);
				if ($res) {
					$res=inserisciCommessa($dbTH,$dbhandle,$tabella,$valoreCampoCommessa,$campoCommessa);
					if ($res)
						$res=valorizzaMacchinexcommessa($valoreCampoCommessa[0],$valoreCampoOP,$campo,$valoreCampo);

				}
			
				if ($res)
				{
					$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
				}
				if($res)
					$msg=$oper." riuscito";
				else
					$msg=$oper." fallito";

		break;

		case "delete":
		case "insert":
		case "edit":
		
			if(isset($_POST["op"]))
			{
				//error_log("POST");
				for ($i=0;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valoreCampo[$i]=$_POST[$nomeCampo];
				}	
			}
			else
			{
				//error_log("GET");
				for ($i=0;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valoreCampo[$i]=$_GET[$nomeCampo];
				}	
			}
			if ($oper == "insert")
			{
				$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
			}
			if ($oper == "edit")
			{
				$res = $dbTH->db_update_valori($dbhandle,$tabella,$valoreCampo,$campo);
			}
			if ($oper == "delete")
			{
				$res = $dbTH->db_delete_valori($dbhandle,$tabella,$valoreCampo,$campo);
			}
			if($res)
				$msg=$oper." riuscito";
			else
				$msg=$oper." fallito";
			
		break;
		
		case "get":
		$npag=0;

			$nomeCampo1=$campo[0];
			$nomeCampo2=$campo[1];
			if(isset($_POST["op"]))
			{
				$valoreId1=$_POST[$nomeCampo1];//first, last,nextfw,nextbw
				$valoreId2=$_POST[$nomeCampo2];//first, last,nextfw,nextbw
			}
			else
			{
				$valoreId1=$_GET[$nomeCampo1];
				$valoreId2=$_GET[$nomeCampo2];
			}
				
			$where=$nomeCampo1." = '".$valoreId1."'";
			$where=$where." and ".$nomeCampo2." = '".$valoreId2."'";
			$msg= $oper."(".$where.")";
			$res=false;
			$pag=1;
			$row=$dbTH->db_get($dbhandle,$tabella,"*",$where,$pag,1);
			$res=true;
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
		case "getListxOP":
			$pag=1;
			$npag=1;
			if(isset($_POST["op"]))
			{
				if(isset($_POST["pag"])) $pag=$_POST["pag"];//first, last,nextfw,nextbw
				if(isset($_POST["npag"]))$npag=$_POST["npag"];//first, last,nextfw,nextbw
				//$nrighexpag=$_POST["nrighe"];//n. di righe x pagina
				$filtro=$_POST["filtro"];//id_order > 'C2'
				$sort=$_POST["ordinamento"];//first, last,nextfw,nextbw
			}
			else
			{
				if(isset($_GET["pag"])) $pag=$_GET["pag"];
				if(isset($_GET["npag"])) $npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
				$sort=$_GET["ordinamento"];//first, last,nextfw,nextbw
			}
			$filtro999="(substr(product,1,11) <> 'AZZERAMENTO')";
			if ($filtro>"") {
				$filtro = $filtro." and ".$filtro999;
			}
			else {
				$filtro=$filtro999;
			}
		//error_log("pag:".$pag." npag:".$npag." filtro:".$filtro." ordinamento:".$sort);

			//$campiElenco="mc.id_order,mc.unique_id, m.posizione, mc.product,    mc.requested_pieces, mc.CNCprogram, mc.planned_start_date, p.order_start_date,  p.order_start_pieces, mc.planned_end_date, p.order_end_date,  p.total_pieces"; 
			//$tabelleJoin="macchinexcommessa as mc left outer join pcs as p  on  mc.unique_id=p.unique_id and mc.CNCprogram=p.order_start_program and mc.planned_start_date <= p.order_start_date and mc.planned_end_date >= p.order_end_date left outer join macchina as m on mc.unique_id=m.unique_id";
			$campiElenco="mc.id_order,mc.unique_id, m.posizione, mc.product,    mc.requested_pieces, mc.CNCprogram, mc.planned_start_date, mc.planned_end_date"; 
			$tabelleJoin="macchinexcommessa as mc  left outer join macchina as m on mc.unique_id=m.unique_id";
			if ($sort>"")
				$ordinamento=$sort;
			else
				$ordinamento="mc.planned_start_date desc , mc.id_order asc ,mc.unique_id asc ";
			//$filtro = str_replace("id_order", "mc.id_order", $filtro);
			//$row=db_select($dbhandle,$tabelleJoin,$campiElenco,$pag,$nrighexpag);
			if ($oper =="getListxOP") 
			{ 
				$campiElenco=str_replace("mc.unique_id,","",$campiElenco);
				$campiElenco=str_replace("mc.CNCprogram,","",$campiElenco);
			}
			$row=$dbTH->db_get($dbhandle,$tabelleJoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	
	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listbox" || $oper =="getListxOP" || $oper == "get" || $oper =="getCNCcounter")
		$risposta=preparaRisposta($res,json_encode($row),$oper,$pag,$npag);
	else
		$risposta=preparaRisposta($res,$msg,$oper);
	
	echo json_encode($risposta);
	$dbTH->db_close($dbhandle);
	}
	else 
	{
		$dbTH->db_close($dbhandle);

		// filename for download
		$nomeReport = "Report_mxc";

		exportReport($nomeReport,$filtro,$row);
	}

function generaCodiceCommessaOP($dbTH,$dbhandle,$di,$idopEs,$idordine_cliente)
{
	// a partire dal codice op (aaaa-xxxxx) crea il codice commessa base (OP-aaaa-xxxxx)
	// cerca un record commessa con il progressivo più alto
	// incrementa il progressivo e compone il codice (OP-aaaa-xxxxx-ppp)

	//$anno=substr($di,0,4);
	
	//$id_order="OP"."-".$anno."-".str_pad($idop, 5, '0', STR_PAD_LEFT)."-".$idordine_cliente;
	$id=explode("-",$idopEs);
	$anno=$id[0];
	$idop=$id[1];
	$id_order="OP"."-".$anno."-".str_pad($idop, 5, '0', STR_PAD_LEFT);

	$nrighexpag=1;
	$npag=1;
	$tabella="commessa";
	$filtro="id_order like '$id_order%'";
	$campi="max(id_order) as id_order";
	$ordinamento="";
	$raggruppamento="id_order";
	$row=$dbTH->db_get($dbhandle,$tabella,$campi,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
	$maxIdOrder="OP-0000-00000-0";
	if (count($row)>0) {
		$max=$row[0]["id_order"];
		if ($max != null)
			$maxIdOrder=$max;

	}
	
	error_log("maxIdOrder:".$maxIdOrder);

	$parti=explode("-",$maxIdOrder);
	$prog=$parti[3]+1;

	$id_order = $id_order."-".str_pad($prog, 3, '0', STR_PAD_LEFT);

	error_log("IdOrder:".$id_order);

	return $id_order;
}

function valorizzaCommessa($dbTH,$dbhandle,$valoreCampoOP,$nomeCampoCommessa,&$valoreCampoCommessa)
{
	$res=true;
	// valorizza commessa
	
	/*
	$di=$valoreCampoOP["data"];
	$anno=substr($di,0,4);
	
	$id_order="OP"."-".$anno."-".$valoreCampoOP["idop"]."-".$valoreCampoOP["idordine_cliente"];
	*/
	$id_order=generaCodiceCommessaOP($dbTH,$dbhandle,$valoreCampoOP["data"],$valoreCampoOP["idop"],$valoreCampoOP["idordine_cliente"]);

	$prodotto_OP=$valoreCampoOP["idarticolo"];
	$prodotto=explode(" ",$valoreCampoOP["idarticolo"]);
	$i=count($prodotto);
	if ($i<2)
		$prodotto=explode("-",$valoreCampoOP["idarticolo"]);
	$partnumber=$prodotto[$i-1];
	
	for ($i=0;$i<count($nomeCampoCommessa);$i++)
	{
		switch ($nomeCampoCommessa[$i]){
			case "id_order":
				$valoreCampoCommessa[$i]=$id_order;
			break;
			case "descrizione":
				$valoreCampoCommessa[$i]=$prodotto_OP;
			break;
			case "data_inizio":
				$valoreCampoCommessa[$i]=$valoreCampoOP["data"];
			break;
			case "data_fine":
			$valoreCampoCommessa[$i]=$valoreCampoOP["data_consegna_prevista"];
			break;
			case "prodotto":
			$valoreCampoCommessa[$i]=$partnumber;
			break;
			case "qta":
			$valoreCampoCommessa[$i]=$valoreCampoOP["qta_richiesta"];
			break;
		}
	}
	return $res;
}

function inserisciCommessa($dbTH,$dbhandle,$tabella,$valoreCampoCommessa,$nomeCampoCommessa){
	// genera record commessa
	$tabella="commessa";
	
	$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoreCampoCommessa,$nomeCampoCommessa);

	return $res;
}

function valorizzaMacchinexCommessa($id_order,$valoreCampoOP,$nomeCampoMxC,&$valoreCampoMxC)
{
	$res=true;
	// valorizza commessa
	/*
	$di=$valoreCampoOP["data"];
	$anno=substr($di,0,4);
	$id_order="OP"."-".$anno."-".$valoreCampoOP["idop"]."-".$valoreCampoOP["idordine_cliente"];
	*/
	//$id_order=generaCodiceCommessaOP($dbhandle,$valoreCampoOP["data"],$valoreCampoOP["idop"],$valoreCampoOP["idordine_cliente"]);

	$prodotto=explode(" ",$valoreCampoOP["idarticolo"]);
	$i=count($prodotto);
	$partnumber=$prodotto[$i-1];
	
	error_log("valorizzaMacchinexCommessa - ".json_encode($valoreCampoOP));

	for ($i=0;$i<count($nomeCampoMxC);$i++)
	{
		switch ($nomeCampoMxC[$i]){
			case "id_order":
				$valoreCampoMxC[$i]=$id_order;
			break;
			case "unique_id": 
			$valoreCampoMxC[$i]=$valoreCampoOP["unique_id"];
			break;
			case "data_inizio":
				$valoreCampoMxC[$i]=$valoreCampoOP["data"];
			break;
			case "planned_start_date":
			$valoreCampoMxC[$i]=$valoreCampoOP["data"];
			break;
			case "planned_end_date":
			$valoreCampoMxC[$i]=$valoreCampoOP["data_consegna_prevista"];
			break;
			case "product":
			$valoreCampoMxC[$i]=$partnumber;
			break;
			case "CNCprogram":
			$valoreCampoMxC[$i]=$valoreCampoOP["CNCprogram"];
			break;
			case "requested_pieces":
			$valoreCampoMxC[$i]=$valoreCampoOP["qta_richiesta"];
			break;
			case "status":
			$valoreCampoMxC[$i]=$valoreCampoOP["status"];
			if ($valoreCampoMxC[$i]=='')
				$valoreCampoMxC[$i]=0;
			break;
		}
	}
	error_log("valorizzaMacchinexCommessa - ".json_encode($valoreCampoMxC));

	return $res;
}		

?>
