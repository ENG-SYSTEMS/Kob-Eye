[INFO [!Query!]|I]
[OBJ [!I::Module!]|[!I::TypeChild!]|O]
[!chldrn:=[!O::getChildTypes!]!]
[!firstField:=!]
{"form":{"type":"VBox","id":"MailPreview:[!I::TypeChild!]?","percentHeight":100,"percentWidth":100,
"setStyle":{"paddingTop":5,"paddingLeft":5,"paddingRight":5,"paddingBottom":5,"verticalGap":0,"backgroundColor":"#dcdcdc"},"clipContent":0,
"kobeyeClass":{"module":"[!I::Module!]","objectClass":"[!I::TypeChild!]"},
"localProxy":1,
"components":[
	{"type":"EditContainer","percentHeight":100,"percentWidth":100,"id":"edit",
	"components":[
		{"type":"DividedBox","direction":"vertical","percentHeight":100,"percentWidth":100,
		"components":[
			{"type":"VBox","percentWidth":100,"percentHeight":100,"verticalScrollPolicy":"auto","minWidth":0,
			"setStyle":{"verticalGap":2,"backgroundColor":"#dcdcdc","paddigTop":4,"paddingBottom":0,"paddingLeft":0,"paddingRight":0},
			"components":[
				{"type":"Spacer","height":4},
				{"type":"HBox","percentWidth":100,"setStyle":{"paddigTop":0,"paddingBottom":0,"paddingLeft":0,"paddingRight":10},"components":[
					{"type":"Label","dataField":"Date","width":90},
					{"type":"Label","dataField":"Subject","setStyle":{"fontSize":12,"fontWeight":"bold"}}
				]},
				{"type":"FormItem","label":"From","labelWidth":35,"components":[
					{"type":"MailAddress","dataField":"FromAddress","percentWidth":100,"height":20,"setStyle":{"backgroundColor":"#dcdcdc"},
					"kobeyeClass":{"module":"Murphy","objectClass":"Contact","select":"Id,Email,FullName"},
					"defaultValue":"userMail","editable":0}
				]},					
				{"type":"FormItem","label":"To","labelWidth":35,"components":[
					{"type":"MailAddress","dataField":"ToAddress","percentWidth":100,"minHeight":20,"setStyle":{"backgroundColor":"#dcdcdc"},
					"kobeyeClass":{"module":"Murphy","objectClass":"Contact","select":"Id,Email,FullName"},"editable":0}
				]},					
				{"type":"FormItem","label":"CC","labelWidth":40,"components":[
					{"type":"MailAddress","dataField":"CcAddress","percentWidth":100,"minHeight":22,"setStyle":{"backgroundColor":"#dcdcdc"},
					"kobeyeClass":{"module":"Murphy","objectClass":"Contact","select":"Id,Email,FullName"},"editable":0}
				]},					
				{"type":"TextArea","dataField":"Body","percentWidth":100,"percentHeight":100}
	//			,"setStyle":{"headerHeight":0,"dropShadowVisible":0,"borderVisible":0},"editable":0}
	//,{"type":"TextArea","id":"dumpKobeye","percentWidth":100,"height":"300","events":[{"type":"start","action":"invoke","method":"dumpKobeye"}]}
			]},
			{"type":"VBox","percentWidth":20,"percentHeight":30,"percentWidth":100,"setStyle":{"verticalGap":1,"paddingLeft":0,"paddingTop":0,"paddingBottom":0},"components":[
				{"type":"HBox","percentWidth":100,"setStyle":{"gap":1,"paddingLeft":4,"paddingTop":2,"paddingBottom":2,"backgroundColor":"#d9d9d9"},
					"components":[
						{"type":"ImageButton","id":"editAtt","width":16,"height":16,"cornerRadius":8,"image":"mwc_i","borderWidth":1},
						{"type":"ImageButton","id":"deleteAtt","width":16,"height":16,"cornerRadius":8,"image":"mwc_moins","borderWidth":1,"stateGroup":"disabled"}
					]
				},
				{"type":"AdvancedDataGrid","id":"attachment","dataField":"attachment","percentWidth":100,"percentHeight":100,"rowHeight":20,"variableRowHeight":1,
				"kobeyeClass":{"dirtyParent":1,"objectClass":"Attachment"},
				"events":[
					//{"type":"start","action":"loadValues","params":{"needsParentId":1}},
					{"type":"dblclick","action":"invoke","method":"loadURL","params":{"url":"Doc"}},
					{"type":"proxy", "triggers":[
						{"trigger":"editAtt","action":"invoke","method":"loadURL","params":{"url":"Doc"}},
						{"trigger":"deleteAtt","action":"invoke","method":"deleteFromSelection"}
					]}
				],
				"columns":[
					{"type":"column","dataField":"Id","headerText":"ID","visible":0},
					{"type":"column","dataField":"Name","headerText":"Attachment","width":200}
				]}
			]}
		]}
	],
	"events":[
		{"type":"start","action":"invoke","method":"callMethod",
		"params":{"method":"object","function":"GetMessage",
		"args":"id:formCreator,pv:replyMode,v:0"}}
	]}
]}
}


