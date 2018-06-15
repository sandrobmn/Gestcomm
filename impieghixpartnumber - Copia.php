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
	error_log("impieghixpartnumber.php"." ".$oper);
	

	switch ($oper){
		case "exportMovMag":
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
			$filtro999="(substr(product,1,11) <> 'AZZERAMENTO')";
			if ($filtro>"") {
				$filtro = $filtro." and ".$filtro999;
			}
			else {
				$filtro=$filtro999;
			}

			$filtro=str_replace("start_date", "date", $filtro);
			$filtro=str_replace("end_date", "date", $filtro);
			error_log("filtro:".$filtro);
			
			// days join pcs e macchine
			$tabelleJoin="days as p join macchina as m on p.unique_id=m.unique_id ";
            $tabelleJoin .="join pcs as pc on p.unique_id=pc.unique_id ";
            
            if (strlen($filtro)>0)
                $filtro .= " and ";
			$filtro .="((p.date || ' ' || substr('00' || cast(p.hour as text),-2,2)) >= substr(pc.order_start_date,1,13) and ((p.date || ' ' || substr('00' || cast(p.hour as text),-2,2))) <= substr(pc.order_end_date,1,13))";
			//$filtro .="((p.date ||' ' || p.hour ||':00:00') >= pc.order_start_date and (p.date ||' ' || p.hour ||':00:00') <= pc.order_end_date)";
			
			// pcs join mxc
            $tabelleJoin .="join macchinexcommessa as mxc on pc.unique_id=mxc.unique_id ";
			$tabelleJoin .=" and pc.part_number = mxc.product ";
			$tabelleJoin .="and pc.order_end_date >= mxc.planned_start_date and pc.order_end_date <= mxc.planned_end_date ";

			/* *** 
            if (strlen($filtro)>0)
                $filtro .= " and ";
			$filtro .="(pc.order_start_date >= mxc.planned_start_date and pc.order_end_date <= mxc.planned_end_date)";
			$filtro .= " and ";
			$filtro .="(pc.part_number = mxc.product)";
			
			// mxc join commesse
			$tabelleJoin .="join commessa as c on c.id_order=mxc.id_order ";
			*** */

			// pn join partnumbers
            $tabelleJoin .="left outer join partnumbers as pn on pn.part_number=pc.part_number ";
            

			//$campiElencoTesta="m.posizione, pc.part_number as product,c.descrizione as articolo, c.id_order as op, c.qta, (date || ' ' || substr(('00' || hour),-2,2) ||':00:00Z') as datehour"; 
			$campiElencoTesta="m.posizione, pc.part_number as product,pn.descrizione as articolo,mxc.id_order as op,mxc.requested_pieces as qta";// (date || ' ' || substr(('00' || hour),-2,2) ||':00:00Z') as datehour"; 
			$campiElencoCoda="sum(p.total_pieces) as totalpieces"; 
			if ($oper == "report" || $oper=="exportMovMag")
				$campiElencoCoda="sum(p.total_pieces) as totalpieces"; 

			$ordinamento="m.posizione asc ,datehour asc ";
			$raggruppamento="m.posizione, datehour, product, op";
	
				
			switch($tipoAggregazione){
				case "H":
					//$ordinamento="m.posizione asc ,datehour asc ";
					//$raggruppamento="m.posizione, date,hour, product,op";
					//***$campiElencoTesta="m.posizione, pc.part_number as product,c.descrizione as articolo, c.id_order as op, c.qta,(date || ' ' || substr(('00' || hour),-2,2) ||':00:00Z') as datehour"; 
					$campiElencoTesta .=", (date || ' ' || substr(('00' || hour),-2,2) ||':00:00Z') as datehour"; 
					break;
				case "G":
					//***$campiElencoTesta="m.posizione, pc.part_number as product, c.descrizione as articolo, c.id_order as op, c.qta,date as datehour"; 
					$campiElencoTesta .=", date as datehour"; 
					break;
				case "M":
					//***$campiElencoTesta="m.posizione, pc.part_number as product,c.descrizione as articolo, c.id_order as op, c.qta,substr(date,1,7) as datehour"; 
					$campiElencoTesta .=", substr(date,1,7) as datehour"; 
					break;
				case "A":
					//***$campiElencoTesta="m.posizione, pc.part_number as product,c.descrizione as articolo, c.id_order as op, c.qta,substr(date,1,4) as datehour"; 
					$campiElencoTesta .=", substr(date,1,4) as datehour"; 
					break;
			}
			$campiElenco=$campiElencoTesta.",".$campiElencoCoda;

			if ($sort>"")
				$ordinamento=$sort;
			//error_log("impieghixpartnumber:$campiElenco - $tabelleJoin - $filtro - $ordinamento");
			$row=$dbTH->db_get($dbhandle,$tabelleJoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	if ($oper=="exportMovMag") {
		$nomeReport="Report_produzione_giornaliera"."_" . date('Ymd') ."txt";
		$res=exportReportTxt($nomeReport,$filtro,$row,$tipoAggregazione,"");
		if ($res)
			$msg="operazione completata";
		else
			$msg="--- operazione non riuscita ---";
		/*
		if ($res) {
			$url="localhost";
			$comando="/gestcomm/movmag.php";
			$parametri="op=importMovMag&nomeFileExport=".$nomeReport."";
			//----------$res=richiamaPagina($url,80,$comando,$parametri,$msg);
		}
		*/
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


		$nomeReport="Report_produzione_giornaliera";
		exportReport($nomeReport,$filtro,$row,$tipoAggregazione,"");
	}
	
	function exportReportTxt($nomeReport,$filtro,$row,$tipoAggregazione="",$tipoRappresentazione="")
	{
		
		$fp = fopen(".\\uploads\\".$nomeReport, "w");
		if(!$fp) {
			$res=false;
		}
		else {
			$res=true;
			// output headers so that the file is downloaded rather than displayed
			$flag = false;
			fwrite($fp, "Criteri di selezione:".$filtro . "\r\n");
			if ($tipoAggregazione!="")
				fwrite($fp, "Tipo di aggregazione:".$tipoAggregazione . "\r\n");
			if ($tipoRappresentazione!="")
				fwrite($fp, "Tipo di rappresentazione:".$tipoRappresentazione . "\r\n");
			foreach($row as $record) {
				if(!$flag) {
				// display field/column names as first row
				fwrite($fp, implode("\t", array_keys($record)) . "\r\n");
				$flag = true;
				}
				fwrite($fp, implode("\t", array_values($record)) . "\r\n");
			
			}
		}	
		return $res;
	}

	function richiamaPagina($agent_url,$agent_port,$comando, $parametri,&$risposta){
		$res=false;
	
			// use key 'http' even if you send the request to https://...
			$method="GET";
			
			$parametri="?".$parametri;
			
			$url="http://".$agent_url.":".$agent_port."/".$comando.$parametri;
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => $method,
					'content' => '' //http_build_query($data)
				)
			);
			
			error_log("richiamaPagina:".$url);
	
			try {
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			
			error_log($url.json_encode($result));
			
			$risposta=$result;
			$res=true;
			
			}
			//catch exception
			catch(Exception $e) {
				$risposta= 'Message: ' .$e->getMessage();
				echo $risposta;
				$res=false;
			}
		return $res;
	}
	
	
?>
