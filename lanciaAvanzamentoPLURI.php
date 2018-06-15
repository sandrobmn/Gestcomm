<?php
require 'vendor/autoload.php';

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

$logger = new Logger;
$writer1 = new Stream('./logs/avanzamentoPLURI.log');
$writer2 = new Zend\Log\Writer\Stream('php://output');

$logger->addWriter($writer1);
$logger->addWriter($writer2);

$logger->log(Logger::INFO, 'start'.": ".json_encode($argv));

/*
lancia avanzamentoNC da linea di comando per superare il timeout di 30 sec del lancio da web

php lanciaAvanzamentoNC op=avanzamentoNC esercizio='2018' a_data='2018-04-04' da_data='2018-01-01' filtro='posizione=G1'

parametri:  op = avanzamentoNC (operazione)
            esercizio (es. 2018)
            a_data  (es. 2018-04-04)
            da_data (es. 2018-01-01)
            filtro  (es. posizione='G1')

*/
echo "lanciaAvanzamentoNC:".json_encode($argv);

foreach ($argv as $arg) {
    echo($arg);
    $e=explode("=",$arg);
    /*
   if(count($e)==2) {
        echo "lanciaAvanzamentoNC-explode1 $arg:$e[0]-$e[1]<";
       $_GET[$e[0]]=$e[1];
    }
   else    {
    echo "lanciaAvanzamentoNC-explode2 $arg:$e[0]<";
    $_GET[]=$e[0];
    }
    */
    switch (count($e)) {
        case 0:
        break;
        case 1:
        $_GET[]=$e[0];
        break;
        case 2:
        $_GET[$e[0]]=$e[1];
        break;
        default:
        $i=strpos($arg,"=");
        $_GET[$e[0]]=substr($arg,$i+1);
    }

}

include_once('avanzamentoPLURI.php');

?>