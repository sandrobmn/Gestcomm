<?php

include_once('db.php');
include 'exportX.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="macchinexcommessa";
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
	$row="";
	$pag=1;
	if(isset($_POST["op"]))
	{
		$oper=$_POST["op"];
	}
	else
	{
		$oper=$_GET["op"];
	}
	//echo "oper:".$oper;
	
	error_log("setupCNC.php"." ".$oper);

	switch ($oper){
		case "getCNCreportmisure":
		case "getCNCToolOffset":
		case "setCNCToolOffset":
		case "getCNCcounter":
		case "setCNCcounter":
		case "setCNCprogram":
		case "setCNCistruzioni":
/*		case "delete":
		case "insert":
        case "edit":
*/
			if(isset($_POST["op"]))
			{
				//error_log("POST");
				for ($i=0;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valoreCampo[$i]=$_POST[$nomeCampo];
				}	
			}
			else
			{
				//error_log("GET");
				for ($i=0;$i<count($campo);$i++)
				{
					$nomeCampo=$campo[$i];
					$valoreCampo[$i]=$_GET[$nomeCampo];
				}	
			}
			if ($oper == "getCNCcounter")
			{
				$unique_id=$valoreCampo[$indiceTk];
				$req_pieces=-1;
				$machined_pieces=-1;
				// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name 
				$res=getCNCRequestedPieces($dbhandle, $unique_id,$req_pieces,$machined_pieces);
				$row=array();
				$row[0]=array("cnc_requested_pieces"=>$req_pieces,"cnc_machined_pieces"=>$machined_pieces);
				
			}	
			if ($oper == "setCNCcounter")
			{
				$unique_id=$valoreCampo[$indiceTk];
				$i=count($campo);
				$nomeCampo="cnc_requested_pieces";
				$valoreCampo[$i]=$_POST[$nomeCampo];
				$req_pieces=$valoreCampo[$i];

				$i=$i+1;
				$nomeCampo="cnc_machined_pieces";
				$valoreCampo[$i]=$_POST[$nomeCampo];
				$machined_pieces=$valoreCampo[$i];

				$res=true;
				if ($req_pieces =='' || (int)$req_pieces < 0)
					$res=false;
				if ($machined_pieces =='' || (int)$machined_pieces < 0)
					$res=false;
				// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name 
				if ($res)
					$res=setCNCRequestedPieces($dbhandle, $unique_id,$req_pieces,$machined_pieces);
			}	
			if ($oper == "setCNCistruzioni")
			{
				$unique_id=$valoreCampo[$indiceTk];
				$i=count($campo);

				$nomeCampo="cnc_folder_part_program";
				$valoreCampo[$i]=$_POST[$nomeCampo];
				$folder_part_program=$valoreCampo[$i];
				//error_log("folder:".$folder_part_program);

				$i=$i+1;
				$nomeCampo="part_program_istruzioni";
				$valoreCampo[$i]=$_POST[$nomeCampo];
				$part_program_istruzioni=$valoreCampo[$i];
				error_log("folder:".$part_program_istruzioni);
				$nomePgmIstruzioni=registraPgmIstruzioni($part_program_istruzioni);
				
				//error_log("setupCNC - ".$nomePgmIstruzioni);
				$res=setCNCPartProgram($dbhandle, $unique_id,$nomePgmIstruzioni,$folder_part_program);
			}
			if ($oper == "setCNCprogram")
			{
				$unique_id=$valoreCampo[$indiceTk];
				$i=count($campo);

				$nomeCampo="cnc_folder_part_program";
				$valoreCampo[$i]=$_POST[$nomeCampo];
				$folder_part_program=$valoreCampo[$i];
				error_log("folder:".$folder_part_program);

				$i=$i+1;
				$nomeCampo="cnc_part_program";
				//$valoreCampo[$i]=$_POST[$nomeCampo];
				//$part_program=$valoreCampo[$i];
				if ( 0 < $_FILES[$nomeCampo]['error'] ) {
					echo 'Error: ' . $_FILES[$nomeCampo]['error'] . '<br>';
					$res=$_FILES[$nomeCampo]['error'];
				}
				else {
					error_log("file:".$_FILES[$nomeCampo]['name']);

					$uploaddir = './uploads/';
					$uploadfile = $uploaddir . basename($_FILES[$nomeCampo]['name']);
					if (move_uploaded_file($_FILES[$nomeCampo]['tmp_name'], $uploadfile)) {
						error_log("File is valid, and was successfully uploaded.\n");
					} else {
						error_log("Possible file upload attack!\n");
					}
					
					$valoreCampo[$i]=$uploadfile;
					$part_program=$valoreCampo[$i];
					// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name 
					$res=setCNCPartProgram($dbhandle, $unique_id,$part_program,$folder_part_program);
				}
			}	
			if ($oper == "getCNCToolOffset")
			{
				$unique_id=$valoreCampo[$indiceTk];
				$toolNumber='';
				$pathNumber='';
				$nomiOffset=array("X","Z","R","I","Y");
				$mapnomiOffset=array(0,2,3,4,1);
				$toolOffsets = array();
				// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name 
				$i=count($campo);

				$nomeCampo="tool_number";
				if(isset($_POST["op"]))
					$valoreCampo[$i]=$_POST[$nomeCampo];
				else
					$valoreCampo[$i]=$_GET[$nomeCampo];
				$toolNumber=$valoreCampo[$i];

				$i=$i+1;
                $nomeCampo="path_number";
				if(isset($_POST["op"]))
					$valoreCampo[$i]=$_POST[$nomeCampo];
				else
					$valoreCampo[$i]=$_GET[$nomeCampo];
                $pathNumber=$valoreCampo[$i];

				$i=$i+1;
                $nomeCampo="scalaTofs";
				if(isset($_POST["op"]))
					$valoreCampo[$i]=$_POST[$nomeCampo];
				else
					$valoreCampo[$i]=$_GET[$nomeCampo];
                $scalaTofs=$valoreCampo[$i];

				for ($j=0;$j<5;$j++){
                   
                        $toolOffsets[$j]=-1;
				}

                $res=getCNCToolOffset($dbhandle, $unique_id,$mapnomiOffset,$nomiOffset, $pathNumber,$toolNumber, $toolOffsets,$scalaTofs);
				$row=array();
                $row[0]=array("tool_number"=>$toolNumber
                ,"offsetX"=>$toolOffsets[0]
                ,"offsetZ"=>$toolOffsets[1]
                ,"offsetR"=>$toolOffsets[2]
                ,"offsetI"=>$toolOffsets[3]
                ,"offsetY"=>$toolOffsets[4]);
				
			}	
			if ($oper == "setCNCToolOffset")
			{
				$toolNumber='';
				$pathNumber='';
				$nomiOffset=array("X","Z","R","I","Y");
				$mapnomiOffset=array(0,4,1,2,3);
				$toolOffsets = array();
				
				$unique_id=$valoreCampo[$indiceTk];
				$i=count($campo);
				if(isset($_POST["op"]))
					$valoreCampo[$i]=$_POST[$nomeCampo];
				else
					$valoreCampo[$i]=$_GET[$nomeCampo];
					$toolNumber=$valoreCampo[$i];

					$i=$i+1;
					$nomeCampo="path_number";
					if(isset($_POST["op"]))
						$valoreCampo[$i]=$_POST[$nomeCampo];
					else
						$valoreCampo[$i]=$_GET[$nomeCampo];
					$pathNumber=$valoreCampo[$i];
	
					$i=$i+1;
					$nomeCampo="scalaTofs";
					if(isset($_POST["op"]))
						$valoreCampo[$i]=$_POST[$nomeCampo];
					else
						$valoreCampo[$i]=$_GET[$nomeCampo];
					$scalaTofs=$valoreCampo[$i];
                
				for ($j=0;$j<5;$j++){
					$i=$i+1;
					$nomeCampo="offset".$nomiOffset[$j];
					if(isset($_POST["op"]))
						$valoreCampo[$i]=$_POST[$nomeCampo];
					else
						$valoreCampo[$i]=$_GET[$nomeCampo];
					$toolOffsets[$j]=$valoreCampo[$i];
				}

                $res=true;
				if ($toolNumber =='' || count($toolOffsets) == 0)
					$res=false;
                for ($j=0;$j<5;$j++){
                    if ($toolOffsets[$j]=='')
                        $res=false;
                }
                    // recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name 
				if ($res)
					$res=setCNCToolOffset($dbhandle, $unique_id,$mapnomiOffset,$nomiOffset, $pathNumber, $toolNumber,$toolOffsets,$scalaTofs);
            }
			if ($oper == "getCNCreportmisure")
			{
				$unique_id=$valoreCampo[$indiceTk];
				$i=count($campo);

				$nomeCampo="cnc_report_misure";
				//$valoreCampo[$i]=$_POST[$nomeCampo];
				//$part_program=$valoreCampo[$i];
				if ( 0 < $_FILES[$nomeCampo]['error'] ) {
					echo 'Error: ' . $_FILES[$nomeCampo]['error'] . '<br>';
                    $res=$_FILES[$nomeCampo]['error'];
                    $res=false;
				}
				else {
					error_log("file:".$_FILES[$nomeCampo]['name']);

					$uploaddir = './uploads/';
					$uploadfile = $uploaddir . basename($_FILES[$nomeCampo]['name']);
					// sostituisce spazi con trattini
					$uploadfile=str_replace(" ", "-" ,$uploadfile);
					if (move_uploaded_file($_FILES[$nomeCampo]['tmp_name'], $uploadfile)) {
						error_log("File is valid, and was successfully uploaded.\n");
                        $valoreCampo[$i]=$uploadfile;
                        $res=true;

                    } else {
                        error_log("Possible file upload attack!\n");
                        $res=false;
					}
					if ($res=true){
                        // elabora il report
                        // chiama pdftotext
                        // elabora output di pdftotext
                        $misure=array();
                        $misure[0]=array("cnc_report_misure_uploaded"=>$uploadfile);
                        $res=preparaReportMisure($uploadfile,$misure);
                        $row=$misure;
                    }
                }
			}	
            
            
			if($res)
				$msg=$oper." riuscito";
			else
				$msg=$oper." fallito";
			
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
    
	if ($oper != "report" )
	{
	
        if ( $oper == "list" || $oper == "listbox" || $oper == "get" || $oper =="getCNCcounter" || $oper =="getCNCToolOffset" || $oper == "getCNCreportmisure")
            $risposta=preparaRisposta($res,json_encode($row),$oper,$pag);
        else
            $risposta=preparaRisposta($res,$msg,$oper);

            error_log("uscita:".$oper." ".$res. " risposta:".json_encode($risposta));

        echo json_encode($risposta);
        db_close($dbhandle);
	}
	else 
	{
		db_close($dbhandle);

		// filename for download
		$nomeReport = "Report_mxc";

		exportReport($nomeReport,$filtro,$row);
	}

function esamina($temp)
{
	error_log("esamina ".$temp);
	for ($i=0;$i<strlen($temp);$i++) {
		
		$c=substr($temp,$i,1);
		error_log("i:$i c:".$c." asc:".ord($c));

	}
}

function convertiGradi($angolo)
{
	// asd= g + p/60 + s/3600
	$pg=strpos($angolo,176);
	$pp=strpos($angolo,39);
	$ps=strpos($angolo,34);
	
	$g=substr($angolo,0,$pg);
	$p=substr($angolo,$pg+1,($pp-$pg-1));
	$s=substr($angolo,$pp+1,($ps-$pp-1));

	$angolosessadecimale=$g+$p/60+$s/3600;
	$angolosessadecimale = ceil($angolosessadecimale*10000);
	$angolosessadecimale=$angolosessadecimale/10000;
	error_log("a1:".$angolo."($g $p $s) "." a2:".$angolosessadecimale);

	return $angolosessadecimale;
}
function preparaReportMisure($uploadfile,&$misure) {
        // invocazione: pdftotext  
        $dirreportfile=dirname($uploadfile);
        $reportfile=$dirreportfile."\\".basename($uploadfile,".pdf").".txt";
        if (file_exists($reportfile))
            unlink($reportfile);
		$stringaInvocazione=".\\exec\\pdftotext.exe -raw ".$uploadfile;
		//---$stringaInvocazione=".\\exec\\pdftotext.exe -table ".$uploadfile;
		error_log("invocazione pdftotext: ".$stringaInvocazione." ".$reportfile);
		exec($stringaInvocazione,$output,$ret_var);
		//error_log("ritorno da invocazione pdftotext: ".$stringaInvocazione);
        $t0=time()+3;
        $fineop=false;
        do {
            $t1=time();
            $fineop=file_exists($reportfile);
        } while ($t1<$t0 && !$fineop);
        error_log("esito invocazione pdftotext: ".$fineop);
        //
        // elaborazione report.txt
        //
        $res=$fineop;
        if ($fineop){
            $campiMisuraA=array("nome","DimNom","TS","TI","VM","diffN","diffA");
            $reportRows=file($reportfile);
            $inizio=false;$fine=false;
            $i=0;
            $m=count($misure);
            do {
                if (!$inizio) // ricerca inizio
                    $inizio=!(strpos($reportRows[$i],"Grafica") === false);
                else {
                    // ricerca fine
                    $fine=!(strpos($reportRows[$i],"EXACTA") === false);
                    if (!$fine){
                        // pulizia linea terminata con \r\n
                        $n=strpos($reportRows[$i], "\r\n");
                        if (!($n === false))
                            $riga=substr($reportRows[$i], 0, $n);
                        else
                            $riga=$reportRows[$i];
						//error_log("accoda riga:".$riga);
                        $misura=explode(" ",$riga);
                        $misuraA=array();
						$k0=count($misura);
						//error_log("array($k0):".json_encode($misura));
						$j=0;
						$descr="";
						$temp="";
						$zona=0;// 0: zona alfanumerica 1: zona misure
                        for ($k=0;$k<$k0;$k++){
							$temp = $misura[$k];
							$a= strpos ( $temp , ".");$b=strpos($temp,176);
							$zona=0;
							if (!($a === false))
								$zona=1;
							if (!($b === false))
								$zona=2;
							
							if ($zona == 0) {
								if (strlen($descr) > 0)
									$descr=$descr." ";
								$descr=$descr.$temp;
							}
							else {
								if (strlen($descr) > 0) {
									// unione campi alfabetici
									$misuraA[$campiMisuraA[$j]] = $descr;
									$descr="";
									$j++;
								}
								if ($zona == 2)
									$temp=convertiGradi($temp);

								// se campo numerico con . o ° ok
								$misuraA[$campiMisuraA[$j]] = $temp;
								$j++;
							}
								
						}
						if ($j < 7) {
							$misuraA[$campiMisuraA[$j]]='';
						}

						$accodaRiga=false;
						// criteri di selezione righe:
						// diffA<>0
                        if ($j >= 7) {
							$accodaRiga=true;
						}
						// tutte
						$accodaRiga=true;
						
                        if ($accodaRiga) {
						$misure[$m]=$misuraA;
						error_log("accodata riga($m):".$riga);
						$m++;
                        }
                    }
                }
                $i++;
            } while($i<=count($reportRows) && !$fine);
        }
        return $res;
}

function getCNCToolOffset($dbhandle, $unique_id,  $mapnomiOffset,$nomiOffset, $pathNumber,$toolNumber, &$toolOffsets,$toolOffsetScala){
        $res=false;
            // recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name
        $dev=getDeviceParam($dbhandle, $unique_id);
        if (count($dev)>=1) {
            $agent_url=$dev[0]["agent_url"];
            $agent_port=$dev[0]["agent_port"];
            $agent_device_name=$dev[0]["agent_device_name"];
            
			if ($pathNumber <=1)
				$param_name=sprintf("T%d%02d",$toolNumber,$toolNumber);
			else
			$param_name=sprintf("T%d%02d_%d",$toolNumber,$toolNumber,$pathNumber);
			
			$param_valueXML="";
			$param_value=-1;

			$res=getCNCParam($agent_url,$agent_port,$agent_device_name, $param_name, $param_valueXML);
			if ($res){
				$param_value=$param_valueXML->Position;
			}
			error_log("res:".$res." param_value:".$param_value);

			if ($res) {
				$tofs=explode(" ",$param_value);
				error_log("res:".$res." tofs:".json_encode($tofs)." param_value:".$param_value);

				if (count($tofs)<5)
					$res=false;
				if ($res){
					error_log("res:".$res." tofs:".json_encode($tofs)." param_value:".$param_value);
					for ($j=0;$j<4;$j++) {
						$k=$mapnomiOffset[$j];
						if ($nomiOffset[$j]!='I')
							$toolOffsets[$j]=$tofs[$k]/$toolOffsetScala;
						else
						$toolOffsets[$j]=$tofs[$k];
					}
					$j=4;
					$k=$mapnomiOffset[$j];
					if ($nomiOffset[$j]!='I')
						$toolOffsets[$j]=$tofs[$k]/$toolOffsetScala;
					else
						$toolOffsets[$j]=$tofs[$k];
				}
			}
    
            $res=true;
        }
    
        
        return $res;
}
    
    
    
	
function setCNCToolOffset($dbhandle, $unique_id,$mapnomiOffset, $nomiOffset, $pathNumber, $toolNumber, $toolOffsets,$toolOffsetScala){
	$res="";
		// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name
	$dev=getDeviceParam($dbhandle, $unique_id);
	if (count($dev)>=1) {
		$agent_url=$dev[0]["agent_url"];
		$agent_port=$dev[0]["agent_port"];
		$agent_device_name=$dev[0]["agent_device_name"];
		
        $method="POST";
		$comando="";
		$data=array();
        $parametri="";
        $scalaTofs=(int)$toolOffsetScala;
        
		//$data["time"]="2017-11-19T23:55:00Z";
		//$data["Fovr"]=$req_pieces;
        $data["_type"]="command";

		if ($pathNumber <=1)
				$param_name=sprintf("T%d%02d",$toolNumber,$toolNumber);
			else
				$param_name=sprintf("T%d%02d_%d",$toolNumber,$toolNumber,$pathNumber);
            $valore="";
            for ($j=0;$j<4;$j++){
                $k=$mapnomiOffset[$j];
                error_log("tooloffset[".$k."]:".$toolOffsets[$k]."-".strval((int)((float)$toolOffsets[$k]*$scalaTofs)));
                if ($valore !="")
                    $valore=$valore." ";
                if ($nomiOffset[$k]!='I')
                        $valore=$valore.strval((int)((float)$toolOffsets[$k]*$scalaTofs));
                else
                    $valore=$valore.strval((int)((float)$toolOffsets[$k]));
            }
            $j=4;
            $k=$mapnomiOffset[$j];
            $valore=$valore." ";
            $valore=$valore.(int)$toolOffsets[$k];
                
            $data[$param_name]=$valore;
            
            error_log("valore:".$valore);


		// use key 'http' even if you send the request to https://...
        $url="http://".$agent_url.":".$agent_port."/".$agent_device_name."/".$comando.$parametri;
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => $method,
				'content' => http_build_query($data)
			)
		);
		
		error_log("setCNCToolOffset-setDevice:".$url.'/'.$options['http']['content']);

		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		
		error_log($url.json_encode($result));

		$res=true;
	}
	else {
		error_log("setCNCToolOffset-getDevice:".$url.'/'.$options['http']['content']);
	}

	return $res;

}

