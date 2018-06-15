<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	//$tabella="macchinexcommessa";
	//$campo=$campidb[$tabella];

	$dbhandle=$dbhData;
	
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
	
	error_log("produzione.php"." ".$oper);

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
			$row=db_get($dbhandle,$tabella,"*",$where,1,1);
		*/
		
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
				$sort=$_POST["ordinamento"];//id_order > 'C2'
			}
			else
			{
				$pag=$_GET["pag"];
				$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
				$sort=$_GET["ordinamento"];//id_order > 'C2'
			}
			$filtro999="(substr(product,1,11) <> 'AZZERAMENTO')";
			if ($filtro>"") {
				$filtro = $filtro." and ".$filtro999;
			}
			else {
				$filtro=$filtro999;
			}
	
		error_log("filtro:".$filtro);

			$campiElenco="mc.id_order,m.posizione,mc.planned_start_date, mc.planned_end_date, mc.product, mc.requested_pieces,  min(p.order_start_date) as mind, max(p.order_end_date) as maxd, mc.CNCprogram, sum( (p.total_pieces - p.order_start_pieces)) as totprod"; 
			$tabelleJoin="macchinexcommessa as mc left outer join pcs as p  on  mc.unique_id=p.unique_id and mc.product=p.part_number and mc.planned_start_date <= p.order_start_date and mc.planned_end_date >= p.order_end_date left outer join macchina as m on mc.unique_id=m.unique_id";
			$raggruppamento="mc.id_order, m.unique_id";
			$ordinamento="mc.id_order desc , m.unique_id asc ";
			
			if ($sort>"")
				$ordinamento=$sort;

			//$filtro = str_replace("id_order", "mc.id_order", $filtro);
			//$row=db_select($dbhandle,$tabelleJoin,$campiElenco,$pag,$nrighexpag);
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
		
		$nomeReport="Report_produzione_totale_x_OP";
		exportReport($nomeReport,$filtro,$row);
		
	}
	
	
	
	
	
?>
