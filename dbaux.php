<?php
function preparaRisposta($res, $msg,$op,$pagina=0,$pagine=0)
{
	$risposta = array();
	if ($res)
		$risposta["esito"]=true;
	else
		$risposta["esito"]=false;
	$risposta["richiesta"]=$op;
	$risposta["dati"]=$msg;
	if ($pagina>0) $risposta["pagina"]=$pagina;
	if ($pagine>0) $risposta["pagine"]=$pagine;
	
	return $risposta;
}
?>