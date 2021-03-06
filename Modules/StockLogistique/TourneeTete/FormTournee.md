{"form":{"type":"VBox","id":"StockLogistique/Tournee","label":"Feuille de Route","labelPrefix":"FDR:",
"percentWidth":100,"percentHeight":100,"setStyle":{"paddingTop":0,"paddingLeft":5,"paddingRight":5},
"kobeyeClass":{"module":"StockLogistique","objectClass":"Tournee"},"localProxy":1,"focusedID":"reference",
"components":[
	{"type":"MenuTab","id":"menuList", "menuItems":[
		{"label":"$__File__$", "children":[
			{"label":"$__Save__$", "icon":"save", "data":"save"},
			{"label":"$__Save & Close__$", "icon":"save", "data":"saveClose"},
			{"type":"vseparator"},
			{"label":"Valider", "icon":"", "data":"valider"}
		]}
	],
	"actions":[
		{"type":"itemClick","actions":{
			"valider":{
				"action":"invoke","method":"callMethod","params":{
				"method":"query","data":{"dirtyChild":1,"module":"Devis","objectClass":"DevisTete"},
				"function":"ValideStock","args":[]}
			}
		}}
	]},
	{"type":"Box","percentWidth":100,"percentHeight":100,"setStyle":{"paddingTop":0}, 
	"components":[
		{"type":"EditContainer","id":"edit","percentWidth":100,"percentHeight":100,"defaultButtonID":"search",
		"components":[
			{"type":"VGroup","percentWidth":100,"percentHeight":100,
			"components":[
				{"type":"TitledBorderBox","title":"Feuille de Route",
				"components":[
					{"type":"Group","percentWidth":100,"layout":{"type":"HorizontalLayout","verticalAlign":"baseline","gap":4,"paddingLeft":6,"paddingRight":0,"paddingTop":0,"paddingBottom":4},
					"components":[
						{"type":"Label","text":"Référence"},
						{"type":"TextInput","dataField":"Reference","width":50,"formLabel":1,"editable":0},
						{"type":"Spacer","width":4},
						{"type":"Label","text":"Date"},
						{"type":"DateField","dataField":"Date"},
						{"type":"Spacer","width":4},
						{"type":"Label","text":"Effectué"},
						{"type":"CheckBox","dataField":"Effectue"},
						{"type":"Spacer","width":4},
						{"type":"Label","text":"Chauffeur"},
						{"type":"ComboBox","dataField":"ChauffeurId","width":150,"requireSelection":1,
						"kobeyeClass":{"module":"Repertoire","objectClass":"Tiers","identifier":"Id","label":"Intitule","filter":"Livreur=1"},
						"actions":[
							{"type":"init","action":"loadData"}
						]},
						{"type":"Spacer","width":4},
						{"type":"Label","text":"Véhicule"},
						{"type":"ComboBox","dataField":"VehiculeId","width":150,"requireSelection":1,
						"kobeyeClass":{"module":"StockLogistique","objectClass":"Vehicule","identifier":"Id","label":"Designation"},
						"actions":[
							{"type":"init","action":"loadData"}
						]}
					]}
				]},
				{"type":"HGroup","percentHeight":100,
				"components":[
					{"type":"AdvancedDataGrid","dataField":"tournee","updatedItems":1,
					"width":665,"percentHeight":100,"rowHeight":20,"variableRowHeight":1, 
					"kobeyeClass":{"dirtyParent":1,"objectClass":"BLTete"},"hierarchical":1,
					"events":[
						{"type":"start", "action":"invoke","method":"callMethod",
						"params":{"method":"object",
						"function":"GetLivraison","args":[{"value":[4]},{"id":["parentForm"]}]}}
					],
					"columns":[
						{"type":"column","dataField":"DateLR","headerText":"Date","format":"date","width":85,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"Type","headerText":"T","width":20,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"Livre","headerText":"L","format":"checkbox","width":20,"setStyle":{"paddingLeft":0,"paddingRight":1}},
						{"type":"column","dataField":"Repris","headerText":"R","format":"checkbox","width":20,"setStyle":{"paddingLeft":0,"paddingRight":1}},
						{"type":"column","dataField":"LivraisonId","headerText":"Magasin","width":150,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"CodPostal","headerText":"CP","width":40,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"Ville","headerText":"Ville","width":100,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"ClientId","headerText":"Client","width":120,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"Famille","headerText":"Famille","width":80,"setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"Quantite","headerText":"Qté","width":30,"format":"int","setStyle":{"paddingLeft":1,"paddingRight":1}},
						{"type":"column","dataField":"Id","visible":0},
						{"type":"column","width":0}
					]},
					{"type":"VBox","percentHeight":100,"percentWidth":100,
					"components":[
						{"type":"Group","width":500,"layout":{"type":"HorizontalLayout","verticalAlign":"baseline","gap":4,"paddingLeft":0,"paddingRight":0,"paddingTop":0,"paddingBottom":1},
						"components":[
							{"type":"Label","text":"Référence"},
							{"type":"BarcodeInput","id":"reference","width":100,
							"params":{"input":{"field":"Reference","prefix":"+","idField":"ReferenceId","addItems":1,
							"method":{"method":"object","data":{"module":"StockLogistique","objectClass":"Tournee"},"function":"FindReference"}},
							"dataGridID":"elements"},
							"events":[
								{"type":"proxy","triggers":[
									{"trigger":"search","action":"invoke","method":"commit"},
									{"trigger":"clear","action":"invoke","method":"clearInput"}
								]}
							]},
							{"type":"Button","label":"Chercher","id":"search"},
							{"type":"Button","label":"Effacer","id":"clear"}
						]},
						{"type":"AdvancedDataGrid","dataField":"elements","id":"elements","updatedItems":0,
						"width":320,"percentHeight":100,"rowHeight":20,"variableRowHeight":1, 
						"kobeyeClass":{"dirtyParent":1,"objectClass":"Reprise"},
						"events":[
							{"type":"start","action":"loadValues","params":{"needsParentId":1}},
							{"type":"proxy","triggers":[
								{"trigger":"valide","action":"invoke","method":"restart"}
							]}
						],
						"columns":[
							{"type":"column","dataField":"Reference","headerText":"Reference","width":100,"setStyle":{"paddingLeft":1,"paddingRight":1}},
							{"type":"column","dataField":"Commentaire","headerText":"Commentaire","width":220,"setStyle":{"paddingLeft":1,"paddingRight":1}},
							{"type":"column","dataField":"ReferenceId","visible":0},
							{"type":"column","dataField":"Id","visible":0},
							{"type":"column","width":0}
						]}
					]}
				]}
			]}
		],
		"events":[
			{"type":"start","action":"loadValues","params":{"needsId":1}},
			{"type":"proxy","triggers":[
				{"trigger":"saveClose","action":"invoke","method":"saveData","params":{"closeForm":1}},
				{"trigger":"save","action":"invoke","method":"saveData"},
				{"trigger":"close","action":"invoke","objectID":"parentForm","method":"closeForm"},
				{"trigger":"cancel","action":"invoke","method":"cancelEdit"}
			]},
			{"type":"save","action":"invoke","method":"callMethod",
			"params":{"method":"object","function":"SaveRetour","args":[{"dataValue":["*"]}]}}
		]}
	]}
],
"actions":[
	{"type":"close","action":"confirmUpdate"}
]}
}
