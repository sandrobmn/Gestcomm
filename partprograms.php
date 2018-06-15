<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="partprograms";
	$schema=$schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoPKs=$schema["PK"];
	$campo=array();
	$i=0;
	foreach($campiTab as $x => $x_value) {
		//echo "Key=" . $x . ", Value=" . $x_value;
		$campo[$i]=$x;
		if ($x=="part_number")
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

	error_log("partprograms.php"." ".$oper);
	
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
				$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
			if ($oper == "edit")
				$res = $dbTH->db_update_valori($dbhandle,$tabella,$valoreCampo,$campo);
			if ($oper == "delete")
				$res = $dbTH->db_delete_valori($dbhandle,$tabella,$valoreCampo,$campo);
			if($res)
				$msg=$oper." riuscito";
			else
				$msg=$oper." fallito";
		break;
		
		case "get":
			$npag=0;
			$nomeCampo1=$campo[0];
			$nomeCampo2=$campo[1];
			$nomeCampo3=$campo[2];
			$valoreId1='';//first, last,nextfw,nextbw
			$valoreId2='';//first, last,nextfw,nextbw
			$valoreId3='';//first, last,nextfw,nextbw
		if(isset($_POST["op"]))
			{
				if(isset($_POST[$nomeCampo1]))
					$valoreId1=$_POST[$nomeCampo1];//first, last,nextfw,nextbw
				if(isset($_POST[$nomeCampo2]))
					$valoreId2=$_POST[$nomeCampo2];//first, last,nextfw,nextbw
				if(isset($_POST[$nomeCampo3]))
					$valoreId3=$_POST[$nomeCampo3];//first, last,nextfw,nextbw
			}
			else
			{
				if(isset($_GET[$nomeCampo1]))
					$valoreId1=$_GET[$nomeCampo1];
				if(isset($_GET[$nomeCampo2]))
					$valoreId2=$_GET[$nomeCampo2];
				if(isset($_GET[$nomeCampo3]))
					$valoreId3=$_GET[$nomeCampo3];
			}
			
			$where='';
			if ($valoreId1>'')
				$where=$where.$nomeCampo1." = '".$valoreId1."'";
			if ($valoreId2>'' && $where>'')
				$where=$where." and ";
			if ($valoreId2>'')
				$where=$where.$nomeCampo2." = '".$valoreId2."'";
			if ($valoreId3>'' && $where>'')
				$where=$where." and ";
			if ($valoreId3>'')
				$where=$where.$nomeCampo3." = '".$valoreId3."'";
			$msg= $oper."(".$where.")";
			$res=true;
			$pag=1;
			$row=$dbTH->db_get($dbhandle,$tabella,"*",$where,$pag,1);
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
			$campiElenco="*";
			$raggruppamento="part_number,id_tipom,path_n";
			$ordinamento="part_number asc ,id_tipom asc ,path_n asc ";
			if ($sort>"")
				$ordinamento=$sort;


		error_log("filtro:".$filtro);

			//---$row=$dbTH->db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
			$row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
		break;
		
		case "listbox":
			$npag=0;
			$campiElenco="part_number as id,descrizione as descr";
			$ordinamento="part_number asc ";
			$pag=1;
			$row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,"",$pag,1000,$ordinamento);
			
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

		// filename for download
		$nomeReport = "Report_part_programs";

		exportReport($nomeReport,$filtro,$row);
	}
	
?>