function registraPgmIstruzioni($part_program_istruzioni) {
	// legge 1^riga pgm ed estrae il nome (default: =0777)
	// aggiunge estensione txt
	// cancella se esiste già nel folder .\uploads
	// esamina ogni linea e toglie \r\n
	// registra ogni linea terminando con \n

	//error_log("registraPgmIstruzioni - inizio:".$part_program_istruzioni);

	$nomePgmDefault="O0777";
	$nomePgm="";
	
	$lineePgm=explode("\n",$part_program_istruzioni);
	error_log("lineePgm:".count($lineePgm));
	$trovatoNomePgm=false;
	for ($i=0;$i<count($lineePgm);$i++){
		if(!$trovatoNomePgm){
			$linea=$lineePgm[$i];
			if (substr($linea,0,1)=="O"){
				$numPgm=substr($linea,1,4);
				$delimit=substr($linea,5,1);
				if ($delimit <'0' || $delimit >'9'){
					$numPgmNumerico=true;
					for ($j=0;$j<4;$j++){
						if(substr($numPgm,$j,1)<'0'||substr($numPgm,$j,1)>'9')
							$numPgmNumerico=false;
					}
					if ($numPgmNumerico){
						$trovatoNomePgm=true;
						$nomePgm='O'.$numPgm;
					}
				}
			}
		}	
	}
	if (!$trovatoNomePgm)
		$nomePgm=$nomePgmDefault;
	$nomePgmIstruzioni=".\\uploads\\".$nomePgm.".txt";
	
	//error_log("registraPgmIstruzioni - nome:".$nomePgmIstruzioni);

	if (file_exists($nomePgmIstruzioni))
		unlink($nomePgmIstruzioni);
	
	//error_log("registraPgmIstruzioni-componi testo");

	$testo="%\n";
	for ($i=0;$i<count($lineePgm);$i++){
		$linea=$lineePgm[$i];
		$p=strpos($linea,"\r");
		//error_log("registraPgmIstruzioni-linea:".$linea." - p:".$p);
		if (!($p===false))
			$linea=substr($linea,0,$p);
		$testo .=$linea."\n";
	}
	$testo .="%\n";

	//error_log("registraPgmIstruzioni - testo:".$testo);

	$res=file_put_contents($nomePgmIstruzioni,$testo);

	//error_log("registraPgmIstruzioni - res:".$res);
	
	return $nomePgmIstruzioni;
}

