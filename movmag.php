<?php

include_once('dbGEMINI.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="movimenti_magazzino";
	$schema=$schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoPKs=$schema["PK"];
	$campo=array();
	$i=0;
	foreach($campiTab as $x => $x_value) {
		//echo "Key=" . $x . ", Value=" . $x_value;
		$campo[$i]=$x;
		if ($x=="registrazione")
			$indiceTk=$i;
		$i++;
	}	
	$dbhandle=$dbhData;
	
	$valoreCampo = array();
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

	error_log("movmag.php"." ".$oper);
	
	$row="";
	$pag=1;
	switch ($oper){
		case "importMovMag":
			if(isset($_POST["op"]))
			{
					$nomeCampo="nomeFileExport";
					$nomeFileExport=$_POST[$nomeCampo];
			}
			else
			{
				$nomeCampo="nomeFileExport";
				$nomeFileExport=$_GET[$nomeCampo];
			}

			$tabellaDett="movimenti_magazzino_corpo";
			$schemaDett=$schemadb[$tabellaDett];
			$campiTabDett=$schemaDett["campi"];
			$elencoPKs=$schema["PK"];
			$campoDett=array();
			$i=0;
			foreach($campiTabDett as $x => $x_value) {
				//echo "Key=" . $x . ", Value=" . $x_value;
				$campoDett[$i]=$x;
				if ($x=="registrazione")
					$indiceTkDett=$i;
				$i++;
			}	
		
			$res=importMovMag($dbGMN,$dbhandle,$nomeFileExport,$campo,$campoDett);

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
			$campiElenco="registrazione,data_registrazione,causale,descrizione,deposito,annotazioni,numero_documento,data_documento";
			$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$where,$pag,1,"","",$npag);
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
			$campiElenco="registrazione,data_registrazione,causale,descrizione,deposito,annotazioni,numero_documento,data_documento";
			$ordinamento="registrazione desc ";

		error_log("filtro:".$filtro);

            //---$row=$dbGMN->db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
            $res=true;
            $row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,"",$npag);
            if (count($row)>0)
                $res=true;
            else
                $res=false;
		break;
		
		case "listbox":
		$npag=0;

			$campiElenco="registrazione as id,(cast(registrazione as varchar) + ' ' + cast(data_registrazione as varchar) as descr";
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
	
	if ( $oper == "list" || $oper == "listbox" || $oper == "get"  || $oper == "getData" || $oper == "getExt" || $oper == "getIstruzioni")
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

function getMaxNumReg($dbGMN,$dbhandle,&$num)
{
	$campiElenco="max(registrazione) as registrazione";
	$ordinamento="registrazione desc ";
	$tabella="movimenti_magazzino";
	$npag=1;
	$pag=1;
	$res=false;
	$num=0;

	$row=$dbGMN->db_get($dbhandle,$tabella,$campiElenco,"",$pag,1,$ordinamento,"",$npag);
	if (count($row)>0) {
		$res=true;
		$num=$row[0]["registrazione"];
	}
	else
		$res=false;

	return $res;
}

function importMovMag($dbGMN,$dbhandle,$nomeFileExport,$campo,$campoDett)
{
	$causaleCaricoMag="CA2";
	$descrCausale="CA2";
	$numDeposito=2;
	$valoreCampo=array();
	$valoreCampoDett=array();

	$passo=0;
	$nomeFile = ".\\uploads\\".$nomeFileExport;
	foreach(file($nomeFile) as $riga){
		error_log("riga;".$riga);
		$campiRiga=explode("\t",$riga);
		for ($i=0;$i<count($campiRiga);$i++)
		{
			$campiRiga[$i]=str_replace("\r\n","",$campiRiga[$i]);
		}
		switch($passo) {
			case 0: // criteri
				if (!(strpos($campiRiga[0],"Criteri ") === false)) {
					$passo=1;
				}
				break;
			case 1: // aggregazione
				if (!(strpos($campiRiga[0],"Tipo di ") === false)) {
					$passo=2;
				}
				break;
			case 2: // intestazioni
				$iProduct=-1;
				$iQta=-1;
				$iData=-1;

				$nc=count($campiRiga);
				
				for ($i=0;$i < $nc;$i++) {
					error_log("ciclo for: $i,$campiRiga[$i]");
					
					
					if ($iProduct==-1 && !(strpos($campiRiga[$i],"articolo") === false)) {
						$iProduct=$i;
						error_log("ciclo for: trovato articolo");
					}
					if ($iQta == -1 && !(strpos($campiRiga[$i],"pieces") === false)) {
						$iQta=$i;
						error_log("ciclo for: trovato pieces");
					}
					if ($iData == -1 && !(strpos($campiRiga[$i],"date") === false)) {
						$iData=$i;
						error_log("ciclo for: trovato date");
					}
				}
				$passo=3;
				break;
			case 3: // dati
				// ottieni nuovo num. registrazione
				$res=getMaxNumReg($dbGMN,$dbhandle,$numreg);
				if ($res) {
					$numreg++;
				// inserisci record movimenti
				for ($i=0;$i<count($campo);$i++)
				{
					switch($campo[$i]){
						case "registrazione":
							$valoreCampo[$i]=$numreg;
						break;
						case "data_registrazione":
							$valoreCampo[$i]=date("Y-m-d H:i:s");//$campiRiga[$iData];
						break;
						case "causale":
							$valoreCampo[$i]=$causaleCaricoMag;
						break;
						case "descrizione":
							$valoreCampo[$i]=$descrCausale;
						break;
						case "deposito":
							$valoreCampo[$i]=$numDeposito;
						break;
						case "annotazioni":
							$valoreCampo[$i]='';
						break;
						case "numero_documento":
							$valoreCampo[$i]=$numreg;
						break;
						case "data_documento":
							$valoreCampo[$i]=$campiRiga[$iData];
						break;
					}
				}
				$tabella="movimenti_magazzino";
				$res = $dbGMN->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
				
				// inserisci record movimenti dettaglio
				error_log("campidett:".json_encode($campoDett));
				
				for ($i=0;$i<count($campoDett);$i++)
				{
					switch($campoDett[$i]){
						case "registrazione":
							$valoreCampoDett[$i]=$numreg;
						break;
						case "riga":
							$valoreCampoDett[$i]=0;
						break;
						case "articolo":
							$valoreCampoDett[$i]=$campiRiga[$iProduct];
						break;
						case "quantita":
							$valoreCampoDett[$i]=$campiRiga[$iQta];
						break;
						case "esercizio":
							$valoreCampoDett[$i]=substr($campiRiga[$iData],0,4);
						break;
					}
				}

				$tabellaDett="movimenti_magazzino_corpo";
				$res = $dbGMN->db_insert_valori($dbhandle,$tabellaDett,$valoreCampoDett,$campoDett);
			}
		}
	}


}
?>
