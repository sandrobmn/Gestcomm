<?php

include_once('dbGEMINI.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="materiali_x_articolo";
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

	error_log("materialixarticolo.php"." ".$oper);
	
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
		$npag=0;

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
			$row=$dbGMN->db_get($dbhandle,$tabella,"*",$where,$pag,1);
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

			}
			else
			{
				$pag=$_GET["pag"];
				$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
			}
			$tabellejoin="materiali_x_articolo as mxa join materiali as m on mxa.idmateriale=m.idmateriale";
			$campiElenco="idarticolo,mxa.idmateriale,descrizione,qta_unitaria";
			$ordinamento="idarticolo asc ,idmateriale asc ";

		error_log("filtro:".$filtro);

            //---$row=$dbGMN->db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
            $res=true;
            $row=$dbGMN->db_get($dbhandle,$tabellejoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
            if (count($row)>0)
                $res=true;
            else
                $res=false;
		break;
		
		case "listQtaRichiesta":
			if(isset($_POST["op"]))
			{
				$pag=$_POST["pag"];//first, last,nextfw,nextbw
				$npag=$_POST["npag"];//first, last,nextfw,nextbw
				//$nrighexpag=$_POST["nrighe"];//n. di righe x pagina
				$filtro=$_POST["filtro"];//id_order > 'C2'
				$qta=$_POST["qta_richiesta"];//id_order > 'C2'

			}
			else
			{
				$pag=$_GET["pag"];
				$npag=$_GET["npag"];
				//$nrighexpag=$_GET["nrighe"];//n. di righe x pagina
				$filtro=$_GET["filtro"];//id_order > 'C2'
				$qta=$_GET["qta_richiesta"];//id_order > 'C2'
			}
			$tabellejoin="materiali_x_articolo as mxa join materiali as m on mxa.idmateriale=m.idmateriale";
			$campiElenco="idarticolo,mxa.idmateriale,descrizione,qta_unitaria,(qta_unitaria*".$qta.") as qta_richiesta";
			$ordinamento="idarticolo asc ,idmateriale asc ";

		error_log("filtro:".$filtro);

            //---$row=db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
            $res=true;
            $row=$dbGMN->db_get($dbhandle,$tabellejoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
            if (count($row)>0)
                $res=true;
            else
				$res=false;
			error_log("$oper : db_get:".$res);
		break;
		
		case "listbox":
		$npag=0;

			$campiElenco="idmateriale as id,idmateriale as descr";
			$ordinamento="descr desc ";
			$pag=1;
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,"",$pag,1000,$ordinamento);
			
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	
	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listQtaRichiesta"  || $oper == "listbox" || $oper == "get")
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
		$nomeReport = "Report_materialexarticolo";

		exportReport($nomeReport,$filtro,$row);
	}
	
?>