function setCNCPartProgram($dbhandle, $unique_id,$part_program,$folder_part_program){
	$res="";
		// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name
	$dev=getDeviceParam($dbhandle, $unique_id);
	if (count($dev)>=1) {
		$agent_url=$dev[0]["agent_url"];
		$agent_port=$dev[0]["agent_port"];
		$agent_device_name=$dev[0]["agent_device_name"];
		
		// agent_device_name è il folder del file di configurazione dell'adapter
		$fileNameAdpt="c:/program files (x86)/fanuc mtconnect agent/adapters/".$agent_device_name."/adapter.ini";
		// ricava ip e port della macchina da file di configurazione dell'adapter
		$chiave="host";$sezione="focus";
		$risposta=read_ini_section_key($fileNameAdpt,$chiave,$sezione);
		//error_log(json_encode($risposta));
		$valori=$risposta;
		$ipDevice=$valori;
		$portDevice=8193;
		// invocazione: fanucupload ip xxxxxx port 8193 upload folder fffff pgm p1 p2 ... 
		$stringaInvocazione=".\\exec\\fanucupload ip ".$ipDevice." port ".$portDevice;
		$stringaInvocazione=$stringaInvocazione." upload ";
		$stringaInvocazione=$stringaInvocazione." folder ".$folder_part_program;
		$stringaInvocazione=$stringaInvocazione." pgm ".$part_program;
		error_log("invocazione fanucupload: ".$stringaInvocazione);
		exec($stringaInvocazione,$output,$ret_var);
		error_log("esito invocazione fanucupload: ".json_encode($output)."-".$ret_var);
	}

	$res="ok";
	return $res;
}

