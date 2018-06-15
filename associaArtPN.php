<?php
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

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

	error_log("associaArtPN.php"." ".$oper);
	$logger->log(Logger::INFO, "associaArtPN.php"." ".$oper);
	error_log("associaArtPN.php"." ".json_encode($_GET));
	$logger->log(Logger::INFO, "associaArtPN.php"." ".json_encode($_GET));
	
	$row="";
	$pag=1;
	switch ($oper){
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
			$pag=1;
            $npag=0;
            //$filtro="";
            
            $inputFileName = $pathAvanzamento;//'./uploads/avanzamento_produzione.xlsx';
            
            
            if ($a_data >= $da_data){
				$logger->log(Logger::INFO,"associaArtPN:start associazioneArtPN(esercizio:$esercizio a:$a_data da:$da_data filtro:$filtro)");
                $num_art=associazioneArtPN($dbTH,$dbhandle,$inputFileName,$campo,$esercizio,$a_data,$da_data,$filtro);
                $logger->log(Logger::INFO,"associaArtPN:end   associazioneArtPN:num_art:$num_art");
                $msg="ok";
            }
            else {
                $msg="date errate";
                $res=false;
                }
				$logger->log(Logger::INFO,"associaArtPN:fine:msg:$msg");
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
		$nomeReport = "Report_part_numbers";

		exportReport($nomeReport,$filtro,$row);
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
		$mylogger=$GLOBALS['logger'];

		$mylogger->log(Logger::INFO,"associazioneArtPN:$inputFileName,$esercizio,$da_data,$a_data,$filtro");
	
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
        $num_art=0;// contatore record aggiornati
		$data=date('Y-m-d', strtotime($da_data. ' - 1 days'));
		//do {
			$data=date('Y-m-d', strtotime($data. ' + 1 days'));
			//error_log("avanzamentoNC -> caricaDaExcel:".date("Y-m-d H:i:s"));
			$recPcs=caricaDatiDaExcel($inputFileName,$esercizio,$da_data,$a_data,false);
			
			$mylogger->log(Logger::INFO,"associazioneArtPN:end caricaDaExcel($da_data,$a_data): nrec=".count($recPcs).",".date("Y-m-d H:i:s"));
			
            $commessa_precedente="";
            $i_max=count($recPcs);
			for ($i=0;$i<count($recPcs);$i++)
			{
				$rec=$recPcs[$i];
				
				$macchina=$rec["macchina"];
                $pn=$rec["part_number"];
                // pulisci pn da riferimenti contenuti tra ()
                $partiPn=explode("(",$pn);
                $pn=trim($partiPn[0]);

				$commessa=$rec["commessa"];
				$dataora=$rec["order_end_date"];
				
				$mylogger->log(Logger::INFO,"associazioneArtPN:record:".($i+1)." / $i_max m:$macchina,pn:$pn data:$dataora op:$commessa");
				
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
						$dataoraISO=str_replace(" ","T",$dataora);
						
						$where="data <='".$dataoraISO."'";
						$campi="max(idop) as max_idop";
						$row=$dbGMN->db_get($dbhandleGMN,"op",$campi,$where,$pag,1);
						if (count($row)>0) {
							$max_idop=$row[0]['max_idop'];
							$partiMax_idop=explode("-",$max_idop);
                            $max_op=(int)$partiMax_idop[1];
                            
                            // estrae gli op dal campo
                            $ops=estraiOP($commessa);
                            // usa l'ultimo op
                            $op=$ops[count($ops)-1];

							if ((int)$op>$max_op) {
								$esercizio_commessa=(int)$esercizio-1;
							}
							else {
								$esercizio_commessa=$esercizio;
							}
							// componi idop da ricercare
							$idop=sprintf("%04d-%05d",(int)$esercizio_commessa,(int)$op);
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
								$valoreCampo[0]=$pn; $valoreCampo[1]=$articolo;
								$tabella="partnumbers";
								$row=$dbTH->db_get($dbhandle,$tabella,$campi,$where,$pag,1);
								if (count($row)>0) {
									// se trovato aggiorna record : descrizione = articolo
									$res = $dbTH->db_update_valori($dbhandle,$tabella,$valoreCampo,$campo);
									}
								else {
									// altrimenti inserisci record
									$res = $dbTH->db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo);
                                }
                                $num_art++;
							}
							else {
								$mylogger->log(Logger::INFO,"associazioneArtPN:partnumbers->associazioneArtPN:non trovato idop($idop)");
							}
						}
						else {
							$mylogger->log(Logger::INFO,"associazioneArtPN:partnumbers->associazioneArtPN:non trovato max_idop");
						}
						
					}
					else {
						$res=false;
					} // if commessa
					$commessa_precedente=$commessa;
				} // if macchinaRichiesta
			} // for
		//} while ($data<$a_data);
            return $num_art;
	}	
    
    function estraiOP($commessa){
        
        $ops=array();
        $indice=0;
        // cerca il segno +
        $partiCommessa=explode("+",$commessa);
        $i_max=count($partiCommessa);
        if ($i_max>0){
            for ($i=0;$i<count($partiCommessa);$i++) {
                $op=$partiCommessa[$i];
                // elimina prefissi non numerici P, R , spazi
                do {
                    $c1=substr($op,0,1);
                    if ($c1<"0" || $c1>"9")
                        $op=substr($op,1,strlen($op)-1);
                } while ($op>"" && ($c1<"0" || $c1>"9"));
                $ops[$indice++]=$op;
            }
        }
        
        return $ops;
    }
?>
