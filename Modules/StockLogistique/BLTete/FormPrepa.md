{"form":{"type":"VBox","id":"StockLogistique/Preparation","label":"Préparation","labelPrefix":"Prép ",
"percentWidth":100,"percentHeight":100,"setStyle":{"paddingTop":0,"paddingLeft":5,"paddingRight":5},
"kobeyeClass":{"module":"StockLogistique","objectClass":"BLTete"},"localProxy":1,"focusedID":"reference",
"components":[
	{"type":"MenuTab","id":"menuList","maxLines":1,"menuItems":[
		{"children":[
			{"label":"$__Save__$", "icon":"save", "data":"save"},
			{"label":"$__Save & Close__$", "icon":"save", "data":"saveClose"},
			{"type":"vseparator"},
			{"label":"Etiquettes", "icon":"print", "data":"print"}
		]}
	],"actions":[
		{"type":"itemClick","actions":{
			"print":{
				"action":"invoke","method":"callMethod","params":{
				"interface":1,
				"method":"query","data":{"dirtyChild":1,"module":"StockLogistique","objectClass":"BLTete","form":"PrintPrepa.json"},
				"function":"PrintLabels","args":[{"selectedValues":["Elements"]},{"interfaceValues":["select"]}]}
			}
		}}
	]},
	{"type":"HBox","percentWidth":100,"percentHeight":100,"setStyle":{"paddingTop":0}, 
	"components":[
		{"type":"EditContainer","id":"edit","percentWidth":100,"percentHeight":100,"defaultButtonID":"search",
		"components":[
			{"type":"HGroup","percentWidth":100,"percentHeight":100,"gap":6,"setStyle":{"paddingLeft":0,"paddingRight":0,"paddingTop":0,"paddingBottom":0}, 
			"components":[
				{"type":"VBox","percentHeight":100,"width":350,"setStyle":{"paddingTop":0,"verticalGap":4},
				"components":[
					{"type":"TitledBorderBox","title":"Préparation",
					"components":[
						{"type":"Form","percentWidth":100,"setStyle":{"verticalGap":1,"paddingLeft":1,"paddingRight":1,"paddingTop":0,"paddingBottom":1},
						"components":[
							{"type":"FormItem","label":"Numéro","labelWidth":80,"components":[
								{"type":"TextInput","dataField":"Reference","width":59,"formLabel":1,"editable":0}
							]},
							{"type":"FormItem","label":"Préparé","labelWidth":80,"components":[
								{"type":"CheckBox","dataField":"Prepare","editable":0}
							]},
							{"type":"FormItem","label":"Date Départ","labelWidth":80,"components":[
								{"type":"DateField","dataField":"DateLivraison","editable":0}
							]},
							{"type":"FormItem","label":"Date Retour","labelWidth":80,"components":[
								{"type":"DateField","dataField":"DateReprise","editable":0}
							]}
						]}
					]},
					{"type":"TitledBorderBox","title":"Livraison",
					"components":[
						{"type":"Form","percentWidth":100,"setStyle":{"verticalGap":1,"paddingLeft":1,"paddingRight":1,"paddingTop":0,"paddingBottom":1},
						"components":[
							{"type":"FormItem","label":"Magasin","labelWidth":80,"components":[
								{"type":"TextInput","dataField":"Livraison","percentWidth":100,"editable":0}
							]},
							{"type":"FormItem","labelWidth":80,"label":"Adresse","percentWidth":100,"setStyle":{"verticalGap":1},"components":[
								{"type":"TextInput","dataField":"Adr1Livr","percentWidth":100,"editable":0},
								{"type":"TextInput","dataField":"Adr2Livr","percentWidth":100,"editable":0}
							]},
							{"type":"FormItem","label":"CP - Ville","labelWidth":80,"direction":"horizontal","setStyle":{"horizontalGap":2,"verticleGap":2},"components":[
								{"type":"TextInput","dataField":"CPLivr","width":59,"editable":0},
								{"type":"TextInput","dataField":"VilleLivr","percentWidth":100,"editable":0}
							]},
							{"type":"FormItem","label":"Téléphone","labelWidth":80,"components":[
								{"type":"TextInput","dataField":"TelLivr","percentWidth":100,"editable":0}
							]}
						]}
					]},
					{"type":"TitledBorderBox","title":"Client",
					"components":[
						{"type":"Form","percentWidth":100,"setStyle":{"verticalGap":1,"paddingLeft":1,"paddingRight":1,"paddingTop":0,"paddingBottom":1},
						"components":[
							{"type":"FormItem","label":"Client","labelWidth":80,"components":[
								{"type":"TextInput","dataField":"Client","percentWidth":100,"editable":0}
							]},
							{"type":"FormItem","labelWidth":80,"label":"Adresse","percentWidth":100,"setStyle":{"verticalGap":1},"components":[
								{"type":"TextInput","dataField":"Adr1Client","percentWidth":100,"editable":0},
								{"type":"TextInput","dataField":"Adr2Client","percentWidth":100,"editable":0}
							]},
							{"type":"FormItem","label":"CP - Ville","labelWidth":80,"direction":"horizontal","setStyle":{"horizontalGap":2,"verticleGap":2},"components":[
								{"type":"TextInput","dataField":"CPClient","width":59,"editable":0},
								{"type":"TextInput","dataField":"VilleClient","percentWidth":100,"editable":0}
							]},
							{"type":"FormItem","label":"Téléphone","labelWidth":80,"components":[
								{"type":"TextInput","dataField":"TelClient","percentWidth":100,"editable":0}
							]}
						]}
					]}
				]},
				{"type":"VBox","percentHeight":100,"percentWidth":100,
				"components":[
					{"type":"HBox","width":500,"setStyle":{"verticalAlign":"baseline","horizontalGap":4,"paddingLeft":0,"paddingRight":0,"paddingTop":0,"paddingBottom":1},
					"components":[
						{"type":"Label","text":"Référence"},
						{"type":"BarcodeInput","id":"reference","width":120,
						"params":{"input":{"field":"Reference","prefix":"+","idField":"ReferenceId",
						"method":{"method":"object","data":{"module":"StockLogistique","objectClass":"BLTete"},"function":"FindReference"}},
						"selection":{"field":"Famille","prefix":"-"},"dataGridID":"Elements"},
						"events":[
							{"type":"proxy","triggers":[
								{"trigger":"search","action":"invoke","method":"commit"},
								{"trigger":"clear","action":"invoke","method":"clearInput"}
							]}
						]},
						{"type":"Button","label":"Chercher","id":"search"},
//						{"type":"Spacer","percentWidth":100},
						{"type":"Button","label":"Effacer","id":"clear"}
					]},
					{"type":"AdvancedDataGrid","id":"Elements","dataField":"Elements","width":500,"percentHeight":100,"checkBoxes":1,
					"kobeyeClass":{"dirtyParent":1,"objectClass":"Element"},
					"columns":[
						{"type":"column","dataField":"Famille","headerText":"Famille","width":80},
						{"type":"column","dataField":"Designation","headerText":"Désignation","width":200},
						{"type":"column","dataField":"Quantite","headerText":"Qté","width":40,"format":"int"},
						{"type":"column","dataField":"Reference","headerText":"Reference","width":100},
						{"type":"column","dataField":"Id","visible":0},
						{"type":"column","dataField":"ReferenceId","visible":0},
						{"type":"column","width":0}
					],				
					"events":[
						{"type":"start", "action":"loadValues"}
					]}
				]}
			]}
		],
		"events":[
			{"type":"start","action":"invoke","method":"callMethod","params":{"method":"query","function":"GetPreparation"}},
			{"type":"proxy","triggers":[
				{"trigger":"saveClose","action":"invoke","method":"saveData","params":{"closeForm":1}},
				{"trigger":"save","action":"invoke","method":"saveData"},
				{"trigger":"close","action":"invoke","objectID":"parentForm","method":"closeForm"},
				{"trigger":"cancel","action":"invoke","method":"cancelEdit"}
			]},
			{"type":"save","action":"invoke","method":"callMethod",
			"params":{"method":"query","function":"SavePreparation","args":[{"dataValue":["Elements"]}]}}
		]}
	]}
],
"actions":[
	{"type":"close","action":"confirmUpdate"}
]}
}
