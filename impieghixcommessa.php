<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	//$tabella="macchinexcommessa";
	//$campo=$campidb[$tabella];

	$dbhData=$dbhData;
	
	$valoreCampo = array(6);
	$oper="";
	$row="";
	
	if(isset($_POST["op"]))
	{
		$oper=$_POST["op"];
	}
	else
	{
		$oper=$_GET["op"];
	}
	//echo "oper:".$oper;
	
	error_log("impieghixcommessa.php"." ".$oper);

	switch ($oper){
		
		case "get":
		/*
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
			$row=db_get($dbhData,$tabella,"*",$where,1,1);
		*/
		
		break;
		
		case "updateCommesse":
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
			error_log("filtro:".$filtro);
			$filtro = str_replace("posizione", "mc.posizione", $filtro);
				// esegue aggiornamento commesse
				aggiornaCommesse($dbhData,$filtro,$dbTH);
			$res=true;
			$msg="";
		break;

		case "updateList":
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
			$filtro999="(substr(product,1,11) <> 'AZZERAMENTO')";
			if ($filtro>"") {
				$filtro = $filtro." and ".$filtro999;
			}
			else {
				$filtro=$filtro999;
			}

		error_log("filtro:".$filtro);
			$filtro = str_replace("posizione", "mc.posizione", $filtro);
			
			if ($oper == "updateList")
			{
				// esegue aggiornamento commesse
				aggiornaCommesse($dbhData,$filtro,$dbTH);
				// restituisce la lista aggiornata
				if ($oper == "updateList")
					$oper="list";
			}

			$query="drop table if exists temp1";
			$res = $dbTH->db_execStatement($dbhData,$query);

			$campiElenco="mc.id_order as id_order,m.unique_id as unique_id,posizione,planned_start_date, planned_end_date, product, requested_pieces";
			$campiElenco=$campiElenco.",  min(p.order_start_date) as mind, max(p.order_end_date) as maxd, CNCprogram";
			$campiElenco=$campiElenco.", sum( (p.total_pieces - p.order_start_pieces)) as totprod"; 
			$tabelleJoin="macchinexcommessa as mc left outer join pcs as p  on  mc.unique_id=p.unique_id and mc.product=p.part_number and mc.planned_start_date <= p.order_start_date and mc.planned_end_date >= p.order_end_date left outer join macchina as m on mc.unique_id=m.unique_id";
			$ordinamento="mc.id_order desc , m.unique_id asc ";
			$raggruppamento = $ordinamento;
			$raggruppamento = str_replace(" asc ", "", $raggruppamento);
			$raggruppamento = str_replace(" desc ", "", $raggruppamento);
			//$filtro = str_replace("id_order", "mc.id_order", $filtro);
			//$row=db_select($dbhData,$tabelleJoin,$campiElenco,$pag,$nrighexpag);

			$query="create table temp1 as ";
			$query=$query."select ".$campiElenco." from ".$tabelleJoin;
			$query=$query." group by ".$raggruppamento." "." order by ".$ordinamento." ";
			$res= $dbTH->db_execStatement($dbhData,$query);

			$campiElenco="id_order, product, mc.posizione, mind,maxd,  sum(planned_production_time) as tplanned_production_time, sum(operating_time) as toperating_time, sum(ideal_operating_time) as tideal_operating_time";
			$campiElenco=$campiElenco.",totprod, sum(total_pieces) as totalpieces, sum(active+idle+alert) as total, sum(active) as tactive,sum(active)/sum(active+idle+alert) as pactive";
			$campiElenco=$campiElenco.",  sum(idle) as tidle, sum(idle)/sum(active+idle+alert) as pidle, sum(alert) as talert, sum(alert)/sum(active+idle+alert) as palert";
			$campiElenco=$campiElenco.", sum(production), sum(setup), sum(teardown), sum(maintenance), sum(process_development)";
			$tabelleJoin="days as d join temp1 as mc on d.unique_id=mc.unique_id ";
			$tabelleJoin=$tabelleJoin." and substr((date || ' ' || substr(('00' || hour),-2,2)),0,13)>=substr(mind,0,13)";
			$tabelleJoin=$tabelleJoin." and substr((date || ' ' || substr(('00' || hour),-2,2)),0,13)<=substr(maxd,0,13)";
			$tabelleJoin=$tabelleJoin."join macchina as m on d.unique_id=m.unique_id";
			$ordinamento="mind desc ,d.unique_id asc ,product desc  ";

			$raggruppamento = $ordinamento;
			$raggruppamento = str_replace(" asc ", "", $raggruppamento);
			$raggruppamento = str_replace(" desc ", "", $raggruppamento);

			if ($sort>""){
				// se indicato posizione prefissare con mc.
				$sort=str_replace("posizione","mc.posizione",$sort);
				$ordinamento=$sort;
			}

			$row=$dbTH->db_get($dbhData,$tabelleJoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
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
	$dbTH->db_close($dbhData);
	}
	else 
	{
		$dbTH->db_close($dbhData);
		
		$nomeReport="Report_produzione_totale_x_OP_Oee";
		exportReport($nomeReport,$filtro,$row);
		
	}
	
	function aggiornaCommesse($dbhandle,$filtro,$dbTH)
	{
		$campiCommessa=array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
		$valoriCampiCommessa=array(6);
		$campiMacchinexCommessa=array("id_order","unique_id","planned_start_date","planned_end_date","product","requested_pieces","CNCprogram","status");
		$valoriCampiMacchinexCommessa=array(8);

		// crea elenco lotti ordinato asc su data inizio
		$elencoPcs=elencoLotti($dbTH,$dbhandle,$filtro);
		
		// per ogni lotto dell'elenco:
		$numRecPcs=count($elencoPcs);
        for ($k=0;$k<$numRecPcs;$k++)
        {
            $pcs=$elencoPcs[$k];
			
			$macchina=$pcs['unique_id'];
			$pn=$pcs['part_number'];
			$di=$pcs['order_start_date'];
			$df=$pcs['order_end_date'];
			$qta=$pcs['total_pieces']-$pcs['order_start_pieces'];
			$status=$pcs['status'];
			$pgm=$pcs['order_start_program'];
			
			//    cerca commessa che lo comprenda
			$order=cercaCommessaxLotto($dbTH,$dbhandle,$macchina,$pn,$di,$df);
			$numRecOrder=count($order);
			//        se commessa non trovata
			if ($numRecOrder == 0)
			{
				//  crea commessa e macchinexcommessa
				$idOrder=generaCodiceCommessa($dbTH,$dbhandle,$di);

				$valoriCampiCommessa[0]=$idOrder;
				$valoriCampiCommessa[1]=$idOrder." ".$pn;
				$valoriCampiCommessa[2]=$di;
				$valoriCampiCommessa[4]=$pn;
				$statusProd=2;
				if ($status == 0)
				{
					// dati fine = data inizio + 30 gg
					//$df=strtotime(date("Y-m-d", strtotime($df)) . " +30 days");
					$df=date("Y-m-d", (strtotime($df)+3600*24*30));
					// qta = 100000
					$qta=100000;

					$statusProd=1;
				}
				$valoriCampiCommessa[3]=$df;
				$valoriCampiCommessa[5]=$qta;

				$tabella="commessa";
				$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoriCampiCommessa,$campiCommessa);

				// crea macchinexcommessa
				
				$valoriCampiMacchinexCommessa[0]=$idOrder;
				$valoriCampiMacchinexCommessa[1]=$macchina;
				$valoriCampiMacchinexCommessa[2]=$di;
				$valoriCampiMacchinexCommessa[3]=$df;
				$valoriCampiMacchinexCommessa[4]=$pn;
				$valoriCampiMacchinexCommessa[5]=$qta;
				$valoriCampiMacchinexCommessa[6]=$pgm;
				$valoriCampiMacchinexCommessa[7]=$statusProd;
				
				$tabella="macchinexcommessa";
				$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoriCampiMacchinexCommessa,$campiMacchinexCommessa);
			}
		}

	}
	
	function elencoLotti($dbTH,$dbhandle,$filtro)
	{
		// crea elenco lotti ordinato asc su data inizio
		$tabella="pcs";
		$campiElenco="*";
		if ($filtro>"")
			$filtro=$filtro." and ";
		$filtro=$filtro."part_number is not null ";
		$pag=1;
		$nrighexpag=1000;
		$ordinamento="order_start_date asc , unique_id asc ";
		$row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento);

		return $row;
	}
	
	function cercaCommessaxLotto($dbTH,$dbhandle,$macchina,$pn,$di,$df)
	{
		//    cerca commessa compatibile con i parametri del lotto
		$tabella="commessa as c join macchinexcommessa as mc on c.id_order=mc.id_order";
		$campiElenco="c.id_order";
		$filtro="mc.unique_id="."'".$macchina."'";
		$filtro=$filtro." and "."planned_start_date <= "."'".$di."'";
		$filtro=$filtro." and "."planned_end_date >= "."'".$df."'";
		$filtro=$filtro." and "."product = "."'".$pn."'";

		$pag=1;
		$nrighexpag=1000;
		$ordinamento="";
		$row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento);

		return $row;
		

	}

	function generaCodiceCommessa($dbTH,$dbhandle,$di)
	{

			$anno=substr($di,0,4);
			$progressivo=1;
			$order=cercaUltimaCommessa($dbTH,$dbhandle,$anno);
			$numRecOrder=count($order);
			if ($numRecOrder > 0)
			{
				$id=$order[0]['last_id_order'];
				if ($id != null)
					$progressivo=(int)substr($id,5,5)+1;
			}
			$idOrder=$anno."-".str_pad($progressivo, 5, '0', STR_PAD_LEFT);

			return $idOrder;
	}

	function cercaUltimaCommessa($dbTH,$dbhandle,$anno)
	{
		//    cerca ultima commessa
		$tabella="commessa";
		$campiElenco="max(id_order) as last_id_order";
		$filtro="id_order like "."'".$anno."-%"."'";

		$pag=1;
		$nrighexpag=1000;
		$ordinamento="";
		$row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento);

		return $row;
		

	}

	
	
?>
