<?php




function db_open($location,$mode) 
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

function db_close($dbh)
{ 
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;

    //$dbhandle->close(); 
    $dbhandle = null; 
}

function db_exec($dbhandle,$query) 
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

function db_execReturned($dbhandle,$query) 
{ 
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

function db_execStatement($dbh,$query)
{ 
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;

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


function db_create_table($dbh,$myTable)
{	
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;


	$schema=$schemadb[$myTable];
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
	
	return db_exec($dbhandle,$query);
} 

function db_insert_valori($dbh,$tabella,$valoreCampo,$campo)
{
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;


	$schema=$schemadb[$tabella];
	$campiTab=$schema["campi"];
	$elencoCampiPK=$schema["PK"];

	
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

function db_insert_replace_valori($dbh,$tabella,$valoreCampo,$campo)
{
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;


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
	
	return db_exec($dbhandle,$query);

}


function db_delete_valori($dbh,$tabella,$valoreCampo,$campo)
{
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;


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


function db_update_valori($dbh,$tabella,$valoreCampo,$campo)
{
	  $dbhandle=$dbh['dbhandle'] ;
	  $schemadb=$dbh['dbschema'] ;


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
	
	return db_execReturned($dbhandle,$query);

}

function db_select($dbhandle,$tabella,$campi,$pag,$nrighexpag,&$npag=0)
{
	$npag=0;
	return db_get($dbhandle,$tabella,$campi,"",$pag,$nrighexpag,"","",$npag);
}

function db_get($dbh,$tabella,$campi,$where,&$pag,$nrighexpag,$ordinamento="",$raggruppamento="",&$npag=-1)
{
	$dbhandle=$dbh['dbhandle'] ;
	$schemadb=$dbh['dbschema'] ;
	
	error_log("campi:".$campi." where:".$where." ord:".$ordinamento." grp:".$raggruppamento);

	$query="select ".$campi." from ".$tabella;
	if (strlen($where))
		$query=$query." where (".$where." )";
	$queryBase=$query;
	if (strlen($ordinamento)){
		if (strlen($raggruppamento)==0) {
			$raggruppamento = $ordinamento;
			$raggruppamento = str_replace(" asc ", "", $raggruppamento);
			$raggruppamento = str_replace(" desc ", "", $raggruppamento);
		}
		$query=$query." group by ".$raggruppamento." "." order by ".$ordinamento." ";
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
	if ($nrighexpag>1){
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
        error_log("db_get 1 ");
		$res = $stmt->execute();
        // set the resulting array to associative
        $res = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
        error_log("db_get 2 ");
        $result = $stmt->fetchAll();

        return $result;

        if (( $res)) // $res : (object)Sqlite3Result oppure (boolean)false
		{
            error_log("db_get 3 ");
            // sqlite3_stmt *statement ;
			$totalColumn = $res->columnCount();
			for ($iterator = 0; $iterator<$totalColumn; $iterator++) {
				$field[$iterator]=$res->columnName($iterator);
			}
            error_log("db_get 4 ");
            $i = 0; 

		 while($arr = $res->fetchAll()){ 
			for ($j=0;$j<count($arr);$j++)
			{
			  $row[$i][$field[$j]] = $arr[$field[$j]]; 
			}	
			$i++; 
		 }
		 }
	 }
	return $row; 
}

	  
?>