<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="status_info";
	//$campo=$campidb[$tabella];
	
	$schema=$schemadb[$tabella];
	$campiTabella=$schema["campi"];
	$elencoPKs=$schema["PK"];
	$pks = explode(",",$elencoPKs);
    	
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
	error_log("statusCNC.php"." ".$oper);
	

	switch ($oper){
        case "getCNCstatus":
                $sort="";
                if(isset($_POST["op"]))
                {
                    $unique_id=$_POST["unique_id"];//first, last,nextfw,nextbw
                    $daData=$_POST["start_date"];//first, last,nextfw,nextbw
                    $aData=$_POST["end_date"];//first, last,nextfw,nextbw
                }
                else
                {
                    $unique_id=$_GET["unique_id"];//first, last,nextfw,nextbw
                    $daData=$_GET["start_date"];//first, last,nextfw,nextbw
                    $aData=$_GET["end_date"];//first, last,nextfw,nextbw
                }

                if (strlen($daData) <= 10) {
                    $daData .= " 00:00:00";
                }
                if (strlen($aData) <= 10) {
                    $aData .= " 23:59:59";
                }
    
                $partiDataOra=explode(" ", $daData);
                $daData_data=$partiDataOra[0];
                $daData_ora=$partiDataOra[1];
    
                $partiDataOra=explode(" ", $aData);
                $aData_data=$partiDataOra[0];
                $aData_ora=$partiDataOra[1];
                
                $filtro  ="unique_id='".$unique_id."'";
                $filtro .=" and ";
                $filtro .="date >='".$daData_data."'";
                $filtro .=" and ";
                $filtro .="date <='".$aData_data."'";
                $filtro .=" and ";
                $filtro .="time >='".$daData_ora."'";
                $filtro .=" and ";
                $filtro .="time <='".$aData_ora."'";
                
                        
                error_log("filtro:".$filtro);
                
                $ordinamento="date asc , time asc ";
                $raggruppamento="date , time";
                    
                $campiElenco="unique_id,date,time,connected,device_status,device_status_timestamp,device_status_timer";


                //$row=db_select($dbhandle,$tabelleJoin,$campiElenco,$pag,$nrighexpag);
                $pag=1;
                $nrighexpag=1000;
                $npag=1;
                $rowStatus=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
                $res=trasformaStatoxGrafico($rowStatus,$unique_id,$daData,$aData,$row);
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
            $sort="";
			if(isset($_POST["op"]))
			{
//				$pag=$_POST["pag"];//first, last,nextfw,nextbw
//				$npag=$_POST["npag"];//first, last,nextfw,nextbw
				$unique_id=$_POST["unique_id"];//first, last,nextfw,nextbw
				$daData=$_POST["start_date"];//first, last,nextfw,nextbw
				$aData=$_POST["end_date"];//first, last,nextfw,nextbw
//				$filtro=$_POST["filtro"];//id_order > 'C2'
//				$sort=$_POST["ordinamento"];//first, last,nextfw,nextbw
			}
			else
			{
//				$pag=$_GET["pag"];
//				$npag=$_GET["npag"];
				$unique_id=$_GET["unique_id"];//first, last,nextfw,nextbw
				$daData=$_GET["start_date"];//first, last,nextfw,nextbw
				$aData=$_GET["end_date"];//first, last,nextfw,nextbw
//				$filtro=$_GET["filtro"];//id_order > 'C2'
//				$sort=$_GET["ordinamento"];//first, last,nextfw,nextbw
			}

            if (strlen($daData) <= 10) {
                $daData .= " 00:00:00";
            }
            if (strlen($aData) <= 10) {
                $aData .= " 23:59:59";
            }

            $partiDataOra=explode(" ", $daData);
            $daData_data=$partiDataOra[0];
            $daData_ora=$partiDataOra[1];

            $partiDataOra=explode(" ", $aData);
            $aData_data=$partiDataOra[0];
            $aData_ora=$partiDataOra[1];
            
            $filtro  ="unique_id='".$unique_id."'";
            $filtro .=" and ";
            $filtro .="date >='".$daData_data."'";
            $filtro .=" and ";
            $filtro .="date <='".$aData_data."'";
            $filtro .=" and ";
            $filtro .="time >='".$daData_ora."'";
            $filtro .=" and ";
            $filtro .="time <='".$aData_ora."'";
            
            error_log("filtro:".$filtro);
			
			$ordinamento="date asc , time asc ";
			$raggruppamento="date , time";
            
            $campiElenco="unique_id,date,time,connected,device_status,device_status_timestamp,device_status_timer";


            //$row=db_select($dbhandle,$tabelleJoin,$campiElenco,$pag,$nrighexpag);
            $pag=1;
            $nrighexpag=1000;
            $npag=1;
            $row=$dbTH->db_get($dbhandle,$tabella,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	

	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listbox" || $oper=="get"  || $oper=="getCNCstatus")
		$risposta=preparaRisposta($res,json_encode($row),$oper,$pag,$npag);
	else
		$risposta=preparaRisposta($res,$msg,$oper);
	
	echo json_encode($risposta);
	$dbTH->db_close($dbhandle);
	}
	else 
	{
		$dbTH->db_close($dbhandle);


		$nomeReport="Report_stato_giornaliero";
		exportReport($nomeReport,$filtro,$row,$tipoAggregazione,"");
	}
	
    /*
    scorre i record di status_info(Tiso,durata_sec) e prepara i necessari record per la 
    graficazione(tsec,durata_sec) con tsec relativo all'inizio intervallo selezionato
    vengono inseriti i record 'neri' per i periodi in cui i dati mancano
    */
    function trasformaStatoxGrafico($row,$unique_id,$daData,$aData,&$rowGxM)
	{
        $rowG=array();
        $res=true;

        $T0="";$T1="";
        $t_sec_prec=0;$d_sec_prec=0;
        if ($daData > "") {
            $partiData=explode(" ",$daData);
            if (count($partiData)<=1) {
                $T0=$daData." 00:00:00";
            }
            else {
                $T0=$daData;
            }
        }
        if ($aData > "") {
            $partiData=explode(" ",$aData);
            if (count($partiData)<=1) {
                $T1=$aData." 23:59:59";
            }
            else {
                $T1=$aData;
            }
        }
        error_log("trasformaStatoxGrafico:T0,T1:$T0,$T1");
        // ultimo record selezionato
        $imax=count($row);
        if ($imax >0) {
            $key="device_status";
            $stato=$row[$imax-1][$key];
            $key="device_status_timestamp";
            $T=normalizzaData($row[$imax-1][$key]);
            $key="device_status_timer";
            $d_sec=$row[$imax-1][$key];
            // se l'ultimo record non copre la fine dell'intervallo
            // si inserisce un record in row a marcare la fine intervallo
            $t_sec=differenzaSec($T1,$T);
            error_log("trasformaStatoxGrafico:ultimo T:$T,T1-T:$t_sec,$d_sec");
            if ($t_sec > $d_sec){
                $key="device_status";
                $row[$imax][$key]='Unknown';
                $key="device_status_timestamp";
                $row[$imax][$key]=$T1;
                $key="device_status_timer";
                $row[$imax][$key]=0;
            }
        }
		foreach($row as $record) {
            $key="device_status";
            $stato=$record[$key];
            $key="device_status_timestamp";
            $T=normalizzaData($record[$key]);
            
            $key="device_status_timer";
            $d_sec=$record[$key];
            if ($T0 == "") {
                $T0=$T;
                $t_sec_prec=0;
                $d_sec_prec=0;
            }
 
            $t_sec=differenzaSec($T,$T0);
            // se T(i-1)+d(i-1) < T(i) => inserire un record nero
            //error_log("T0,T,tsec,tsecprec,dsecprec:$T0;$T;$t_sec;$t_sec_prec;$d_sec_prec");
            $t_sec_next = ($t_sec_prec + $d_sec_prec);
            if ($t_sec > $t_sec_next) {
                $d_sec_next = ( $t_sec - $t_sec_next);
                // inserire record nero
                $recG=array();
                $recG["device_status"]="Unknown";
                $recG["t"]=$t_sec_next;
                $recG["d"]=$d_sec_next;

                $r=count($rowG);
                $rowG[$r]= $recG;
            }

            $recG=array();
            $recG["device_status"]=$stato;
            $recG["t"]=$t_sec;
            $recG["d"]=$d_sec;

            $r=count($rowG);
            $rowG[$r]= $recG;
        
            $t_sec_prec=$t_sec;
            $d_sec_prec=$d_sec;
        }	
        $rowGxM['unique_id']=$unique_id;
        $rowGxM['daData']=$daData;
        $rowGxM['aData']=$aData;
        $rowGxM['data']=$rowG;
		return $res;
	}

    function normalizzaData($dataOra){
        // da formato gg/mm/aaaa hh:mm:ss a formato iso aaaa-mm-gg hh:mm:ss UTC 
        $d=new DateTime();
        $tz= new DateTimeZone("UTC");
        $d2=date_timezone_set($d,$tz);
        $suffix=$d2->format("O");

        $partiDataOra=explode(" ",$dataOra);
        $parteData=$partiDataOra[0];
        $parteOra=$partiDataOra[1];
        $partiD=explode("/",$parteData);
        if (count($partiD) == 3) {
            $data=$partiD[2]."-".$partiD[1]."-".$partiD[0];
            $dataISO=$data." ".$parteOra.$suffix;//."+02";
        }
        else {
            $dataISO=$dataOra;
        }
        
        return $dataISO;
    }

	function calcolaTsec($timestamp){
        return strtotime($timestamp);
    }

    function differenzaSec($t2,$t1) {
        return (strtotime($t2)-strtotime($t1));
    }
?>
