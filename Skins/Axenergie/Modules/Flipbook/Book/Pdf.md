[!Page:=!]
[!Edit:=!]
[STORPROC [!Query!]/Page|Pages|0|10000|Id|ASC]
//	[!Page+=[!Pages::Image!].limit.1654x2339.jpg;!]
	[!Page+=[!Pages::Image!];!]
	[!Edit+=[!Pages::tmsEdit!];!]
[/STORPROC]

[STORPROC [!Query!]|Book][/STORPROC]

[OBJ Flipbook|Book|Pdf]
	[METHOD Pdf|saveToPdf]
		[PARAM][!Page!][/PARAM]
		[PARAM][!Edit!][/PARAM]
		[PARAM][!Book::Titre!][/PARAM]
		[PARAM]false[/PARAM]
		[PARAM][/PARAM]
	[/METHOD]