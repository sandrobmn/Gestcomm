<?php

//include_once('dbGEMINI.php');
function ricalcolaDataFineProduzioneStimata($dbGMN,$dbhandle,$idarticolo,$qta_richiesta,$data_inizio_produzione_prevista)
{
	// trasforma data xxxx-xx-xxThh:mm in xxxx-xx-xx hh:hh:hh 
	$f_datetime_local=false;
	$partiData=explode(" ",$data_inizio_produzione_prevista);
	if (count($partiData)>1) {
		$f_datetime_local=false;
	}
	else {
		$partiData=explode("T",$data_inizio_produzione_prevista);
		if (count($partiData)>1) {
			$f_datetime_local=true;
			$data_inizio_produzione_prevista=str_replace("T"," ",$data_inizio_produzione_prevista);
		}
	}
	$esito=false;
	$npag=1;
	$pag=1;
	$pagesize=1;

	$campiOC="idarticolo";
	$tabellaJoin="fasi_x_articolo";
	$campiOC .=",tempo_attrezzaggio_h,tempo_ciclo_s";
	$filtro = " idarticolo = '$idarticolo' and tempo_ciclo_s > 0";
	$ordinamento="";
	$raggruppamento="idarticolo,tempo_attrezzaggio_h,tempo_ciclo_s";
	$rowOCs=$dbGMN->db_get($dbhandle,$tabellaJoin,$campiOC,$filtro,$pag,$pagesize,$ordinamento,$raggruppamento,$npag);

	/*
	$campiOC="max(idordine_cliente) as idordine_cliente,idcliente,idarticolo,sum(qta_richiesta) as qta_richiesta,max(data_consegna_prevista) as data_consegna_prevista";
	$whereOC=$where;
	$ordOC="";//"data_consegna_prevista desc ";
	$raggrOC="idcliente,idarticolo";
	$rowOCs=db_get($dbhandle,"ordini_clienti",$campiOC,$where,$pag,1,$ordOC,$raggrOC,$npag);
*/
    $data_fine_produzione_stimata="";
	if (count($rowOCs)>0) {
		$rowOC=$rowOCs[0];

		// calcolo tempo di produzione (1 turno) e data inizio
		$tempo_ciclo_s=$rowOC["tempo_ciclo_s"];
		$tempo_attr_h=$rowOC["tempo_attrezzaggio_h"];
		$turni_x_g=1;

        $data_fine_produzione_stimata = stimaDataFineProduzione($data_inizio_produzione_prevista,$qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g);
		
		// trasforma data xxxx-xx-xx hh:mm:ss in xxxx-xx-xxThh:hh:hh 
		
		if ($f_datetime_local){
			$data_fine_produzione_stimata=str_replace(" ","T",$data_fine_produzione_stimata);
		}
		
        //error_log("data:$year-$month-$day tempo:$tempo_prod_g data inizio:$data_inizio_produzione_stimata");
		
	}

	return $data_fine_produzione_stimata;
}




