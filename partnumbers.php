<?php

include_once('db.php');
include 'exportX.php';
include_once 'caricaDaExcel.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="partnumbers";
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

	error_log("partnumbers.php"." ".$oper);
	
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
			$nomeCampo=$campo[$indiceTk];
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
			$row=$dbTH->db_get($dbhandle,$tabella,"*",$where,$pag,1);
		break;
		
		case "getCheckAssociazioneArtPN":
			$res=true;
			$pag=0;
            $npag=0;
			$percAvanzamento=checkAssociazioneArtPN();
			$row=array();
			$row[0]["percAvanzamento"]=$percAvanzamento;
			$msg="avanzamento:$percAvanzamento %";
			break;
	    case "associazioneArtPN":
            if(isset($_POST["op"]))
            {
                $esercizio=$_POST["esercizio"];//id_order > 'C2'
                $a_data=$_POST["a_data"];//first, last,nextfw,nextbw
                $da_data=$_POST["da_data"];//first, last,nextfw,nextbw
				$filtro=$_POST["filtro"];//id_order > 'C2'
            }
            else
            {
                $esercizio=$_GET["esercizio"];//id_order > 'C2'
                //--echo "esercizio:".$esercizio;
                $a_data=$_GET["a_data"];//first, last,nextfw,nextbw
                //--echo "a_data:".$a_data;
                $da_data=$_GET["da_data"];//first, last,nextfw,nextbw
                //--echo "da_data:".$da_data;
                $filtro=$_GET["filtro"];//id_order > 'C2'
                //--echo "filtro:".$filtro;
            }
            $row=array();
			$res=true;
			$pag=0;
            $npag=0;
            //$filtro="";
            
            $inputFileName = $pathAvanzamento;//'./uploads/avanzamento_produzione.xlsx';
            
            error_log("esercizio:$esercizio a:$a_data da:$da_data filtro:$filtro");
            
            if ($a_data >= $da_data){
                //associazioneArtPN($dbTH,$dbhandle,$inputFileName,$campo,$esercizio,$a_data,$da_data,$filtro);
                lanciaAssociazioneArtPN($inputFileName,$esercizio,$a_data,$da_data,$filtro);

                $msg="ok";
            }
            else {
                $msg="date errate";
                $res=false;
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
			$campiElenco="*";
			$raggrupamento="part_number";
			$ordinamento="part_number asc ";
			if ($sort>"")
				$ordinamento=$sort;

		error_log("filtro:.$filtro ordinamento:$ordinamento sort:$sort");

			//---$row=$dbTH->db_select($dbhandle,$tabella,"*",$pag,$nrighexpag);
			$row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggrupamento,$npag);
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
	
	if ( $oper == "list" || $oper == "listbox" || $oper=="get" || $oper == "getCheckAssociazioneArtPN")
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
		$nomeReport = "Report_part_numbers";

		exportReport($nomeReport,$filtro,$row);
	}


function checkAssociazioneArtPN(){
	$percAvanzamento=0;
	$filename="c:/php/pgmoutPN.txt";
	$filename='./logs/associazioneArtPN.log';
	if (file_exists($filename)) {
	$fh = fopen($filename,'r');
	while ($line = fgets($fh)) {
		// ricerca una stringa 'associazioneArtPN:record:'..
		$str='associazioneArtPN:record:';
		$i=strpos($line,$str);
		// se trovata estrae il token successivo contenente xxxx / yyyy ...
		//            e calcola la percentuale int( xxxx/yyyy * 100)
		if ($i>0) {
			$j=$i+strlen($str);
			$s=substr($line,$j,strlen($line)-$j);
			$t=explode(":",$s);
			$e=explode("/",$t[0]);
			if (intval($e[1])>0)
				$percAvanzamento=round(intval($e[0])/intval($e[1])*100);
		}
		// ricerca una stringa 'associaArtPN:fine:'
		$str='associaArtPN:fine';
		$i=strpos($line,$str);
		// se trovata restituisce percAvanzamento = 100
		if ($i>0) {
			$percAvanzamento=100;
		}

	}
	fclose($fh);
	}
	else {

	}

	return $percAvanzamento;
}


