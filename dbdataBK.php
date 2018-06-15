<?php

include_once 'rwini.php';

/*include_once 'dbms.php';


    $campidb=array(
			"commessa" => array("id_order","descrizione","data_inizio","data_fine","prodotto","qta"),
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
				"VIEW" => "CREATE VIEW IF NOT EXISTS macchina as select unique_id, description as descrizione, manufacturer as posizione from devices order by posizione",
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
				
			"tipimacchina" => array(
				"campi" => array(
					"id_tipom" => "varchar(90) NOT NULL",'descrizione' => "varchar(90) NOT NULL",
				),
				"PK"=>"id_tipom",
				),
					
			"partprograms" => array(
				"campi" => array(
					"part_number" => "varchar(90) NOT NULL",'id_tipom' => "varchar(90) NOT NULL","path_n" => "varchar(90) NOT NULL","part_program" => "varchar(90) NOT NULL",
				),
				"PK"=>"part_number,id_tipom,path",
				),
					
				"partnumbers" => array(
					"campi" => array(
						"part_number" => "varchar(90) NOT NULL","descrizione" => "varchar(90)",
					),
					"PK"=>"part_number",
					),
						
						);

*/
include_once 'database.php';

	error_log("---- dbdataBK.php");
	
	//$nomedb="\TrakHound\Databases\local-server.db";
	$nomedb="\TrakHound\Backup\local-server-backup.db";
	$nrighexpagina=10;
	
	$filenamePref="\TrakHound\configurazione.ini";
	  $chiave="pathdb";
	  $nomedb_opt=read_ini_section_key($filenamePref,$chiave,"");
	  if ($nomedb_opt>"") $nomedb=$nomedb_opt;

	  $chiave="nrighexpag";
	  $nrighexpag_opt=read_ini_section_key($filenamePref,$chiave,"");
	  if ($nrighexpag_opt>"") $nrighexpag=$nrighexpag_opt;
	  error_log("dbdataBK-lettura parametri:nrighexpag_opt=$nrighexpag_opt,nrighexpag=$nrighexpag");
	  
	  $chiave="pathAvanzamento";
	  $pathAvanzamento_opt=read_ini_section_key($filenamePref,$chiave,"");
	  if ($pathAvanzamento_opt>"") $pathAvanzamento=$pathAvanzamento_opt;
	  
	  //$dbhandle = db_open($nomedb,'');
	  $dbTH =new DbTH();
	  //error_log("dbdataBK:dbTH:".$dbTH->getDbDriverUrl());
	  
	  $dbhDataBK['dbhandle'] = $dbTH->getDbhData();
	  $dbhDataBK['dbschema'] = $dbTH->getSchemadb();
	  $dbhData = $dbhDataBK;
	  $schemadb=$dbhDataBK['dbschema'];

	  //error_log("dbdataBK:".json_encode($dbhDataBK['dbschema']));
	  
	  $res=$dbTH->db_create_table($dbhDataBK,"commessa");
	  $res=$dbTH->db_create_table($dbhDataBK,"macchina");
	  $res=$dbTH->db_create_table($dbhDataBK,"macchinexcommessa");
	  $res=$dbTH->db_create_table($dbhDataBK,"pcs");
	  $res=$dbTH->db_create_table($dbhDataBK,"hours");
	  $res=$dbTH->db_create_table($dbhDataBK,"days");
	  $res=$dbTH->db_create_table($dbhDataBK,"tipimacchina");
	  $res=$dbTH->db_create_table($dbhDataBK,"partnumbers");
	  $res=$dbTH->db_create_table($dbhDataBK,"partprograms");
	  
      //$db   = sqlite_query('hours');
	  // crea table se non esistono

	  
?>