function ricalcolaDataInizioProduzioneStimata($dbGMN,$dbhandle,$idarticolo,$qta_richiesta,$data_consegna_prevista)
{
	// trasforma data xxxx-xx-xxThh:mm in xxxx-xx-xx hh:hh:hh 
	$f_datetime_local=false;
	$partiData=explode(" ",$data_consegna_prevista);
	if (count($partiData)>1) {
		$f_datetime_local=false;
	}
	else {
		$partiData=explode("T",$data_consegna_prevista);
		if (count($partiData)>1) {
			$f_datetime_local=true;
			$data_consegna_prevista=str_replace("T"," ",$data_consegna_prevista);
		}
	}
	$esito=false;
	$npag=1;
	$pag=1;
	$pagesize=1;

	$campiOC="idarticolo";
	$tabellaJoin="fasi_x_articolo";
	$campiOC .=",tempo_attrezzaggio_h,tempo_ciclo_s";
	$filtro = " idarticolo = '$idarticolo' and tempo_ciclo_s > 0";
	$ordinamento="";
	$raggruppamento="idarticolo,tempo_attrezzaggio_h,tempo_ciclo_s";
	$rowOCs=$dbGMN->db_get($dbhandle,$tabellaJoin,$campiOC,$filtro,$pag,$pagesize,$ordinamento,$raggruppamento,$npag);

	/*
	$campiOC="max(idordine_cliente) as idordine_cliente,idcliente,idarticolo,sum(qta_richiesta) as qta_richiesta,max(data_consegna_prevista) as data_consegna_prevista";
	$whereOC=$where;
	$ordOC="";//"data_consegna_prevista desc ";
	$raggrOC="idcliente,idarticolo";
	$rowOCs=db_get($dbhandle,"ordini_clienti",$campiOC,$where,$pag,1,$ordOC,$raggrOC,$npag);
*/
    $data_inizio_produzione_stimata="";
	if (count($rowOCs)>0) {
		$rowOC=$rowOCs[0];

		// calcolo tempo di produzione (1 turno) e data inizio
		$tempo_ciclo_s=$rowOC["tempo_ciclo_s"];
		$tempo_attr_h=$rowOC["tempo_attrezzaggio_h"];
		$turni_x_g=1;

        $data_inizio_produzione_stimata = stimaDataInizioProduzione($data_consegna_prevista,$qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g);
		
		// trasforma data xxxx-xx-xx hh:mm:ss in xxxx-xx-xxThh:hh:hh 
		
		if ($f_datetime_local){
			$data_inizio_produzione_stimata=str_replace(" ","T",$data_inizio_produzione_stimata);
		}
		
        //error_log("data:$year-$month-$day tempo:$tempo_prod_g data inizio:$data_inizio_produzione_stimata");
		
	}

	return $data_inizio_produzione_stimata;
}



function calcolaDataInizioProduzioneStimata($dbGMN,$dbhandle,$whereOP,&$rec)
{
	$esito=false;
	$npag=1;
	$pag=1;
	$pagesize=1;

	$campiOC="max(idordine_cliente) as idordine_cliente,idcliente,idarticolo,sum(qta_richiesta) as qta_richiesta,max(data_consegna_prevista) as data_consegna_prevista";
	$campiOC .=",tempo_attrezzaggio_h,tempo_ciclo_s";
	
		
		$subQuerySelect="
		select (idordine_cliente) as idordine_cliente,idcliente,oc.idarticolo,(qta_richiesta) as qta_richiesta,
		(data_consegna_prevista) as data_consegna_prevista,fxa.tempo_attrezzaggio_h,fxa.tempo_ciclo_s ";
		$subQueryFrom="
		from ordini_clienti as oc join fasi_x_articolo as fxa on oc.idarticolo=fxa.idarticolo ";
		$subQueryWhere  = "where (".$whereOP. " and fxa.tempo_ciclo_s > 0".")";
		
		$subQueryGroup="
		group by idordine_cliente,data_consegna_prevista,idcliente,oc.idarticolo,fxa.tempo_attrezzaggio_h,fxa.tempo_ciclo_s,oc.qta_richiesta";
		
		$subQuery=$subQuerySelect." ".$subQueryFrom." ". $subQueryWhere." ".$subQueryGroup;
		
		

	$tabellaJoin="(".$subQuery.") as xxx";
	
	$ordinamento="";
	$filtro="";
	$raggruppamento="idcliente,idarticolo,tempo_attrezzaggio_h,tempo_ciclo_s";
	
	$rowOCs=$dbGMN->db_get($dbhandle,$tabellaJoin,$campiOC,$filtro,$pag,$pagesize,$ordinamento,$raggruppamento,$npag);

	/*
	$campiOC="max(idordine_cliente) as idordine_cliente,idcliente,idarticolo,sum(qta_richiesta) as qta_richiesta,max(data_consegna_prevista) as data_consegna_prevista";
	$whereOC=$where;
	$ordOC="";//"data_consegna_prevista desc ";
	$raggrOC="idcliente,idarticolo";
	$rowOCs=db_get($dbhandle,"ordini_clienti",$campiOC,$where,$pag,1,$ordOC,$raggrOC,$npag);
*/
	$data_inizio_produzione_stimata="";
	$data_consegna_prevista="";$idordine_cliente="";
	if (count($rowOCs)>0) {
		$rowOC=$rowOCs[0];
		$data_consegna_prevista=$rowOC["data_consegna_prevista"];
		$idordine_cliente=$rowOC["idordine_cliente"];
		$qta_richiesta=$rowOC["qta_richiesta"];

		// calcolo tempo di produzione (1 turno) e data inizio
		$tempo_ciclo_s=$rowOC["tempo_ciclo_s"];
		$tempo_attr_h=$rowOC["tempo_attrezzaggio_h"];
		$turni_x_g=1;

        $data_inizio_produzione_stimata = stimaDataInizioProduzione($data_consegna_prevista,$qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g);
        
        //error_log("data:$year-$month-$day tempo:$tempo_prod_g data inizio:$data_inizio_produzione_stimata");
		
		//$rec=$row[0];
		$rec["data"]=$data_inizio_produzione_stimata;
		$rec["qta_richiesta"]=$qta_richiesta;
		$rec["idordine_cliente"]=$idordine_cliente;
		$rec["idordine_cliente_h"]=$idordine_cliente;
		$rec["data_consegna_prevista"]=$data_consegna_prevista;
		//$row[0]=$rec;
		//error_log("2:".json_encode($row));
		$esito=true;
	}

	return $esito;
}

