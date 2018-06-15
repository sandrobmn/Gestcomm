<?php

include_once('db.php');
include 'exportX.php';
include 'caricaDaExcel.php';

	
	//$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	
	$tabella="pcs";
	$schema=$schemadb[$tabella];
	$campiTabella=$schema["campi"];
	$elencoPKs=$schema["PK"];
	$campo=array();
	$i=0;
	foreach($campiTabella as $x => $x_value) {
		//echo "Key=" . $x . ", Value=" . $x_value;
		$campo[$i]=$x;
		if ($x=="unique_id")
			$indiceTk=$i;
		$i++;
	}	

	$dbhandle=$dbhData;

	$pos = strpos($elencoPKs, ",");
	$pks=array();
	$pag=1;
	
	if ($pos === false)
	{
		$pks[0]=$elencoPKs; $pks[1]="";
	}
	else
	{
		$pks[0]=substr($elencoPKs,0,$pos); $pks[1]=substr($elencoPKs,$pos+1);
	}	
	//error_log("tabella:".$tabella." :".$pks[0]." :".$pks[1]);
	
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
	error_log( "pcs.php:".$oper);
	

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
			$row="";
		break;
		
		
		case "get":
			$npag=0;

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
			$nomeCampo1=$campo[0];
			$nomeCampo2=$campo[1];
			$where=$nomeCampo1." = '".$valoreId1."'";
			$where=$where." and ".$nomeCampo2." = '".$valoreId2."'";
			$msg= $oper."(".$where.")";
			$res=true;
			$pag=1;
			$row=$dbTH->db_get($dbhandle,$tabella,"*",$where,$pag,1);
		break;
		
		case "avanzamentoNC":
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

			if ($oper=='avanzamentoNC') {
				$inputFileName = $pathAvanzamento;//'./uploads/avanzamento_produzione.xlsx';
				avanzamentoNC($dbTH,$dbhandle,$inputFileName,$campo);
			}

			error_log("filtro:".$filtro);

			$campiElenco="p.unique_id, m.descrizione, m.posizione, p.order_start_program, p.part_number, p.order_start_date, p.order_start_pieces, p.order_end_date, p.total_pieces, p.good_pieces, p.status"; 
			$tabelleJoin="pcs as p join macchina as m on p.unique_id=m.unique_id ";
			$ordinamento="m.posizione asc ,p.order_start_date desc ";
			$raggruppamento="m.posizione ,p.order_start_date";

			if ($sort>"")
				$ordinamento=$sort;

			//$row=$dbTH->db_select($dbhandle,$tabelleJoin,$campiElenco,$pag,$nrighexpag);
			$row=$dbTH->db_get($dbhandle,$tabelleJoin,$campiElenco,$filtro,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag);
		break;
		
		default:
			$msg= $oper.":operazione non supportata";
			$res=false;
		break;
	}
	
	
	if ($oper != "report" )
	{
	
	if ( $oper == "list" || $oper == "listbox"  || $oper=="avanzamentoNC" || $oper=="get")
		$risposta=preparaRisposta($res,json_encode($row),$oper,$pag,$npag);
	else
		$risposta=preparaRisposta($res,$msg,$oper);
	
	echo json_encode($risposta);
	$dbTH->db_close($dbhandle);
	}
	else 
	{
		$dbTH->db_close($dbhandle);


		$nomeReport="Report_lotti_di_produzione";
		exportReport($nomeReport,$filtro,$row);
	}

