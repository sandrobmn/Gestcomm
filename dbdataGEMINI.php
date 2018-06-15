<?php

include_once 'rwini.php';
/*
include_once 'dbmsMSSQL.php';


	$schemadb=array(
		"articoli" => array(
				"campi" => array(
					"idarticolo" => "varchar(45) NOT NULL","descrizione" => "varchar(45)" 
				),
				"PK"=>"idarticolo",
				),
				
        "materiali" => array(
                    "campi" => array(
                        "idmateriale" => "varchar(45) NOT NULL","descrizione" => "varchar(45)" 
                    ),
                    "PK"=>"idmateriale",
                    ),
                    
        "clienti" => array(
                    "campi" => array(
                        "idcliente" => "int NOT NULL","descrizione" => "varchar(45)" 
                    ),
                    "PK"=>"idcliente",
                    ),
                    
        "fasi" => array(
                        "campi" => array(
                            "idfase" => "int NOT NULL","descrizione" => "varchar(45)" 
                        ),
                        "PK"=>"idfase",
                        ),
                        
        "risorse" => array(
                    "campi" => array(
                                "idrisorsa" => "int NOT NULL","descrizione" => "varchar(45)" 
                            ),
                            "PK"=>"idrisorsa",
                    ),
                            
        "fasi_x_articolo" => array(
                        "campi" => array(
                            "idarticolo" => "varchar(45) NOT NULL",
                            "idfase" => "int NOT NULL",
                            "idrisorsa" => "int NOT NULL",
                            "tempo_attrezzaggio_h" => "int", 
                            "tempo_ciclo_s" => "int", 
                            "note" => "varchar(45)"
                        ),
                        "PK"=>"idarticolo,idfase,idrisorsa",
                        ),
                        
        "materiali_x_articolo" => array(
                            "campi" => array(
                                "idarticolo" => "varchar(45) NOT NULL",
                                "idmateriale" => "varchar(45) NOT NULL",                                "tempo_attrezzaggio_h" => "int", 
                                "qta_unitaria" => "int"
                            ),
                            "PK"=>"idarticolo,idmateriale",
                            ),
                            
        "ordini_clienti" => array(
                    "campi" => array(
                                    "idordine_cliente" => "int NOT NULL", 
                                    "idcliente" => "int NOT NULL",   
                                    "idarticolo" => "varchar(45) NOT NULL",
                                    "qta_richiesta" => "int",
                                    "data_consegna_prevista" => "date NOT NULL", 
                                    "qta_prodotta" => "int",
                                    "qta_consegnata" => "int ",
                                    "data_consegna_effettiva" => "date NOT NULL",
                                    "note" => "varchar(45)",
                                    "idop" => "varchar(45) NOT NULL"   
                                ),
                                    "PK"=>"idordine_cliente",
                                    ),
                                    
        "op" => array(
                    "campi" => array(
                        "idop" => "varchar(45) NOT NULL",    
                        "idarticolo" => "varchar(45) NOT NULL",
                        "data" => "date NOT NULL", 
                        "qta_richiesta" => "int",
                        "qta_prodotta" => "int "
                        ),
                        "PK"=>"idop",
                        ),
                        
        "movimenti_magazzino" => array(
                            "campi" => array(
                                "registrazione" => "int NOT NULL",    
                                "data_registrazione" => "date NOT NULL", 
                                "causale" => "varchar(45) NOT NULL",
                                "descrizione" => "varchar(45) NOT NULL",
                                "deposito" => "int",
                                "numero_documento" => "int ",
                                "data_documento" => "date " 
                                ),
                                "PK"=>"registrazione",
                                ),
                                
        "movimenti_magazzino_corpo" => array(
                                    "campi" => array(
                                        "registrazione" => "int NOT NULL",    
                                        "riga" => "int NOT NULL",    
                                        "articolo" => "varchar(45) NOT NULL",
                                        "quantita" => "int",
                                        "esercizio" => "int"
                                        ),
                                        "PK"=>"registrazione,riga",
                                        ),
                                        
                                                
					);

*/
include_once 'database.php';

	error_log("---- dbdataGEMINI.php");
	
	//$nomedb="\TrakHound\Databases\local-server.db";
	$nomedb="\TrakHound\Backup\local-server-backup.db";
	$nrighexpagina=10;
	
	$filenamePref="\TrakHound\configurazione.ini";
	  $chiave="pathdbGEMINI";
	  $nomedb_opt=read_ini_section_key($filenamePref,$chiave,"");
	  if ($nomedb_opt>"") $nomedb=$nomedb_opt;

	  $chiave="nrighexpag";
	  $nrighexpag_opt=read_ini_section_key($filenamePref,$chiave,"");
	  if ($nrighexpag_opt>"") $nrighexpag=$nrighexpag_opt;
	  
      //$dbhandle = db_open($nomedb,'');
	  /*$dbhDataBK['dbhandle'] = db_open($nomedb,'');
	  $dbhDataBK['dbschema'] = $schemadb;
      $dbhData = $dbhDataBK;*/

      $dbGMN =new DbGemini();
	  
	  $dbhDataBK['dbhandle'] = $dbGMN->getDbhData();
	  $dbhDataBK['dbschema'] = $dbGMN->getSchemadb();
	  $dbhData = $dbhDataBK;
	  $schemadb=$dbhDataBK['dbschema'];


	  /*
	  $res=db_create_table($dbhDataBK,"articoli");
	  $res=db_create_table($dbhDataBK,"macchina");
	  $res=db_create_table($dbhDataBK,"macchinexcommessa");
	  $res=db_create_table($dbhDataBK,"pcs");
	  $res=db_create_table($dbhDataBK,"hours");
	  $res=db_create_table($dbhDataBK,"days");
	  $res=db_create_table($dbhDataBK,"tipimacchina");
	  $res=db_create_table($dbhDataBK,"partnumbers");
	  $res=db_create_table($dbhDataBK,"partprograms");
      */
      
      //$db   = sqlite_query('hours');
	  // crea table se non esistono

	  
?>