function getCNCParam($agent_url,$agent_port,$agent_device_name, $param_name, &$param_value){
	$res=false;

		// use key 'http' even if you send the request to https://...
		$method="GET";
		$comando="current";
		$parametri="?path=//DataItem[@name='".$param_name."']";
		
		$url="http://".$agent_url.":".$agent_port."/".$agent_device_name."/".$comando.$parametri;
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => $method,
				'content' => '' //http_build_query($data)
			)
		);
		
		error_log("getCNCParams:".$url);

		try {
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		
		error_log($url.json_encode($result));

		$myXMLData=$result;
		$xml=simplexml_load_string($myXMLData);

		$errore=strpos($result,"MTConnectError");

		if ($errore === false) {
            $e=$xml->Streams->DeviceStream->ComponentStream->Events;
            if ($e->PartCount == null)
			    $e=$xml->Streams->DeviceStream->ComponentStream->Samples;
            //-------$param_value=(int)$e->PartCount;
            $param_value=$e;
			error_log("param_value:".json_encode($param_value));
			//error_log("e:".json_encode($e->PartCount));
			$res=true;
		}
		else {
			$errore=$xml->Errors[0]->Error;
			error_log("error:".$errore);
			
	
		}
		
	}
	//catch exception
	catch(Exception $e) {
  		echo 'Message: ' .$e->getMessage();
	}
	return $res;
}