function stimaDataInizioProduzione($data_consegna_prevista,$qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g)
{

    $tempo_prod_g = stimaDurataProduzione($qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g);

    // il timestamp della data di inizio a partire dalla data di fine
    $d0=explode(" ",$data_consegna_prevista);
    $d=explode("-",$d0[0]);
    $year = $d[0];
    $month = $d[1];
    $day = $d[2];
    $di = mktime(0,0,0,$month,$day - $tempo_prod_g,$year);
    $data_inizio_produzione_stimata=date ("Y-m-d H:i:s", $di);

    return $data_inizio_produzione_stimata;
}

function stimaDataFineProduzione($data_inizio_produzione_prevista,$qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g)
{

    $tempo_prod_g = stimaDurataProduzione($qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g);

    // il timestamp della data di inizio a partire dalla data di fine
    $d0=explode(" ",$data_inizio_produzione_prevista);
    $d=explode("-",$d0[0]);
    $year = $d[0];
    $month = $d[1];
    $day = $d[2];
    $di = mktime(23,59,0,$month,$day + $tempo_prod_g,$year);
    $data_fine_produzione_stimata=date ("Y-m-d H:i:s", $di);

    return $data_fine_produzione_stimata;
}

function stimaDurataProduzione($qta_richiesta,$tempo_attr_h,$tempo_ciclo_s,$turni_x_g)
{

    $tempo_prod_h=($tempo_ciclo_s*$qta_richiesta)/(3600);
    // aggiungo il tempo di attrezzaggio
    $tempo_prod_h += $tempo_attr_h;
    $tempo_prod_g=($tempo_prod_h)/(8*$turni_x_g);
    // aggiungo un anticipo di 5 gg lav x le fasi successive alla produzione (zincatura)
    $tempo_prod_g += 5;
    // aggiungo un anticipo di 10 gg lav x le fasi interne alla produzione (controllo 100%)
    $tempo_prod_g += 10;
    // rapporto il tempo necessario in giorni lavorativi alle settimane
    $tempo_prod_g = $tempo_prod_g/5*7;

    return $tempo_prod_g;
}


?>

