<?php
include_once 'rwini.php';
abstract class DataBase {
    private $schemadb;
    private $dbDriverUrl;
    private $dbhData;

    public function __construct($nomedb,$schemadb){
        $this->dbDriverUrl=$nomedb;
        $this->schemadb=$schemadb;
    
        $mode="";
        $this->dbhData=$this->db_open($this->dbDriverUrl,$mode) ;
    }

/* ---------- ----------------------*/

public function getSchemaDb(){
    return $this->schemadb;
    
}

public function getSchemaTabella($tabella){
    return $this->schemadb[$tabella];
    
}

public function getDbhData(){
    return $this->dbhData;
    
}

public function getDbDriverUrl(){
    return $this->dbDriverUrl;
    
}

/* ---------- ----------------------*/
private function db_open($location,$mode) 
{ 
    /*
    $handle = new SQLite3($location); 
	$handle->busyTimeout(5000);
	// WAL mode has better control over concurrency.
	// Source: https://www.sqlite.org/wal.html
	$handle->exec('PRAGMA journal_mode = wal;');
    return $handle; 
    */
    //error_log("db_open($location , $mode)");
    $servername = "localhost";
    $username = "username";
    $password = "password";

	// da location estrarre servername, dbname, username, password
	error_log("location:".$location);

    $prm=array();
    $prm=explode ("," , $location );
    $driver_server_db=$prm[0];
    $prmu=explode("=",$prm[1]); 
    $username=$prmu[1];
    $prmp=explode("=",$prm[2]); 
    $password=$prmp[1];
    
    try {
        //$conn = new PDO("mysql:host=$servername;dbname=myDB", $username, $password);
        //$db = new PDO("sqlsrv:Server=YouAddress;Database=YourDatabase", "Username", "Password");
        $handle = new PDO($driver_server_db, $username, $password);
        // set the PDO error mode to exception
        $handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // ***************** echo "Connected successfully"; 
        }
    catch(PDOException $e)
        {
        echo "Connection failed (".$driver_server_db."): " . $e->getMessage();
        }

        return $handle; 
} 

public function db_close($dbh)
{ 
    /*
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;

    $dbhandle = null; 
    */
    $this->dbhData = null; 

}

abstract public function db_fetch_array(&$result,$type) ;

abstract function db_execStatement($dbh,$stmt);

abstract public function db_create_table($dbh,$myTable);

abstract public function db_insert_replace_valori($dbh,$tabella,$valoreCampo,$campo);

abstract public function db_get($dbh,$tabella,$campi,$where,&$pag,$nrighexpag,$ordinamento="",$raggruppamento="",&$npag=-1);

/* ------------- funzioni pubbliche ----------------------------*/
public function db_exec($dbhandle,$query) 
{ 
	//echo "----db_exec(,".$query.")----";
	/*
    $array['dbhandle'] = $dbhandle; 
    $array['query'] = $query; 
    //$result = $dbhandle->exec($query); 
 
	//echo "result:".$result;
	if (substr($query,0,6)!="CREATE")
		error_log("exec:".$query);
    return $result; 
    */
    $dbhandle=$this->dbhData;
    try {
        // use exec() because no results are returned
        $dbhandle->exec($query);        
        $result=true;
    }
    catch(PDOException $e)
    {
        echo $query . "<br>" . $e->getMessage();
        $result=false;
    }
    return $result; 

} 

public function db_execReturned($dbhandle,$query) 
{ 
    $dbhandle=$this->dbhData;
    try {
        // Prepare statement
        $stmt = $dbhandle->prepare($query);

        // execute the query
        $stmt->execute();

        // echo a message to say the UPDATE succeeded
        echo $stmt->rowCount() . " records UPDATED successfully";        
        $result=true;
    }
    catch(PDOException $e)
    {
        echo $query . "<br>" . $e->getMessage();
        $result=false;
    }
    return $result; 

} 

public function db_query($dbhandle,$query) 
{ 
	//echo "---db_query(,".$query.")-----";
	/*
    $array['dbhandle'] = $dbhandle; 
    $array['query'] = $query; 
    */
    $dbhandle=$this->dbhData;
    $result = $dbhandle->query($query); 
	//echo "result:".$result;
    return $result; 
} 

public function db_insert_valori($dbh,$tabella,$valoreCampo,$campo)
{
	/*  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;*/
      $dbhandle=$this->dbhData;

	$schema=$this->schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoCampiPK=$schema["PK"];

	
	$numCampi=count($campo);
	$query="insert into ".$tabella." ";
	$query=$query."(";
		for ($i=0;$i<$numCampi;$i++)
		{
			if ($i>0) $query=$query.",";
			$query=$query.$campo[$i];
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
	
	return $this->db_exec($dbhandle,$query);

}

public function db_delete_valori($dbh,$tabella,$valoreCampo,$campo)
{
	/*  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;*/
      $dbhandle=$this->dbhData;

	$schema=$this->schemadb[$tabella];
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
	
	return $this->db_exec($dbhandle,$query);

}

public function db_update_valori($dbh,$tabella,$valoreCampo,$campo)
{
	/*  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;*/
      $dbhandle=$this->dbhData;

	$schema=$this->schemadb[$tabella];
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
			//$query=$query."'".$campo[$i]."'";
			$query=$query.$campo[$i];
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
	
	return $this->db_execReturned($dbhandle,$query);

}

public function db_select($dbhandle,$tabella,$campi,$pag,$nrighexpag,&$npag=0)
{
	$npag=0;
	return $this->db_get($dbhandle,$tabella,$campi,"",$pag,$nrighexpag,"","",$npag);
}



}

class DataBaseMSSQL extends DataBase {
    
/* ---------- ----------------------*/

function db_execStatement($dbh,$stmt)
{ 
/*	  $dbhandle=$dbh['dbhandle'] ;
      $schemadb=$dbh['dbschema'] ;*/
      
    try {
        $stmt->execute();
    }
    catch(PDOException $e)
        {
        echo "db_execStatement" . "<br>" . $e->getMessage();
        return false;
    }
      return true;
}

public function db_create_table($dbh,$myTable)
{	
	/*  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;*/


	$schema=$this->schemadb[$myTable];
//	$campiTab=$schema["campi"];
//	$pk=$schema["PK"];
//	echo "create table:".$myTable." PK:".$pk;
	if (isset($schema["VIEW"]))
		$tipo="view";
	else
		$tipo="table";
	switch ($tipo) {
		case "view":
			$query=$schema["VIEW"];
			break;
		default:

			$campiTab=$schema["campi"];
			$elencoPKs=$schema["PK"];
			$query = "CREATE TABLE IF NOT EXISTS ".$myTable." (" ;
			foreach($campiTab as $x => $x_value) {
				$query=$query."'".$x."' ".$x_value.",";
			}	
			$query=$query."PRIMARY KEY (".$elencoPKs."))";
		break;
	}
	
	return $this->db_exec($dbhandle,$query);
} 

public function db_fetch_array(&$result,$type) 
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


public function db_insert_replace_valori($dbh,$tabella,$valoreCampo,$campo)
{
	/*  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;*/
      $dbhandle=$this->dbhData;

	$schema=$this->schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoCampiPK=$schema["PK"];

	
	$numCampi=count($campo);
	$query="insert or replace into ".$tabella." ";
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
	
	return $this->db_exec($dbhandle,$query);

}


public function db_get($dbh,$tabella,$campi,$where,&$pag,$nrighexpag,$ordinamento="",$raggruppamento="",&$npag=-1)
{
/*	$dbhandle=$dbh['dbhandle'] ;
	$schemadb=$dbh['dbschema'] ;*/
    $dbhandle=$this->getDbhData();
    $schemadb=$this->getSchemadb();

	$query="select ".$campi." from ".$tabella;
	if (strlen($where))
		$query=$query." where (".$where." )";
    if (strlen($raggruppamento))
		$query=$query." group by ".$raggruppamento;
		
	if (strlen($ordinamento)){
		/*
		if (strlen($raggruppamento)==0) {
           
			$raggruppamento = $ordinamento;
			$raggruppamento = str_replace(" asc ", "", $raggruppamento);
            $raggruppamento = str_replace(" desc ", "", $raggruppamento);
               
        }
        if (0 && strlen($raggruppamento))
			$query=$query." group by ".$raggruppamento;
		*/
        $query=$query." order by ".$ordinamento." ";
	}
	// calcola n. record totali e n. ultima pagina
	if ( $nrighexpag >1 && $npag <= 0) {
        if (strlen($raggruppamento)>0)
            $campiNumRec=str_replace("group by","",$raggruppamento);
        else
            $campiNumRec=$campi;
        $queryNumRec="select ".$campiNumRec." from ".$tabella;
        if (strlen($raggruppamento)>0)
		    $queryNumRec=$queryNumRec." group by ".$raggruppamento;
        $queryNumRec="select count(*) from (".$queryNumRec.") as xyz";
        error_log("db_get:".$queryNumRec);
        try {
            $stmt = $dbhandle->prepare($queryNumRec); 
            $stmt->execute();

            // set the resulting array to associative
            $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
            foreach($stmt->fetchAll() as $k0=>$v0) { 
                foreach($v0 as $k=>$v) { 
                    $numRec=$v;
                }
            }

        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        
		$numUltimaPag=ceil($numRec/$nrighexpag);
		$npag=$numUltimaPag;
        if ($pag > $numUltimaPag) 
            $pag=$numUltimaPag;
	}
	else {
		$numUltimaPag = $npag;
	}
	
	//error_log( "numrec:".$numRec.",".$resQuery1);
	
    // imposta limiti se tabella semplice senza join o get di un record
	
	if ( $nrighexpag >1) {
        if ($pag <= 0) $pag=1;
        //----$limite=($pag-1)*$nrighexpag;
        //error_log( "limite:".$limite.",".$nrighexpag);
        //$query=$query." LIMIT ".$limite." , ".$nrighexpag;
		//++++++++++++$query=$query." OFFSET ".$limite." ROWS FETCH NEXT ".$nrighexpag." ROWS ONLY";
		$query=$this->impostaLimiti($tabella,$campi,$where,$ordinamento,$raggruppamento,$pag,$nrighexpag);
		error_log("query pag:".$query);
    }
	error_log($query);
	$stmt = $dbhandle->prepare($query);
	$row = array(); 
	$field = array();
	
	if (!($stmt === false) ){
        //---error_log("db_get 1 ");
		$res = $stmt->execute();
        // set the resulting array to associative
        $res = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
        //---error_log("db_get 2 ");
        $result = $stmt->fetchAll();

        return $result;

	 }
	return $row; 
}

private function impostaLimiti($tabella,$campi,$where,$ordinamento,$raggruppamento,$pag,$nrighexpag)
{
	$p=strpos($tabella,"join"); 
	if ($p === false) {
		$tabellaBase=$tabella;
		$tabellaJoin="";
	}
	else {
		$p1=strpos($tabella," as ");
		if (!($p1 === false) && $p1< $p)
			$p=$p1;
		$tabellaBase=substr($tabella,0,$p);
		$tabellaJoin=substr($tabella,$p,strlen($tabella)-$p);
	}
	
	$c=explode(",",$campi);
	$idP=explode(" as ",$c[0]);
	$id=$idP[0];
	
	error_log("id:".$c[0]."-".$idP[0]);

	$query="WITH CTE AS ";
	$query .= "( ";
	$query .= "SELECT ";
	$query .= "ROW_NUMBER() OVER ( ORDER BY $ordinamento ) AS RowNum ,";
	$query .= "*";
	$query .=" FROM ".$tabellaBase;
	if (strlen($where))
		$query .=" WHERE (".$where." )";

	/*
	if (strlen($raggruppamento))
		$query=$query." GROUP BY (".$raggruppamento." )";
	*/
	$query .= " ) ";
	$query .= "SELECT ";
	$query .= $campi;
	$query .= " FROM CTE ".$tabellaJoin;
	$query .=" WHERE ";
	$query .="(RowNum >= $nrighexpag * ($pag - 1) )";
	$query .=" AND ";
	$query .="(RowNum <= $nrighexpag * $pag )";
	$query .=" ORDER BY RowNum ";
	/*
	if (strlen($ordinamento))
		$query=$query.",".$ordinamento;
	*/
	
	return $query;
}
/*
    WITH CTE AS
    (
      SELECT 
        ROW_NUMBER() OVER ( ORDER BY [MasterJobId] ) AS RowNum ,
        MasterJob.Title, MasterJob.CompanyName, MasterJob.ShortDesc,      
        MasterJob.Url,MasterJob.PostedTime, MasterJob.Location, JobBoard.JobBoardName  
      FROM MasterJob 
        LEFT JOIN JobBoard ON MasterJob.JobBoardId = JobBoard.JobBoardId
      WHERE 
      (MasterJob.Title LIKE '%' + @EnteredKeyword + '%')
      AND( MasterJob.Location LIKE '%' + @EnteredLocation + '%' )
    )
    SELECT 
      Title, CompanyName, ShortDesc, Url, PostedTime, Location, JobBoardName
    FROM CTE 
    WHERE 
      (RowNum > @PageSize * (@PageNumber - 1) )
      AND 
      (RowNum <= @PageSize * @PageNumber )
    Order By RowNum 

*/	  


}

class DataBaseMYSQL extends DataBase {
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
    