function avanzamentoNC($dbTH,$dbhandle,$inputFileName,$campo)
{
	error_log("avanzamentoNC:".$inputFileName);

	//$inputFileName = './uploads/avanzamento_produzione.xlsx';

	$recPcs=caricaDatiDaExcel($inputFileName);
	error_log("caricaDaExcel: nrec=".count($recPcs));

	for ($i=0;$i<count($recPcs);$i++)
	{
		$rec=$recPcs[$i];
		$macchina=$rec["macchina"];
		$pn=$rec["part_number"];
		$contapezzi=$rec["machined_pieces_x_pn"];
		$contapezzi_gg=$rec["machined_pieces_x_pn"];
		$dataora=$rec["order_end_date"];

		error_log($i.":".$dataora." ".$macchina." ".$pn." ".$contapezzi);
		// 
		// ricerca unique_id per la macchina: ricerca su tabella macchina x posizione=macchina
		$res=true;
		$pag=1;
		$where="posizione='".$macchina."'";
		$row=$dbTH->db_get($dbhandle,"macchina","*",$where,$pag,1);
		if (count($row)>0) {
			$unique_id=$row[0]["unique_id"];
			// ricerca ultimo record pcs e controlla p/n: 
			$pag=1;
			$where="unique_id='".$unique_id."'";
			$where=$where." and ";
			$where=$where."status=0";
			$campi="unique_id,order_start_date,part_number,total_pieces,order_end_date,status";
			$row=$dbTH->db_get($dbhandle,"pcs",$campi,$where,$pag,1);
			$updateRec=false;
			$insertRec=false;
			//  se non trovato genera record nuovo con order_start_pieces = 0 e part_number=p/n
			//   se trovato aggiorna total_pieces e order-end-date
			// ricerca su tabella pcs con unique_id, status
			if (count($row)<=0) {
				// non esiste un record status=0
				$insertRec=true;
			}
			else {
				// esiste un record status=0, verifica se cambio pn
				$pnPrecedente=$row[0]["part_number"];
				if (strcmp($pn, $pnPrecedente)== 0) {
					// esiste -> aggiorna end-date e contapezzi
					$campoUpdate=array();
					$valoreCampo=array();

					// valori PK
					$campoUpdate[0]="unique_id";
					$valoreCampo[0]=$row[0]["unique_id"];
					$campoUpdate[1]="order_start_date";
					$valoreCampo[1]=$row[0]["order_start_date"];

					// campi da aggiornare
					$campoUpdate[2]="total_pieces";
					$valoreCampo[2]=$contapezzi;
					$campoUpdate[3]="order_end_date";
					$valoreCampo[3]=$dataora;

					$res = $dbTH->db_update_valori($dbhandle,"pcs",$valoreCampo,$campoUpdate);
				}
				else {
					// chiudi corrente (aggiorna status) e inserisci nuovo
					$campoUpdate=array();
					$valoreCampo=array();
					
					// valori PK
					$campoUpdate[0]="unique_id";
					$valoreCampo[0]=$row[0]["unique_id"];
					$campoUpdate[1]="order_start_date";
					$valoreCampo[1]=$row[0]["order_start_date"];
					
					// campi da aggiornare
					$campoUpdate[2]="status";
					$valoreCampo[2]=1;

					$res = $dbTH->db_update_valori($dbhandle,"pcs",$valoreCampo,$campoUpdate);

					$insertRec=true;
				}
			}
			
			if ($insertRec) {
				$valoreCampo=array();
				for ($j=0;$j<count($campo);$j++) {
					switch($campo[$j]){
						case "unique_id":
							$valoreCampo[$j]=$unique_id;
							break;
						case "status":
							$valoreCampo[$j]=0;
							break;
						case "order_start_date":
							$valoreCampo[$j]=$dataora;
							break;
						case "order_end_date":
							$valoreCampo[$j]=$dataora;
							break;
						case "total_pieces":
							$valoreCampo[$j]=$contapezzi;
							break;
						case "order_start_pieces":
							$valoreCampo[$j]=0;
							break;
						case "part_number":
							$valoreCampo[$j]=$pn;
							break;
						default:
							$valoreCampo[$j]="";
							break;
					}
				}
				$res = $dbTH->db_insert_valori($dbhandle,"pcs",$valoreCampo,$campo);
			}
			// genera sempre 1 record hours/days 
			$partiData=explode(" ", $dataora);
			$data=$partiData[0];
			$ora=substr($partiData[1], 0,2);
			$pag=1;
			$where="unique_id='".$unique_id."'";
			$where=$where." and ";
			$where=$where."date='".$data."'";
			$where=$where." and ";
			$where=$where."hour=".$ora;
			$campiDays="unique_id, date, hour, planned_production_time, operating_time, ideal_operating_time, total_pieces";
			error_log("avanzamentoNC-days-where:".$where);
			$rowDays=$dbTH->db_get($dbhandle,"days",$campiDays,$where,$pag,1);
				$campoDays=array();
				$valoreCampo=array();

				$campoDays[0]="unique_id";
				$valoreCampo[0]=$unique_id;
				$campoDays[1]="date";
				$valoreCampo[1]=$data;
				$campoDays[2]="hour";
				$valoreCampo[2]=$ora;
				$campoDays[3]="total_pieces";
				$valoreCampo[3]=$contapezzi_gg;;
				$campoDays[4]="planned_production_time";
				$valoreCampo[4]=3600;
				$campoDays[5]="operating_time";
				$valoreCampo[5]=3600;
				$campoDays[6]="ideal_operating_time";
				$valoreCampo[6]=3600;

			if (count($rowDays)<=0) {
				$res = $dbTH->db_insert_valori($dbhandle,"days",$valoreCampo,$campoDays);
			}
			else {
				$res = $dbTH->db_update_valori($dbhandle,"days",$valoreCampo,$campoDays);
			}
		}
		else {
			$res=false;
		}

	}


}	
?>