function getCNCRequestedPieces($dbhandle, $unique_id, &$req_pieces, &$machined_pieces){
	$res=false;
		// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name
	$dev=getDeviceParam($dbhandle, $unique_id);
	if (count($dev)>=1) {
		$agent_url=$dev[0]["agent_url"];
		$agent_port=$dev[0]["agent_port"];
		$agent_device_name=$dev[0]["agent_device_name"];
		
		$param_name="partsreq";
        $param_valueXML="";
        $param_value=-1;
        $res=getCNCParam($agent_url,$agent_port,$agent_device_name, $param_name, $param_valueXML);
        if ($res){
            $param_value=(int)$param_valueXML->PartCount;
        }
        $req_pieces=$param_value;

		$param_name="partscount";
        $param_valueXML="";
        $param_value=-1;
        $res=getCNCParam($agent_url,$agent_port,$agent_device_name, $param_name, $param_valueXML);
        if ($res){
            $param_value=(int)$param_valueXML->PartCount;
        }
		$machined_pieces=$param_value;

		$res=true;
	}

	
	return $res;
}

function setCNCParam($dbhandle, $unique_id, $dataParams){
	$res=false;
		// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name
	$dev=getDeviceParam($dbhandle, $unique_id);
	if (count($dev)>=1) {
		$agent_url=$dev[0]["agent_url"];
		$agent_port=$dev[0]["agent_port"];
		$agent_device_name=$dev[0]["agent_device_name"];
		
		//error_log("setCNCRequestedPieces-getDevice:".$agent_url);

		// use key 'http' even if you send the request to https://...
		$method="POST";
		$comando="";
		$data=array();
		$parametri="";
		//$data["time"]="2017-11-19T23:55:00Z";
		//$data["Fovr"]=$req_pieces;
		$data["_type"]="command";
		foreach ($dataParams as $key => $value) {
				$data[$key]=$value;
		}
		
		$url="http://".$agent_url.":".$agent_port."/".$agent_device_name."/".$comando.$parametri;
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => $method,
				'content' => http_build_query($data)
			)
		);
		
		error_log("setCNCParam-setParam:".$url.'/'.$options['http']['content']);

		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		
		error_log($url.json_encode($result));

		$res=true;
	}
	else {
		error_log("setCNCParam-getDevice:".$url.'/'.$options['http']['content']);
	}

	return $res;
}


