
    function costruisciMenu() {
	
		var menuItems = {
        "menu-0": {
            "id": "menu-0",
            "title": "Operazioni Principali",
            "parentId": null,
            "url": "#",
            "childCount": 3
        },
        "menu-01": {
            "id": "menu-01",
            "title": "Ordini di Produzione",
            "parentId": "menu-0",
            "url": "ordiniproduzione.html",
            "childCount": 0
        },
        "menu-02": {
            "id": "menu-02",
            "title": "Associazione Macchine a Ordine di Produzione",
            "parentId": "menu-0",
            "url": "macchinexOP.html",
            "childCount": 0
        },
        "menu-03": {
            "id": "menu-03",
            "title": "Avanzamento Lotti di produzione Macchine Plurimandrino",
            "parentId": "menu-0",
            "url": "pcsPLURI.html",
            "childCount": 0
        },
        "menu-1": {
            "id": "menu-1",
            "title": "Statistiche e Report",
            "parentId": null,
            "url": "#",
            "childCount": 4
        },
        "menu-11": {
            "id": "menu-11",
            "title": "Produzione totale per OP",
            "parentId": "menu-1",
            "url": "produzione.html",
            "childCount": 0
        },
        "menu-12": {
            "id": "menu-12",
            "title": "Produzione per Macchina con Oee",
            "parentId": "menu-1",
            "url": "impieghi.html",
            "childCount": 0
        },
        "menu-13": {
            "id": "menu-13",
            "title": "Produzione totale per OP con Oee",
            "parentId": "menu-1",
            "url": "impieghixcommessa.html",
            "childCount": 0
        },
        "menu-14": {
            "id": "menu-13",
            "title": "Produzione oraria e giornaliera",
            "parentId": "menu-1",
            "url": "impieghixpartnumber.html",
            "childCount": 0
        },
        "menu-2": {
            "id": "menu-2",
            "title": "Manutenzione",
            "parentId": null,
            "url": "#",
            "childCount": 7
        },
        "menu-21": {
            "id": "menu-21",
            "title": "Lotti di Produzione",
            "parentId": "menu-2",
            "url": "commesse.html",
            "childCount": 0
        },
        "menu-22": {
            "id": "menu-22",
            "title": "Macchine per Lotto di Produzione",
            "parentId": "menu-2",
            "url": "macchinexcommesse.html",
            "childCount": 0
        },
        "menu-23": {
            "id": "menu-23",
            "title": "Avanzamento Lotti di produzione",
            "parentId": "menu-2",
            "url": "pcs.html#",
            "childCount": 0
        },
        "menu-24": {
            "id": "menu-24",
            "title": "Macchine",
            "parentId": "menu-2",
            "url": "macchine.html",
            "childCount": 0
        },
        "menu-25": {
            "id": "menu-24",
            "title": "Tipi di Macchina",
            "parentId": "menu-2",
            "url": "tipimacchina.html",
            "childCount": 0
        },
        "menu-26": {
            "id": "menu-24",
            "title": "Part Numbers",
            "parentId": "menu-2",
            "url": "partnumbers.html",
            "childCount": 0
        },
        "menu-27": {
            "id": "menu-24",
            "title": "Part Programs",
            "parentId": "menu-2",
            "url": "partprograms.html",
            "childCount": 0
        },
        "menu-3": {
            "id": "menu-3",
            "title": "Opzioni",
            "parentId": null,
            "url": "opzioni.html",
            "childCount": 0
        } 
      };
			var htmlTxt = "";
        var cat = [];
        var htmlStrTxt = "";
        var url = "#";

        function recurseMenu(parent, level) {
            htmlStrTxt = '<ul>';
            for (var x in menuItems) {
                if (menuItems[x].parentId == parent) {
                    htmlStrTxt += '<li><img/><a href="' + menuItems[x].url + '">' + menuItems[x].title + '</a>';

                    if (menuItems[x].childCount > 0) {
                        htmlStrTxt += recurseMenu(menuItems[x].id, level + 1);
                    }
                    htmlStrTxt += '</li>';
                }
            }
            return htmlStrTxt + '</ul>';
        }

        var htmlTxt = recurseMenu(null, 0);
        $(".nestedsidemenu").html(htmlTxt);

	}