function lanciaAssociazioneArtPN($inputFileName,$esercizio,$a_data,$da_data,$filtro) {
		$output=array();
		$ret_var=0;

		$filename='./logs/associazioneArtPN.log';
		if (file_exists($filename)) {
			unlink($filename);
		}
	
		$parametri  = "op="."associazioneArtPN";
		$parametri .= " esercizio=".$esercizio;
		$parametri .= " da_data=".$da_data;
		$parametri .= " a_data=".$a_data;
		$parametri .= " filtro=".$filtro;

		$stringaInvocazione="start c:\php\php lanciaAssociazioneArticoliPN.php ".$parametri;
		$stringaInvocazione .=" ^> c:\php\pgmoutPN.txt";
		error_log("invocazione lanciaAssociazioneArtPN: ".$stringaInvocazione);
		//****exec($stringaInvocazione,$output,$ret_var);
		pclose(popen(escapeshellcmd($stringaInvocazione),'r'));
		error_log("esito invocazione lanciaAssociazioneArtPN: ".json_encode($output)."-".$ret_var);
    }


	/*
	per ogni record caricato da foglio Excel avanzamento: 
		cerca il campo commessa (ordine produzione) e il campo part_number (prodotto)
		se commessa != commessa_precedente
		apre il record op(commessa) e ricava articolo
		aggiorna/inserisce il record partnumbers(part-number) con descrizione=articolo
		aggiorna commessa_precedente = commessa
*/
function associazioneArtPN($dbTH,$dbhandle,$inputFileName,$campo,$esercizio,$a_data,$da_data,$filtro)
	{
		error_log("associazioneArtPN:$inputFileName,$esercizio,$da_data,$a_data,$filtro");
	
		//$inputFileName = './uploads/avanzamento_produzione.xlsx';
		
		// ricava macchinaRichiesta da filtro
		$macchinaRichiesta="";
		$partiFiltro=explode("and",$filtro);
		if ($filtro>""){
			for ($i=0;$i<count($partiFiltro);$i++){
				$parti=explode("=",$partiFiltro[$i]);
				
				//error_log("avanzamentoNC-parti:$parti[0]-$parti[1]-");
				if (trim($parti[0]) == "posizione") {
					$partiMacchina=explode("'",$parti[1]);
					$macchinaRichiesta=$partiMacchina[1];
				}
			}
		}
		// instanzia un oggetto DbGemini per accesso a op
		$dbGMN =new DbGemini();
	  
		$dbhandleGMN = $dbGMN->getDbhData();
		$schemadbGMN=$dbGMN->getSchemadb();
		//error_log("avanzamentoNC: filtro:$filtro , $macchinaRichiesta");

		$data=date('Y-m-d', strtotime($da_data. ' - 1 days'));
		//do {
			$data=date('Y-m-d', strtotime($data. ' + 1 days'));
			//error_log("avanzamentoNC -> caricaDaExcel:".date("Y-m-d H:i:s"));
			$recPcs=caricaDatiDaExcel($inputFileName,$esercizio,$da_data,$a_data,false);
			
			error_log("associazioneArtPN -> caricaDaExcel($da_data,$a_data): nrec=".count($recPcs).",".date("Y-m-d H:i:s"));
			
			$commessa_precedente="";
			for ($i=0;$i<count($recPcs);$i++)
			{
				$rec=$recPcs[$i];
				
				$macchina=$rec["macchina"];
				$pn=$rec["part_number"];
				$commessa=$rec["commessa"];
				$dataora=$rec["order_end_date"];
				
				//error_log("m:$macchina,mR:$macchinaRichiesta".",".strlen($macchinaRichiesta));
				
				if ($macchinaRichiesta =="" || $macchina == $macchinaRichiesta) {
					$res=true;
					$pag=1;
					
					if ($commessa != $commessa_precedente) {
						//--echo "associaPN:$pn-";
						$esercizio_commessa=$esercizio;
						// ricerca max(op) per esercizio
						// se commessa > max_op allora esercizio_commessa=esercizio-1 
						// ricerca record idop=esercizio_commessa+'-'+commessa 
						// se trovato ricava articolo
						//            ricerca record partnumber=pn
						//            se trovato aggiorna record : descrizione = articolo
						// ricerca max(op) per esercizio
						// se commessa > max_op allora esercizio_commessa=esercizio-1 
						// ricerca record idop=esercizio_commessa+'-'+commessa 
						// se trovato ricava articolo
						//            ricerca record partnumber=pn
						//            se trovato aggiorna record : descrizione = articolo

						// ricerca max(op) alla data della colonna del foglio excel
						$pag=1;
						$where="data <='".$dataora."'";
						$campi="max(idop) as max_idop";
						$row=$dbGMN->db_get($dbhandleGMN,"op",$campi,$where,$pag,1);
						if (count($row)>0) {
							$max_idop=$row[0]['max_idop'];
							$partiMax_idop=explode("-",$max_idop);
							$max_op=(int)$partiMax_idop[1];
							if ((int)$commessa>$max_op) {
								$esercizio_commessa=(int)$esercizio-1;
							}
							else {
								$esercizio_commessa=$esercizio;
							}
							// componi idop da ricercare
							$idop=sprintf("%04d-%05d",(int)$esercizio_commessa,(int)$commessa);
							// ricerca record idop=esercizio_commessa+'-'+commessa 
							$pag=1;
							$where="idop='".$idop."'";
							$campi="idop,idarticolo";
							$row=$dbGMN->db_get($dbhandleGMN,"op",$campi,$where,$pag,1);
							if (count($row)>0){
								// se trovato ricava articolo
								$articolo=$row[0]['idarticolo'];
								//--echo "Art:$articolo";
								// ricerca record partnumber=pn
								$pag=1;
								$where="part_number='".$pn."'";
								$campi="part_number,descrizione";
								$tabella="partnumbers";
								$row=$dbTH->db_get($dbhandle,$tabella,$campi,$where,$pag,1);
								$valoreCampo[0]=$pn; $valoreCampo[1]=$articolo;
								if (count($row)>0) {
									// se trovato aggiorna record : descrizione = articolo
									$res = $dbTH->db_update_valori($dbhandle,$tabella,$valoreCampo,$campo);
									}
								else {
									// altrimenti inserisci record
									$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
								}
							}
							else {
								error_log("partnumbers->associazioneArtPN:non trovato idop($idop)");
							}
						}
						else {
							error_log("partnumbers->associazioneArtPN:non trovato max_idop");
						}
						
					}
					else {
						$res=false;
					} // if commessa
					$commessa_precedente=$commessa;
				} // if macchinaRichiesta
			} // for
		//} while ($data<$a_data);
	
	}	
	
?>
