<?php
//require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/../Header.php';

// si censiscono fra i devicesAux anche le restanti macchine con un unique_id semplificato: 
// {Gn} e descrizione Gn
// si modifica la vista macchine come union tra devices e devicesAux
// si associano anche le macchine plurimandrino agli OP come per le CNC
// i record pcs, hours e days vengono generati da questo programma

$inputFileName = __DIR__ . '/sampleData/avanzamento_produzione.xlsx';
$helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
$spreadsheet = IOFactory::load($inputFileName);
//$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
//var_dump($sheetData);

$riga=2;
$cella=sprintf("Q%s",$riga);
$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
$anno=$cellValue;
$riga=3;
$cella=sprintf("Q%s",$riga);
$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
$giorno_ora=$cellValue;
$partiData=explode(" ",$giorno_ora);
$meseGiorno=explode("-", $partiData[0]);
$dataRilevamento=$anno."-".$meseGiorno[1]."-".$meseGiorno[0];
$ora=explode("h", $partiData[1]);
$oraRilevamento=$ora[1].':00:00';

for ($riga=4;$riga<60;$riga++){
	$pcs=array();
	//$riga=4;
	$cella=sprintf("A%s",$riga);
	$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
	$macchina=$cellValue;
	$cella=sprintf("N%s",$riga);
	$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
	$part_number=$cellValue;
	$cella=sprintf("O%s",$riga);
	$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
	$commessaPref=$cellValue;
	if ($cellValue>""){
		$cella=sprintf("P%s",$riga);
		$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
		$requested_pieces=$cellValue;
		$cella=sprintf("Q%s",$riga);
		$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
		$machined_pieces=$cellValue;
		$cella=sprintf("U%s",$riga);
		$cellValue = $spreadsheet->getActiveSheet()->getCell($cella)->getValue();
		$previous_machined_pieces=$cellValue;

		// ripulisci codice commessa (P R xxx, xxx+yyy)
		$partiCommessa=explode(" ", $commessaPref);
		$commessa=$partiCommessa[count($partiCommessa-1)];
		// 
		// ricerca unique_id per la macchina: ricerca su tabella macchina
		// ricerca ultimo record pcs e controlla p/n: 
		//                  se non trovato genera record nuovo con order_start_pieces = 0
		//                  se trovato aggiorna order-end_pieces 
		// ricerca su tabella pcs con unique_id, status, p/n                          
		// genera 1 record hours/days 

		$pcs["macchina"]=$macchina;
		$pcs["part_number"]=$part_number;
		$pcs["commessa"]=$commessa;
		$pcs["requested_pieces"]=$requested_pieces;
		$pcs["machined_pieces_x_pn"]=$machined_pieces;
		$pcs["machined_pieces_x_day"]=$machined_pieces-$previous_machined_pieces;
		$pcs["order_end_date"]=$dataRilevamento." ".$oraRilevamento;

		var_dump($pcs);
	}
}


