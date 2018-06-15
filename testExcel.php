<?php
//require 'vendor/autoload.php';

//use PhpOffice\PhpSpreadsheet\IOFactory;
//use PhpOffice\PhpSpreadsheet\Helper\Sample;

include_once('caricaDaExcel.php');

//	$helper = new Sample();

	//require __DIR__ . '/../Header.php';

	// si censiscono fra i devicesAux anche le restanti macchine con un unique_id semplificato: 
	// {Gn} e descrizione Gn
	// si modifica la vista macchine come union tra devices e devicesAux
	// si associano anche le macchine plurimandrino agli OP come per le CNC
	// i record pcs, hours e days vengono generati da questo programma

	//$inputFileName = __DIR__ . '/sampleData/avanzamento_produzione.xlsx';
	$inputFileName ='./uploads/avanzamento_produzione.xlsx';

	$recPcs=caricaDatiDaExcel($inputFileName);
	for ($i=0;$i<count($recPcs);$i++)
	{
		$rec=$recPcs[$i];
		$macchina=$rec["macchina"];
		$pn=$rec["part_number"];
		$contapezzi=$rec["machined_pieces_x_pn"];
		$dataora=$rec["order_end_date"];

		echo($dataora." ".$macchina." ".$pn." ".$contapezzi);
		// 
		// ricerca unique_id per la macchina: ricerca su tabella macchina x posizione=macchina
		// ricerca ultimo record pcs e controlla p/n: 
		//                  se non trovato genera record nuovo con order_start_pieces = 0 e part_number=p/n
		//                  se trovato aggiorna total_pieces e order-end-date
		// ricerca su tabella pcs con unique_id, status, p/n

		// genera 1 record hours/days 
	}

?>