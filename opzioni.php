<?php

include_once('db.php');
include_once 'exportX.php';
include_once 'rwini.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="opzioni";
	$campo=array("id_opzioni","nrighexpag");
	
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

	error_log("opzioni.php"." ".$oper);
	
	$row="";
	switch ($oper){
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
			$row= array(); $valori=array();
			$filename=$filenamePref;//"gestcomm.ini";
			$sezione=$valoreCampo[0]; // nome sezione
			
			// legge l'intera sezione'
			$valoriOld = read_ini_section($filename,$sezione);
			$valori=$valoriOld[0];
			// aggiorna i valori editati		
				for ($i=1;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valori[$nomeCampo]=$valoreCampo[$i];
					//error_log($nomeCampo."=".$valoreCampo[$i]);
				}	
			
			$row[$valoreCampo[0]]=$valori;
			$res = write_ini_section($filename,$row);
			
			$pag=1;
			
			if($res)
				$msg=$oper." riuscito";
			else
				$msg=$oper." fallito";
		break;
		
		case "get":
			$nomeCampo=$campo[0];
			$pag=1;
			if(isset($_POST["op"]))
			{
				$valoreId=$_POST[$nomeCampo];//first, last,nextfw,nextbw
			}
			else
			{
				$valoreId=$_GET[$nomeCampo];
			}
			
			$filename=$filenamePref;//"gestcomm.ini";
			$sezione=$valoreId;
			
			$row = read_ini_section($filename,$sezione);
			
			$res=true;

			//$resultArray = parse_ini_file ($filename,true );

			//foreach($resultArray as $key => $innerArray){
			//	if ($key=$valoreId) {
			//		$row=Array();
			//		$row[0]=$innerArray;
					//foreach($innerArray as $innerRow => $value){
					//	echo $innerRow."=".$value . "<br/>";
					//}
			//	}
			//}
			
		break;
		
		case "report":
		case "list":
		case "listbox":
		case "delete":
		case "insert":
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	
	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listbox" || $oper=="get")
		$risposta=preparaRisposta($res,json_encode($row),$oper,$pag);
	else
		$risposta=preparaRisposta($res,$msg,$oper);
	
	echo json_encode($risposta);
	$dbTH->db_close($dbhData);
	}
	else 
	{
		$dbTH->db_close($dbhandle);

		// filename for download
		$nomeReport = "Report_commesse";

		exportReport($nomeReport,$filtro,$row);
	}
	
	
?>
