<?php


function read_ini_section_key($filename,$chiave,$sezione="")
{
			//$filename="gestcomm.ini";
			$resultArray = parse_ini_file ($filename,true );
			$row="";
			foreach($resultArray as $key => $innerArray){
				if ($key==$sezione || $sezione=="") {
					foreach($innerArray as $innerRow => $value){
						if ($innerRow==$chiave)
							$row=$value;
					//echo $innerRow."=".$value . "<br/>";
					}
				}
			}
	return $row;		
}

function read_ini_section($filename,$sezione)
{
			//$filename="gestcomm.ini";
			$resultArray = parse_ini_file ($filename,true );
			$row=Array();

			foreach($resultArray as $key => $innerArray){
				if ($key==$sezione) {
					$row[0]=$innerArray;
				}
			}
	return $row;		
}

function write_ini_section($filename,$sezioni)
{			
			$res=false;
			//$filename="gestcomm.ini";
			$resultArray = parse_ini_file ($filename,true );
			
			foreach($sezioni as $sezione => $chiavi){
					$resultArray[$sezione]=$chiavi;
					$res=true;
				}
	return write_ini($resultArray,$filename);		
}
function write_ini($array, $file)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        }
        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    return safefilerewrite($file, implode("\r\n", $res));
}

function safefilerewrite($fileName, $dataToSave)
{ 
	$res=false;
	if ($fp = fopen($fileName, 'w'))
    {
        $startTime = microtime(TRUE);
        do
        {            $canWrite = flock($fp, LOCK_EX);
           // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
           if(!$canWrite) usleep(round(rand(0, 100)*1000));
        } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

        //file was locked so now we can store information
        if ($canWrite)
        {            fwrite($fp, $dataToSave);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
		$res=true;
    }
	return $res;
}

?>
