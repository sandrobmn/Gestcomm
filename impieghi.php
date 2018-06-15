<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="hours";
	//$campo=$campidb[$tabella];
	
	$schema=$schemadb[$tabella];
	$campiTabella=$schema["campi"];
	$elencoPKs=$schema["PK"];
	$pos = strpos($elencoPKs, ",");
	$pks=array();
	if ($pos === false)
	{
		$pks[0]=$elencoPKs; $pks[1]="";
	}
	else
	{
		$pks[0]=substr($elencoPKs,0,$pos); $pks[1]=substr($elencoPKs,$pos+1);
	}	
	//error_log("tabella:".$tabella." :".$pks[0]." :".$pks[1]);
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
	

	switch ($oper){
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
				$tipoAggregazione=$_POST["tipoAggregazione"];//id_order > 'C2'
				//$tipoRappresentazione=$_POST["tipoRappresentazione"];//id_order > 'C2'
				$sort=$_POST["ordinamento"];//first, last,nextfw,nextbw
			}
			else
			{
				$pag=$_GET["pag"];
				$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
				$tipoAggregazione=$_GET["tipoAggregazione"];//id_order > 'C2'
				//$tipoRappresentazione=$_GET["tipoRappresentazione"];//id_order > 'C2'
				$sort=$_GET["ordinamento"];//first, last,nextfw,nextbw
			}
			$filtro=str_replace("start_date", "date", $filtro);
			$filtro=str_replace("end_date", "date", $filtro);
			error_log("filtro:".$filtro);
			
			$campiElencoTesta="p.unique_id, m.posizione,  (date || ' ' || substr(('00' || hour),-2,2) ||':00:00Z') as datehour"; 
			$campiElencoCoda="sum(planned_production_time), sum(operating_time), sum(ideal_operating_time),sum(total_pieces) as totalpieces, sum(active+idle+alert) as total, sum(active),sum(active)/sum(active+idle+alert) as pactive,  sum(idle), sum(idle)/sum(active+idle+alert) as pidle, sum(alert), sum(alert)/sum(active+idle+alert) as palert, sum(production), sum(setup), sum(teardown), sum(maintenance), sum(process_development)"; 
			$tabelleJoin="days as p left outer join macchina as m on p.unique_id=m.unique_id ";
			$ordinamento="m.posizione asc ,datehour desc ";
			$raggruppamento="";
			switch($tipoAggregazione){
				case "H":
					$ordinamento="m.posizione asc ,datehour desc ";
					$raggruppamento="p.unique_id,date,hour";
					$campiElencoTesta="p.unique_id, m.posizione,  (date || ' ' || substr(('00' || hour),-2,2) ||':00:00Z') as datehour"; 
					break;
				case "G":
					$campiElencoTesta="p.unique_id, m.posizione,  date as datehour"; 
					$raggruppamento="p.unique_id,datehour";
					$ordinamento="m.posizione asc ,datehour desc ";
					break;
				case "M":
					$campiElencoTesta="p.unique_id, m.posizione, substr(date,1,7) as datehour"; 
					$raggruppamento="p.unique_id, datehour";
					$ordinamento="m.posizione asc , datehour desc ";
					break;
				case "A":
					$campiElencoTesta="p.unique_id, m.posizione, substr(date,1,4) as datehour"; 
					$raggruppamento="p.unique_id, datehour";
					$ordinamento="m.posizione asc , datehour desc ";
					break;
			}
			if ($sort>"")
				$ordinamento=$sort;
		

			$campiElenco=$campiElencoTesta.",".$campiElencoCoda;
			//$row=$dbTH->db_select($dbhandle,$tabelleJoin,$campiElenco,$pag,$nrighexpag);
			$row=$dbTH->db_get($dbhandle,$tabelleJoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	
	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listbox" || $oper=="get")
		$risposta=preparaRisposta($res,json_encode($row),$oper,$pag,$npag);
	else
		$risposta=preparaRisposta($res,$msg,$oper);
	
	echo json_encode($risposta);
	$dbTH->db_close($dbhandle);
	}
	else 
	{
		$dbTH->db_close($dbhandle);


		$nomeReport="Report_produzione_x_macchina_oee";
		exportReport($nomeReport,$filtro,$row,$tipoAggregazione,"");
	}
	
?>
