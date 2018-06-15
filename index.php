<html>
<head>
<title>Gestione Commesse</title>
<meta charset="utf-8">
<meta name="description" content="Gestione commesse"> <!--descrizione sintetica viualizzata nei motori di ricerca-->
<meta name="keywords" content="">
<meta name="robots" content="all"> <!--indica allo spider quali pagine indicizzare -->
<meta name="rating" content="general"> <!--decrive il tipo di pubblico (general, mature, restricted, 14years, safe for kids) -->
<meta http-equiv="expires" content="0"> <!--fa scaricare al browser sempre la copia aggiornata-->
<link rel="stylesheet" href="css/style.css" type="text/css">
<link rel="stylesheet" href="css/style-container.css" type="text/css">
</head>
<body>
<div class="header">
<div class="logo">
<img src="img/exacta-logo.png" />
</div>
<div class="title">
	Gestione Commesse
</div>
</div>
<div style="clear:both;"></div>
    <div id="sidebar">
	<div class="menu">
		<a href="index.html">Home</a>
		<a href="commesse.html">Commesse</a>
		<a href="macchine.html">Macchine</a>
		<a href="macchinexcommesse.html">Macchine per commessa</a>
		<a href="pcs.html">Lotti di produzione</a>
		<a href="produzione.html">Produzione</a>
		<a href="impieghi.html">Impieghi</a>
		<a href="opzioni.html">Opzioni</a>
	</div>
	</div>
 
<!--<script src="script/jquery-1.12.4.min.js" type="text/javascript"></script>-->
<script type="text/javascript" src="script/jquery-1.9.1.js"></script>
<script src="script/myScript.js" type="text/javascript"></script>
<script type='text/javascript'>

jQuery( function($) {
        $('tbody tr[data-href]').addClass('clickable').click( function() {
            window.location = $(this).attr('data-href');
        }).find('a').hover( function() {
            $(this).parents('tr').unbind('click');
        }, function() {
            $(this).parents('tr').click( function() {
                window.location = $(this).attr('data-href');
            });
        });
});
	
</script>
</body>
</html>
