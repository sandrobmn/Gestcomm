<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
//use PhpOffice\PhpSpreadsheet\Helper\Sample;



function caricaDatiDaExcel($inputFileName,$esercizio,$da_data,$a_data,$escludiCNC=true,$elencoMacchine=null) {
	//$helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
	$spreadsheet = IOFactory::load($inputFileName);
	//$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
	//var_dump($sheetData);
	
	$colBase=14;
	$col0=$colBase; // N
	$col1=$col0+1; // O
	$col2=$col0+2; // P
	$col3=$col0+3; // Q

	// imposta il foglio attivo
	if ($esercizio > "2017")
		$worksheet="AVANZA PROD ".$esercizio;
	else
		$worksheet=$esercizio;

	$spreadsheet->setActiveSheetIndexByName($worksheet);

	// ricerca il gruppo di colonne relative a $da_data (data inizio)
	$highestRow = $spreadsheet->getActiveSheet()->getHighestRow(); // e.g. 10
	$highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn(); // e.g 'F'
	$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
	
	error_log("caricaDatiDaExcel($esercizio,$da_data,$a_data):$highestRow $highestColumn $highestColumnIndex");

	$indiceGruppo=-1;
	$trovato=false;
	$fineDati=false;
	$nonPresente=false;
	$data=$da_data;
	$data_precedente="";
	do { 
		$indiceGruppo++;
		$col0=$colBase+$indiceGruppo*4; // N
		$col1=$col0+1; // O
		$col2=$col0+2; // P
		$col3=$col0+3; // Q
		 
		$riga=2;
		$cella=sprintf("R%sC%s",$riga,$col3);// colonna Q
		$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col3, $riga)->getValue();
		$anno=$cellValue;
		$riga=3;
		$cella=sprintf("R%sC%s",$riga,$col3);
		$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col3, $riga)->getValue();
		$giorno_ora=strtolower($cellValue);
		$partiData=explode(" ",$giorno_ora);
		$meseGiorno=explode("-", $partiData[0]);
		if (count($meseGiorno)<2) $meseGiorno=explode("/", $partiData[0]);
		if (isset($meseGiorno[1])) {
			$dataRilevamento=sprintf("%4d-%02d-%02d",$anno,$meseGiorno[1],$meseGiorno[0]);
		
		$ora=explode("h", $partiData[1]);
		$oraRilevamento=sprintf("%02d:00:00",$ora[1]);
		
		// se dataRilevamento >= $data : trovato = true
		if ($dataRilevamento <= $data){
			$trovato = true;
		}
		
		// se dataRilevamento < $data : nonPresente = true
			if ($dataRilevamento < $data) {
				$nonPresente = true;
			}
		// se colbase+4 > $highestColumnIndex : fineDati = true
			if ($colBase+4 > $highestColumnIndex) {
				$fineDati=true;
			}
		}
		else {
				$nonPresente=true;
				$fineDati=true;
				echo "errore cella:$cella-$cellValue-data precedente:$data_precedente";
		}
		//echo("caricaDaExcel-cerca data inizio($data):$indiceGruppo,$dataRilevamento ");
		$data_precedente=$dataRilevamento;

	} while (!$trovato && !$fineDati);
	
	// se dataRilevamento < data allora inizio dalla successiva (incontrata in precedenza)
	if ($nonPresente)
		$indiceGruppo--;
	if ($indiceGruppo < 0)
		$trovato=false;

	//error_log("caricaDatiDaExcel:indiceGruppo,trovato,finedati,nonpresente=$indiceGruppo,$trovato $fineDati $nonPresente");

	$recPcs=array();
	$indice=0;

	if ($trovato) {
		$indiceGruppo++;
		$fineElab=false;
		do {
			// imposto le variabili di colonna sul gruppo iniziale (da_data)
			$indiceGruppo--;
			$col0=$colBase+$indiceGruppo*4; // N
			$col1=$col0+1; // O
			$col2=$col0+2; // P
			$col3=$col0+3; // Q

			// costruisco data rilevamento
			$riga=2;
			$cella=sprintf("R%sC%s",$riga,$col3);// colonna Q
			$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col3, $riga)->getValue();
			$anno=$cellValue;
			$riga=3;
			$cella=sprintf("R%sC%s",$riga,$col3);
			$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col3, $riga)->getValue();
			$giorno_ora=strtolower($cellValue);
			$partiData=explode(" ",$giorno_ora);
			$meseGiorno=explode("-", $partiData[0]);
			if (count($meseGiorno)<2)
				$meseGiorno=explode("/", $partiData[0]);
			if (isset($meseGiorno[1])) {
				$dataRilevamento=sprintf("%4d-%02d-%02d",$anno,$meseGiorno[1],$meseGiorno[0]);
				$ora=explode("h", $partiData[1]);
				$oraRilevamento=sprintf("%02d:00:00",$ora[1]);
		
				// se dataRilevamento >= $data : trovato = true
				if ($dataRilevamento > $a_data){
					$fineElab = true;
				}
				if (!$fineElab) {
					for ($riga=4;$riga<60;$riga++){
						$pcs=array();
						//$riga=4;
						$cella=sprintf("R%sC%s",$riga,1);// A (fissa)
						$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $riga)->getValue();
						$macchina=$cellValue;

						//error_log("ciclo for:$riga,$macchina0,$macchina");

						if (strcmp(substr($macchina, 0,8),"macchine") == 0) {
							break;
						}
						else {
							$macchinaDaElaborare=false;
							// macchina=prefisso+posizione
							$prefissoMacchina = substr($macchina, 0,1);
							// ricava posizione e normalizza a due cifre
							$posizioneMacchina = "00".substr($macchina, 1,strlen($macchina)-1);
							$prefissoMacchina = substr($posizioneMacchina,-2);
							
							if ($elencoMacchine != null) {
								// cerca posizione in elenco
								if (in_array($prefissoMacchina, $elencoMacchine))
									$macchinaDaElaborare=true;
							}
							else {
								// se prefisso macchina <> C (cnc) oppure comprende anche cnc
								if ($prefissoMacchina != 'C' || $escludiCNC==false) {
									$macchinaDaElaborare=true;
								}
							}
							if ($macchinaDaElaborare) {
								$cella=sprintf("R%sC%s",$riga,$col0); // N
								$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col0, $riga)->getValue();
								$part_number=$cellValue;
								$cella=sprintf("R%sC%s",$riga,$col1);// O
								$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col1, $riga)->getValue();
								$commessaPref=$cellValue;
								if ($cellValue>""){
									$cella=sprintf("R%sC%s",$riga,$col2); // P 
									$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col2, $riga)->getValue();
									$requested_pieces=$cellValue;
									$cella=sprintf("R%sC%s",$riga,$col3); // Q (base)
									$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col3, $riga)->getValue();
									$machined_pieces=$cellValue;
									$cella=sprintf("R%sC%s",$riga,$col0+4); // R (N+4)
									$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($col0+4, $riga)->getValue();
									$previous_part_number=$cellValue;
									if (strcmp($previous_part_number, $part_number)==0) {
										$cella=sprintf("R%sC%s",$riga,$col3+4); // U (Q+4)
										$cellValue = (int)$spreadsheet->getActiveSheet()->getCellByColumnAndRow($col3+4, $riga)->getValue();
									}
									else {
										$cellValue=0;
									}
									$previous_machined_pieces=$cellValue;

									// ripulisci codice commessa (P R xxx, xxx+yyy)
									$partiCommessa=explode(" ", $commessaPref);
									$commessa=$partiCommessa[count($partiCommessa)-1];

									$pcs["macchina"]=$macchina;
									$pcs["part_number"]=$part_number;
									$pcs["commessa"]=$commessa;
									$pcs["requested_pieces"]=$requested_pieces;
									$pcs["machined_pieces_x_pn"]=$machined_pieces;
									$pcs["machined_pieces_x_day"]=(int)$machined_pieces-(int)$previous_machined_pieces;
									$pcs["order_end_date"]=$dataRilevamento." ".$oraRilevamento;

									$recPcs[$indice++]=$pcs;
								} // commessa valorizzata
							} // escludi cnc
						} // fine dati
					} // for
				}	// fineElab
			} // errore data
			else {
				echo("CaricaDaExcel - costruiscoDataRilevamento: errore: $anno,$giorno_ora");
			}
		} while(!$fineElab && $indiceGruppo>0);
	}
	
	// libera la memoria
	$spreadsheet->disconnectWorksheets();
	unset($spreadsheet);
	
	return $recPcs;
}
?>

