<?php

include 'rwini.php';

    $campidb=array(
			"commessa" => array("id_order","descrizione","data_inizio","data_fine","prodotto","qta"),
			"macchina" => array('unique_id','descrizione','posizione'),
			"macchinexcommessa" => array('id_order','unique_id','product','CNCprogram', 'requested_pieces','planned_start_date','planned_end_date','status'),
			"pcs" => array("unique_id",'order_start_date',"total_pieces","good_pieces",'order_start_pieces','order_start_program','order_end_date','part_number','status'),
			"hours" => array("unique_id",'date',"hour","planned_production_time",'operating_time','ideal_operating_time','total_pieces','good_pieces',			'total_time','active','idle','alert','production','setup','teardown','maintenance','process_development'),
			"days" => array("unique_id",'date',"hour","planned_production_time",'operating_time','ideal_operating_time','total_pieces','good_pieces',			'total_time','active','idle','alert','production','setup','teardown','maintenance','process_development')
			);
	$campipk=array("commessa"=>1,"macchina"=>1,"macchinexcommessa" =>2,"pcs" => 2,"hours" =>3,"days" =>3)	;
	
	$schemadb=array(
			"commessa" => array(
				"campi" => array(
					"id_order" => "varchar(90) NOT NULL","descrizione" => "varchar(90)","data_inizio" => "varchar(10)","data_fine" => "varchar(10)","prodotto" => "varchar(90)","qta" => "int(10)", 
				),
				"PK"=>"id_order",
				),
				
			"macchina" => array(
				"campi" => array(
					"unique_id" => "varchar(90) NOT NULL","descrizione" => "varchar(90)","posizione" => "varchar(90)",
				),
				"PK"=>"unique_id",
				),

			"macchinexcommessa" => array(
				"campi" => array(
					"id_order" => "varchar(90) NOT NULL","unique_id" => "varchar(90) NOT NULL","product" => "varchar(90)","CNCprogram" => "varchar(90)","requested_pieces" => "int(10)","planned_start_date" => "varchar(10)","planned_end_date" => "varchar(10)","status"  => "int(1)",
				),
				"PK"=>"id_order,unique_id",
				),
				
			"pcs" => array(
				"campi" => array(
					"unique_id" => "varchar(90) NOT NULL",'order_start_date' => "varchar(90) NOT NULL","total_pieces" => "int(10)","good_pieces" => "int(10)",'order_start_pieces' => "int(10)",'order_start_program' => "varchar(90)",'order_end_date' => "varchar(90)",'part_number' => "varchar(90)",'status' => "varchar(90)",
				),
				"PK"=>"unique_id,order_start_date",
				),
				
			"hours" => array(
				"campi" => array(
					"unique_id" => "varchar(90) NOT NULL",'date' => "varchar(90) NOT NULL","hour" => "int(2) NOT NULL","planned_production_time" => "double",'operating_time' => "double",'ideal_operating_time' => "double",'total_pieces' => "int(10)",'good_pieces' => "int(10)",			'total_time' => "double",'active' => "double",'idle' => "double",'alert' => "double",'production' => "double",'setup' => "double",'teardown' => "double",'maintenance' => "double",'process_development' => "double",
				),
				"PK"=>"unique_id,date,hour",
				),
				
			"days" => array(
				"campi" => array(
					"unique_id" => "varchar(90) NOT NULL",'date' => "varchar(90) NOT NULL","hour" => "int(2) NOT NULL","planned_production_time" => "double",'operating_time' => "double",'ideal_operating_time' => "double",'total_pieces' => "int(10)",'good_pieces' => "int(10)",			'total_time' => "double",'active' => "double",'idle' => "double",'alert' => "double",'production' => "double",'setup' => "double",'teardown' => "double",'maintenance' => "double",'process_development' => "double",
				),
				"PK"=>"unique_id,date,hour",
				),
				
			);
/*
	var campo = array('unique_id','descrizione','posizione');
	var tabella="macchina";

	var campo = array('id_order','unique_id','requested_pieces');
	var tabella="macchinexcommessa";
			
		var campo = array('id_order','unique_id','requested_pieces');
	var tabella="pcs";

	$campo = array("id_order","descrizione","data_inizio","data_fine","prodotto","qta");
	$tabella="commessa";
*/





function db_open($location,$mode) 
{ 
    $handle = new SQLite3($location); 
    return $handle; 
     
} 
function db_close($dbhandle)
{ 
    $dbhandle->close(); 
}

