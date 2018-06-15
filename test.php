<?php

echo time(),"<br/>";
$d=new DateTime();
$tz= new DateTimeZone("Europe/Rome");
$d2=date_timezone_set($d,$tz);
echo "d2:",$d2->format("Y-m-d H:i:s")," tzc:". $d2->format("O"), "<br/>";
$tz= new DateTimeZone("Europe/London");
$d3=date_timezone_set($d,$tz);
echo "d3:",$d3->format("Y-m-d H:i:s")," tzc:". $d3->format("O"), "<br/>";
// dati fine = data inizio + 30 gg
$df="2017-04-12 00:00:00Z";
$tdf=strtotime($df);
//$tdf2=strtotime($df . " +30 days");
$tdf2=$tdf+(3600*24);

$df2=date("Y-m-d",$tdf2);
$df2=date("Y-m-d", (strtotime($df)+3600*24*30));
echo($tdf." ".$df." ".$tdf2." ".$df2."<br/>");
echo ("tzcorrection O:".date("O")."<br/>");
echo ("tzcorrection T:".date("T")."<br/>");
echo ("tzcorrection P:".date("P")."<br/>");

/*----------------------------*/

echo ("gestione errori"."<br/>");
// error level

//set error handler
set_error_handler("customError");

trigger_error("errore 0",E_USER_ERROR );
$a=1/0;

echo ("dopo l'errore");

function customError($errno, $errstr,$errfile,$errline,$errcontext) {
    $errorMsg = array();
    $errorMsg[E_WARNING]="E_WARNING";
    $errorMsg[E_NOTICE]="E_NOTICE";
    $errorMsg[E_USER_ERROR]="E_USER_ERROR";
    $errorMsg[E_USER_WARNING]="E_USER_WARNING";
    $errorMsg[E_USER_NOTICE]="E_USER_NOTICE";
    $errorMsg[E_RECOVERABLE_ERROR]="E_RECOVERABLE_ERROR";
    switch($errno){
        case E_WARNING:
        case E_NOTICE:
        case E_USER_ERROR:
        case E_USER_WARNING:
        case E_USER_NOTICE:
        case E_RECOVERABLE_ERROR:
            $msgE=$errorMsg[$errno];
        break;
        default:
        $msgE="ERROR";
        break;
        
    }
    $msg="";
    if ($errno) $msg .= "<b>Error:</b>". $msgE ;
    if ($errstr) $msg .= " $errstr ";
    if ($errfile) $msg .= " in file $errfile ";
    if ($errline) $msg .= " at line $errline ";
    //if ($errcontext) $msg .= " context:". serialize($errcontext) ;
    echo "<br>$msg<br>";
    //echo "Ending Script";
    //die();
  }
?>