function setCNCRequestedPieces($dbhandle, $unique_id, $req_pieces, $machined_pieces){
	$res=false;
		$data=array();
		$data["partsreq"]=$req_pieces;
		if ($machined_pieces >'')
			$data["partscount"]=$machined_pieces;
		
		$res = setCNCParam($dbhandle, $unique_id, $data);
		
		error_log("setCNCRequestedPieces:".$res);

	return $res;
}

	
function getDeviceParam($dbh, $unique_id){
// recupera unique_id e ricava da devices agent_url, agent_port, agent_device_name
$res=false;
$dbhandle=$dbh['dbhandle'] ;
$query="select * from devices where unique_id="."'".$unique_id."'";
	error_log("getDeviceParam:".$query);
	$stmt = $dbhandle->prepare($query);
	$row = array(); 
	$field = array();
	
	if ($stmt){
		$res = $stmt->execute();
		if (!is_bool( $res)) // $res : (object)Sqlite3Result oppure (boolean)false
		{
		// sqlite3_stmt *statement ;
			$totalColumn = $res->numColumns();
			for ($iterator = 0; $iterator<$totalColumn; $iterator++) {
				$field[$iterator]=$res->columnName($iterator);
			}
//         while($arr=$result->fetchArray(SQLITE3_ASSOC))
//         {
//          $names[$arr['id']]=$arr['student_name'];
//         }
		$i = 0; 

		 while($arr = $res->fetchArray(SQLITE3_ASSOC)){ 
			for ($j=0;$j<count($arr);$j++)
			{
			  $row[$i][$field[$j]] = $arr[$field[$j]]; 
			}	
			$i++; 
		 }
		 }
	 }
	 else {
	 //$res=" stmt error";
	}

	return $row; 
	}
?>