    function db_execStatement($dbh,$query)
    { 
        /*  $dbhandle=$dbh['dbhandle'] ;
          $schemadb=$dbh['dbschema'] ;*/
          $dbhandle=$this->getDbhData();

          try {
            $stmt = $dbhandle->prepare($query);
          
            $result = $stmt->execute();
          }
        catch(PDOException $e)
            {
            echo "db_execStatement" . "<br>" . $e->getMessage();
            return false;
        }
          return true;
    }
    
    
    public function db_create_table($dbh,$myTable)
    {	
        /*  $dbhandle=$dbh['dbhandle'] ;
          $schemadb=$dbh['dbschema'] ;*/
        $dbhandle=$this->getDbhData();
    
        $schema=$this->getSchemaTabella($myTable);
    //--    error_log("DataBaseMYSQL:db_create_table($myTable):".json_encode($schema));
    //	$campiTab=$schema["campi"];
    //	$pk=$schema["PK"];
    //	echo "create table:".$myTable." PK:".$pk;
        if (isset($schema["VIEW"]))
            $tipo="view";
        else
            $tipo="table";
        switch ($tipo) {
            case "view":
                $query=$schema["VIEW"];
                break;
            default:
    
                $campiTab=$schema["campi"];
                $elencoPKs=$schema["PK"];
                $query = "CREATE TABLE IF NOT EXISTS ".$myTable." (" ;
                foreach($campiTab as $x => $x_value) {
                    $query=$query."'".$x."' ".$x_value.",";
                }	
                $query=$query."PRIMARY KEY (".$elencoPKs."))";
            break;
        }
        
        return $this->db_exec($dbhandle,$query);
    } 
    
