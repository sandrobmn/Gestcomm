<?php		
	function exportReport($nomeReport,$filtro,$row,$tipoAggregazione="",$tipoRappresentazione="")
	{

		// output headers so that the file is downloaded rather than displayed
		if (0){
		// filename for download
		$filename = $nomeReport."_" . date('Ymd') . ".csv";
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		}
		else {
		// filename for download
		$filename = $nomeReport."_" . date('Ymd') . ".xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel");
		}
		$flag = false;
		echo "Criteri di selezione:".$filtro . "\r\n";
		if ($tipoAggregazione!="")
			echo "Tipo di aggregazione:".$tipoAggregazione . "\r\n";
		if ($tipoRappresentazione!="")
			echo "Tipo di rappresentazione:".$tipoRappresentazione . "\r\n";
		foreach($row as $record) {
			if(!$flag) {
			  // display field/column names as first row
			  echo implode("\t", array_keys($record)) . "\r\n";
			  $flag = true;
			}
			echo implode("\t", array_values($record)) . "\r\n";
		  
		}
	}
?>