function db_exec($dbhandle,$query) 
{ 
	//echo "----db_exec(,".$query.")----";
	
    $array['dbhandle'] = $dbhandle; 
    $array['query'] = $query; 
    $result = $dbhandle->exec($query); 
	//echo "result:".$result;
	error_log("exec:".$query);
    return $result; 
} 

function db_query($dbhandle,$query) 
{ 
	//echo "---db_query(,".$query.")-----";
	
    $array['dbhandle'] = $dbhandle; 
    $array['query'] = $query; 
    $result = $dbhandle->query($query); 
	//echo "result:".$result;
    return $result; 
} 
function db_fetch_array(&$result,$type) 
{ 
    #Get Columns 
    $i = 0; 
    while ($result->columnName($i)) 
    { 
        $columns[ ] = $result->columnName($i); 
        $i++; 
    } 
    
    $resx = $result->fetchArray(SQLITE3_ASSOC); 
    return $resx; 
} 

function db_create_table($dbhandle,$myTable)
{	
global $schemadb;

	$schema=$schemadb[$myTable];
//	$campiTab=$schema["campi"];
//	$pk=$schema["PK"];
//	echo "create table:".$myTable." PK:".$pk;
	
	switch ($myTable) {
    case "commessa":
				$query = "CREATE TABLE IF NOT EXISTS `commessa` (" .

                    "`id_order` varchar(90), " .
					"`descrizione` varchar(90), " .
					"`data_inizio` varchar(90), " .
                    "`data_fine` varchar(90), " .
                    "`prodotto` varchar(90), " .
                    "`qta` int(10), " .
					"PRIMARY KEY (`id_order`))";
				break;
    case "macchina":
				 $query = "CREATE TABLE IF NOT EXISTS `macchina` (" .

                    "`unique_id` varchar(90), " .
					"`descrizione` varchar(90), " .
					"`posizione` varchar(90), " .
					"PRIMARY KEY (`unique_id`))";
				break;
    case "pcs":
				 $query = "CREATE TABLE IF NOT EXISTS `pcs` (" .

                    "`unique_id` varchar(90), " .
					"`total_pieces` int(10), " .
					"`good_pieces` int(10), " .
					"`order_start_pieces` int(10), " .
                    "`order_start_program` varchar(90), " .
                    "`order_start_date` varchar(90), " .
                    "`order_end_date` varchar(90), " .
                    "`par_number` varchar(90), " .
                    "`status` varchar(90), " .
					"PRIMARY KEY (`unique_id`,`order_start_date`))";
				break;
    case "macchinexcommessa":
				 $query = "CREATE TABLE IF NOT EXISTS `macchinexcommessa` (" .

					"`id_order` varchar(90), " .
                    "`unique_id` varchar(90), " .
                    "`product` varchar(90), " .
                    "`CNCprogram` varchar(90), " .
					"`requested_pieces` int(10), " .
                    "`planned_start_date` varchar(10), " .
                    "`planned_end_date` varchar(10), " .
					"`status` int(1), " .
					"PRIMARY KEY (`id_order`,`unique_id`))";
				break;
	}
	//echo "db_create:".$query;
	
	return db_exec($dbhandle,$query);
} 

function db_insert_valori($dbhandle,$tabella,$valoreCampo,$campo)
{
	$numCampi=count($campo);
	$query="insert into ".$tabella." ";
	$query=$query."(";
		for ($i=0;$i<$numCampi;$i++)
		{
			if ($i>0) $query=$query.",";
			$query=$query."'".$campo[$i]."'";
		}
	$query=$query.")";
	$query=$query." values ";
	$query=$query."(";
	for ($i=0;$i<count($valoreCampo);$i++)
		{
			if ($i>0) $query=$query.",";
			$query=$query."'".$valoreCampo[$i]."'";
		}
	$query=$query.")";
	
	return db_exec($dbhandle,$query);

}

function db_delete_valori($dbhandle,$tabella,$valoreCampo,$campo)
{
global $schemadb;

	$schema=$schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoPKs=$schema["PK"];

	$numCampi=count($campo);
	$query="delete from ".$tabella." ";
	
	$where="";
	
	$j=0;$k=0;	
	for ($i=0;$i<$numCampi;$i++)
	{
		$pos = strpos($elencoPKs, $campo[$i]);
		if ($pos !== false)
		{
			if ($k>0) $where=$where." and ";
			$where=$where.$campo[$i];
			$where=$where." = ";
			$where=$where."'".$valoreCampo[$i]."'";
			$k=$k+1;
		}
			
			
	}

	$query=$query." where (".$where.")";
	
	return db_exec($dbhandle,$query);

}