        function db_insert_replace_valori($dbh,$tabella,$valoreCampo,$campo)
    {
        /*  $dbhandle=$dbh['dbhandle'] ;
          $schemadb=$dbh['dbschema'] ;*/
          $dbhandle=$this->dbhData;
          $schemadb=$this->schemadb;
    
        $schema=$schemadb[$tabella];
        $campiTab=$schema["campi"];
        $elencoCampiPK=$schema["PK"];
    
        
        $numCampi=count($campo);
        $query="insert or replace into ".$tabella." ";
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
        
        return $this->db_exec($dbhandle,$query);
    
    }
    
    
    function db_get($dbh,$tabella,$campi,$where,&$pag,$nrighexpag,$ordinamento="",$raggruppamento="",&$npag=-1)
    {
        /*$dbhandle=$dbh['dbhandle'] ;
        $schemadb=$dbh['dbschema'] ;*/
        $dbhandle=$this->getDbhData();
        $schemadb=$this->getSchemadb();
        
        error_log("db_get($tabella):$where,$pag,$nrighexpag,$ordinamento,$raggruppamento,$npag");
        //error_log("campi:".$campi." where:".$where." ord:".$ordinamento." grp:".$raggruppamento);
    
        $query="select ".$campi." from ".$tabella;
        if (strlen($where))
            $query=$query." where (".$where." )";
        $queryBase=$query;
        if (strlen($ordinamento)){
            /*
            if (strlen($raggruppamento)==0) {
                $raggruppamento = $ordinamento;
                $raggruppamento = str_replace(" asc ", "", $raggruppamento);
                $raggruppamento = str_replace(" desc ", "", $raggruppamento);
            }
            */
            if (strlen($raggruppamento)==0) {
                /*
                $raggruppamento = $ordinamento;
                $partiGroupBy=explode(",",$raggruppamento);
                $groupBy="";
                for ($i=0;$i<count($partiGroupBy);$i++){
                    $parti=explode(" ",$partiGroupBy[$i]);
                    if ($parti[0]>"") {
                        if ($groupBy>"") $groupBy .= ",";
                        $groupBy .= $parti[0];
                    }
                }
                $raggruppamento=$groupBy;
                */
            }
    
            if (strlen($raggruppamento))
			    $query = $query." group by ".$raggruppamento;
		
		    $query = $query." order by ".$ordinamento." ";
        }
        // calcola n. record totali e n. ultima pagina
        if ($nrighexpag >1 && $npag <= 0) {
            $queryNumRec=$queryBase;
            /*
            if (strlen($raggruppamento)>0)
                $campiNumRec=str_replace("group by","",$raggruppamento);
            else
                $campiNumRec=$campi;
            $queryNumRec="select ".$campiNumRec." from ".$tabella;
            */
            if (strlen($raggruppamento)>0)
                $queryNumRec=$queryNumRec." group by ".$raggruppamento;
            
            $queryNumRec="select count(*) from (".$queryNumRec.") as xyz";
            error_log("db_get:".$queryNumRec);
            try {
                $stmt = $dbhandle->prepare($queryNumRec); 
                $stmt->execute();
    
                // set the resulting array to associative
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
                foreach($stmt->fetchAll() as $k0=>$v0) { 
                    foreach($v0 as $k=>$v) { 
                        $numRec=$v;
                    }
                }
    
            }
            catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            
            $numUltimaPag=ceil($numRec/$nrighexpag);
            $npag=$numUltimaPag;
            if ($pag > $numUltimaPag) 
                $pag=$numUltimaPag;
        }
        else {
            $numUltimaPag = $npag;
        }
        
        //error_log( "numrec:".$numRec.",".$resQuery1);
        
        // imposta limiti
        if ($nrighexpag >1 ) {
            if ($pag<1) $pag=1;
            $limite=($pag-1)*$nrighexpag;
            //error_log( "limite:".$limite.",".$nrighexpag);
            $query=$query." LIMIT ".$nrighexpag." OFFSET ".$limite;
        }
        error_log($query);
        $stmt = $dbhandle->prepare($query);
        $row = array(); 
        $field = array();
        
        if (!($stmt === false) ){
            //error_log("db_get 1 ");
            $res = $stmt->execute();
            // set the resulting array to associative
            $res = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
            //error_log("db_get 2 ");
            $result = $stmt->fetchAll();
    
            return $result;
    
         }
        return $row; 
    }
}

class DataBaseSQLITE3 extends DataBaseMYSQL{
    
}

class DbGemini extends DataBaseMSSQL {
      
