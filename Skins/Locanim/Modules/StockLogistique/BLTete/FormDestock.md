{"form":{"type":"VBox","id":"CreationDestockage","label":"Déstockages","percentHeight":100,
"setStyle":{"verticalGap":5,"paddingTop":5,"paddingLeft":5,"paddingRight":5},"clipContent":0,
"localProxy":1,
"components":[
	{"type":"MenuTab","id":"menuList","maxLines":1,
	"menuItems":[
		{"children":[
			{"label":"Bon de Déstockage","icon":"save","data":"save","needFocus":1},
			{"label":"Rafraîchir","icon":"refresh","data":"refresh"}
		]}
	]},
	{"type":"HBox","id":"listBox","percentWidth":100,"percentHeight":100,"setStyle":{"paddingTop":0},
	"components":[
		{"type":"EditContainer", "id":"searchBox", "width":180, "percentHeight":100,
		"components":[
			{"type":"CollapsiblePanel","dividerVisible":0,"titleHeight":0,"setStyle":{"backgroundColor":"#d9d9d9","color":"black"},"open":1,"title":"Déstockage",
			"components":[
				{"type":"VBox","percentHeight":100,"percentWidth":100,"setStyle":{"paddingTop":2,"paddingLeft":4,"paddingRight":4,"paddingBottom":4,"verticalGap":2},
				"components":[
					{"type":"LabelItem","label":"Livraison","labelPercent":100,"percentWidth":100,"layout":{"type":"VerticalLayout","gap":-4,"paddingTop":0},"components":[
						{"type":"DateInterval","dataField":"DateLivraison","dataGroup":"searchGroup"}
					]},
					{"type":"LabelItem","label":"Magasin","labelPercent":100,"percentWidth":100,"layout":{"type":"VerticalLayout","gap":-4,"paddingTop":0},"components":[
						{"type":"TextInput","dataField":"Livraison","percentWidth":100,"dataGroup":"searchGroup"}
					]},
					{"type":"LabelItem","label":"Code postal","labelPercent":100,"percentWidth":100,"layout":{"type":"VerticalLayout","gap":-4,"paddingTop":0},"components":[
						{"type":"TextInput","dataField":"CodPostal","percentWidth":100,"dataGroup":"searchGroup"}
					]},
					{"type":"LabelItem","label":"Ville","labelPercent":100,"percentWidth":100,"layout":{"type":"VerticalLayout","gap":-4,"paddingTop":0},"components":[
						{"type":"TextInput","dataField":"Ville","percentWidth":100,"dataGroup":"searchGroup"}
					]},
					{"type":"LabelItem","label":"Client","labelPercent":100,"percentWidth":100,"layout":{"type":"VerticalLayout","gap":-4,"paddingTop":0},"components":[
						{"type":"TextInput","dataField":"Client","percentWidth":100,"dataGroup":"searchGroup"}
					]},
					{"type":"HBox","percentWidth":100,"setStyle":{"paddingTop":4},"components":[
						{"type":"Spacer","percentWidth":100},
						{"type":"Button","label":"$__Clear__$","id":"clear","width":70}
					]}
				]}
			]}
		],
		"events":[
			{"type":"proxy", "triggers":[
				{"trigger":"clear","action":"invoke","method":"clearData"}
			]}
		]},
		{"type":"AdvancedDataGrid","id":"dataGrid","dataField":"dataGrid","percentHeight":100,"percentWidth":100,"rowHeight":20,"variableRowHeight":1, 
		"kobeyeClass":{"module":"StockLogistique","objectClass":"CommandeLigne","setFilter":"Confirme=1&Preparation=0"},
		"checkBoxes":1, //"showCheckBoxes":1,"updatedItems":1,
		"getDataFunction":{"method":"object","data":{"module":"StockLogistique","objectClass":"BLTete"},
		"function":"GetDestockage"},
		"sortableColumns":0,
		"events":[
			{"type":"start","action":"loadValues"},
			{"type":"proxy","triggers":[
				//{"trigger":"save","action":"invoke","method":"callMethod",
				//"params":{"method":"object","data":{"module":"StockLogistique","objectClass":"BLTete"},
				//"function":"SaveDestockage","args":"dv:dataGrid"}},
				{"trigger":"save","action":"invoke","method":"callMethod","params":{"interface":"1",
				"method":"object","data":{"module":"StockLogistique","objectClass":"BLTete","form":"Functions/SaveDestockage.json"},
				"function":"SaveDestockage","selectionRequired":1,"args":"sv:dataGrid,iv:fiche,iv:etiquette,iv:laser"}},
				{"trigger":"refresh","action":"invoke","method":"restart"},
				{"trigger":"clear","action":"invoke","method":"restart"},
				{"trigger":"searchGroup","action":"invoke","method":"filterData","params":{"group":"searchGroup"}}
				//{"trigger":"searchGroup","action":"invoke","method":"restart"}
			]}
		],
		"columns":[
			{"type":"column","dataField":"Destockage","headerText":"D","format":"checkbox","width":20},
			{"type":"column","dataField":"Devis","headerText":"Devis","width":62},
			{"type":"column","dataField":"DateLivraison","headerText":"Livraison","format":"date","width":62},
			{"type":"column","dataField":"DateReprise","headerText":"Reprise","format":"date","width":62},
			{"type":"column","dataField":"Livraison","headerText":"Magasin","width":160},
			{"type":"column","dataField":"CodPostal","headerText":"CP","width":50},
			{"type":"column","dataField":"Ville","headerText":"Ville","width":100},
			{"type":"column","dataField":"Client","headerText":"Client","width":160},
			{"type":"column","dataField":"Quantite","headerText":"Qté","width":30,"format":"0dec"},
			{"type":"column","dataField":"Famille","headerText":"Famille","width":100},
			{"type":"column","dataField":"Designation","headerText":"Désignation","width":180},
			//{"type":"column","dataField":"Id","visible":0},
			{"type":"column","width":0}
		]}
	]}
]
,
"actions":[
//	{"type":"close", "action":"confirmUpdate"}
]}
}