function db_update_valori($dbhandle,$tabella,$valoreCampo,$campo)
{
global $campipk,$schemadb;

	$schema=$schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoPKs=$schema["PK"];

	$numCampi=count($campo);
	$query="update ".$tabella." ";
	$query=$query."set ";
	$where="";
	
	$j=0;$k=0;	
	for ($i=0;$i<$numCampi;$i++)
	{
		$pos = strpos($elencoPKs, $campo[$i]);
		if ($pos === false)
		{
			if ($j>0) $query=$query.",";
			$query=$query."'".$campo[$i]."'";
			$query=$query." = ";
			$query=$query."'".$valoreCampo[$i]."'";
			$j=$j+1;
		}
		else
		{
			if ($k>0) $where=$where." and ";
			$where=$where.$campo[$i];
			$where=$where." = ";
			$where=$where."'".$valoreCampo[$i]."'";
			$k=$k+1;
		}
			
			
	}

	$query=$query." where (".$where.")";
	
	return db_exec($dbhandle,$query);

}

function db_select($dbhandle,$tabella,$campi,$pag,$nrighexpag)
{
	return db_get($dbhandle,$tabella,$campi,"",$pag,$nrighexpag);
}

function db_get($dbhandle,$tabella,$campi,$where,&$pag,$nrighexpag,$ordinamento="",$raggruppamento="")
{
	$query="select ".$campi." from ".$tabella;
	if (strlen($where))
		$query=$query." where (".$where." )";
	if (strlen($ordinamento)){
		if (strlen($raggruppamento)==0) {
			$raggruppamento = $ordinamento;
			$raggruppamento = str_replace(" asc ", "", $raggruppamento);
			$raggruppamento = str_replace(" desc ", "", $raggruppamento);
		}
		$query=$query." group by ".$raggruppamento." "." order by ".$ordinamento." ";
	}
	// calcola n. record totali e n. ultima pagina
	$queryNumRec="select count(*) from (".$query.")";
	$resQuery1 = $dbhandle->querySingle($queryNumRec);
	$numRec=$resQuery1;
	$numUltimaPag=ceil($numRec/$nrighexpag);
	if ($pag>$numUltimaPag) $pag=$numUltimaPag;
	
	//error_log( "numrec:".$numRec.",".$resQuery1);
	
	// imposta limiti
	$limite=($pag-1)*$nrighexpag;
	//error_log( "limite:".$limite.",".$nrighexpag);
	$query=$query." LIMIT ".$limite." , ".$nrighexpag;
	error_log($query);
	$stmt = $dbhandle->prepare($query);
	$row = array(); 
	$field = array();
	
	if ($stmt){
		$res = $stmt->execute();
		
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
	return $row; 
}

function preparaRisposta($res, $msg,$op,$pagina=0)
{
	$risposta = array();
	if ($res)
		$risposta["esito"]=true;
	else
		$risposta["esito"]=false;
	$risposta["richiesta"]=$op;
	$risposta["dati"]=$msg;
	if ($pagina>0) $risposta["pagina"]=$pagina;
	
	return $risposta;
}

	$nomedb="\TrakHound\Backup\local-server-backup.db";
	$nrighexpagina=10;
	
	$filename="gestcomm.ini";
	  $chiave="pathdb";
	  $nomedb_opt=read_ini_section_key($filename,$chiave,"");
	  if ($nomedb_opt>"") $nomedb=$nomedb_opt;

	  $chiave="nrighexpag";
	  $nrighexpag_opt=read_ini_section_key($filename,$chiave,"");
	  if ($nrighexpag_opt>"") $nrighexpag=$nrighexpag_opt;
	  
      $dbhandle = db_open($nomedb,'');
	  
	  
	  $res=db_create_table($dbhandle,"commessa");
	  $res=db_create_table($dbhandle,"macchina");
	  $res=db_create_table($dbhandle,"pcs");
	  $res=db_create_table($dbhandle,"macchinexcommessa");
	  
      //$db   = sqlite_query('hours');
	  // crea table se non esistono

	  
?>