    function __construct(){
        
        // lettura da file ini
        $filenamePref="\TrakHound\configurazione.ini";
        $chiave="pathdbGEMINI";
        $nomedb="";
        $nomedb_opt=read_ini_section_key($filenamePref,$chiave,"");
        if ($nomedb_opt>"") $nomedb=$nomedb_opt;

        

        // schema tabelle
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
        
        parent::__construct($nomedb,$schemadb);
      }              

}

class DbTH extends DataBaseSQLITE3 {
    public function __construct(){
        error_log("DbTH:costruttore");

        // lettura da file ini
        $filenamePref="\TrakHound\configurazione.ini";
        $chiave="pathdb";
        $nomedb="";
        $nomedb_opt=read_ini_section_key($filenamePref,$chiave,"");
        if ($nomedb_opt>"") $nomedb=$nomedb_opt;

        error_log("DbTH:nomedb:".$nomedb);
        // schema tabelle
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
				
            "status_info" => array(
                "campi" => array(
                    "unique_id" => "varchar(90) NOT NULL",'date' => "varchar(90) NOT NULL","time" => "varchar(90) NOT NULL",
                    "connected" => "int(1)",
                    "device_status" => "varchar(20)",
                    "production_status" => "varchar(20)",
                    "device_status_timer" => "double",
                    "production_status_timer" => "double",
                    "device_status_timestamp" => "varchar(30)",
                    "production_status_timestamp" => "varchar(30)",
                "day_run" => "double", "day_operating" => "double",'day_cutting' => "double",'day_spindle' => "double",
                'total_run' => "double",'total_operating' => "double",'total_cutting' => "double",'total_spindle' => "double",
                
                ),
                "PK"=>"unique_id,date,time",
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
						
            "groups" => array(
                "campi" => array(
                        "group_id" => "varchar(90) NOT NULL", "descrizione" => "varchar(90)"
                ),
                "PK"=>"group_id",
                ),

            "groups_x_user" => array(
                "campi" => array(
                        "id" => "varchar(90) NOT NULL", "group_id" => "varchar(90) NOT NULL"
                ),
                "PK"=>"id, group_id",
                ),

            "devices_x_group" => array(
                "campi" => array(
                        "group_id" => "varchar(90) NOT NULL", "unique_id" => "varchar(90) NOT NULL"
                ),
                "PK"=>"group_id, unique_id",
                ),

            "devicesAux" => array(
                "campi" => array(
                        "unique_id" => "varchar(90) NOT NULL", 
                        "device_id" => "varchar(90)", "description" => "varchar(90)", "manufacturer" => "varchar(90)", 
                        "model" => "varchar(90)", "serial" => "varchar(90)", "controller" => "varchar(90)", "location" => "varchar(90)", 
                        "enabled" => "int(1)"
                ),
                "PK"=>"unique_id",
                ),

            "devices" => array(
                "campi" => array(
                        "unique_id" => "varchar(90) NOT NULL", 
                        "agent_url" => "varchar(90)", "agent_port" => "int(10)", "agent_device_name" => "varchar(90)", "polling_interval" => "int(10)", 
                        "device_id" => "varchar(90)", "description" => "varchar(90)", "manufacturer" => "varchar(90)", 
                        "model" => "varchar(90)", "serial" => "varchar(90)", "controller" => "varchar(90)", "location" => "varchar(90)", 
                        "enabled" => "int(1)"
                ),
                "PK"=>"unique_id",
                ),

            "devices_x_sender" => array(
                "campi" => array(
                "sender_id" => "varchar(90) NOT NULL","unique_id" => "varchar(90) NOT NULL","enabled" => "int(1)","index" => "int(10)"
                ),
                "PK"=>"sender_id, unique_id",
                ),

            "devices_x_user" => array(
                "campi" => array(
                "id" => "varchar(90) NOT NULL","unique_id" => "varchar(90) NOT NULL","enabled" => "int(1)","index" => "int(10)"
                ),
                "PK"=>"id, unique_id",
                "VIEW" => "CREATE VIEW IF NOT EXISTS devices_x_user as select id, unique_id from devices_x_group as dg join groups_x_user as gu on dg.group_id = gu.group_id order by id",
                ),
                                    
                            );

        
        parent::__construct($nomedb,$schemadb);
      }              


}
?>