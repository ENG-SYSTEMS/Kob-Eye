[HEADER]
	   <link rel="stylesheet" type="text/css" media="screen,print" href="Skins/Expressiv/Css/slickmap.css" />
[/HEADER]
ici aller aussi chercher les menus boutiques !!!!!!!!!!!!
[STORPROC [!Query!]|Cat][/STORPROC]
[TITLE][!Cat::Title!][IF [!Cat::Chapo!]!=] - [!Cat::Chapo!][/IF][/TITLE]
[IF [!Cat::MetaDesc!]]
		[DESCRIPTION][!Cat::MetaDesc!][/DESCRIPTION]
	[ELSE]
		[DESCRIPTION][SUBSTR 100][!Cat::Description!][/SUBSTR][/DESCRIPTION]
	[/IF]
[INFO [!Query!]|Inf]
[STORPROC [!Inf::Historique!]|H|0|1]
	[!Niv0:=[!H::Value!]!]
[/STORPROC]
<div id="Milieu">
	<div id="Data">
		<div class="TitreCat">
			<div class="TextDefault" style="height:auto;">
				<h1>[!Cat::Nom!]</h1>
				<h2 style="left:0;">[!Cat::Chapo!]</h2>
			</div>
		</div>
		<div>
			//[IF [!Cat::Description!]]
			//	<p class="Description">[!Cat::Description!]</p>
			//[/IF]
		</div>
		<div class="sitemap">	
			<ul id="primaryNav" class="col4">
				<li id="home"><a href="http://www.expressiv.net" title="Agence web Montpellier">Agence web _expressiv&trade;</a></li>
				[STORPROC [!Systeme::Menus!]|Test|0|100|Ordre|ASC]
					[IF [!Test::Affiche!]&&[!Test::Titre!]!=Accueil]
					[SWITCH [!Test::Alias!]|~]
						[CASE Redaction]
							<li>
							<a href="/[!Test::Url!]" title="[!Test::Titre!]">[!Test::Titre!]</a>
								<ul style="background:none;">
									[STORPROC [!Test::Alias!]/Categorie/Publier=1|Cato|0|15|Id|ASC]
										<li>
										<a href="/[!Test::Url!]/[!Cato::Url!]" title="[!Test::Titre!] :  [!Cato::Nom!]">[!Cato::Nom!]</a>
											[COUNT Redaction/Categorie/[!Cato::Id!]/Categorie|Cc]
											[IF [!Cc!]]
											<ul [IF [!Pos!]=1] style="background:none;display:block;position:relative;overflow:hidden;"[/IF]>
												[STORPROC Redaction/Categorie/[!Cato::Id!]/Categorie|C]
												<li style="">
												<a href="/[!Test::Url!]/[!Cato::Url!]/[!C::Url!]" title="[!Test::Titre!] :  [!Cato::Nom!],  [!C::Nom!]">[!C::Nom!]</a>
												</li>
												[/STORPROC]
											</ul>
											[/IF]
										</li>
									[/STORPROC]
								</ul>
							</li>
							
						[/CASE]
						[CASE Portfolio]
							<li>
								<a href="/[!Test::Url!]" title="[!Test::Titre!]">[!Test::Titre!]</a>
								<ul>
									<li style="padding-top:0;">
									<ul [IF [!Pos!]=1] style="background:none;display:block;position:relative;overflow:hidden;"[/IF]>
										[STORPROC [!Test::Alias!]/Categorie/Publier=1|Cata|0|15|Id|DESC]
											<li>
												<a href="/[!Test::Url!]/[!Cata::Url!]" title="[!Test::Titre!]  : [!Cata::Nom!]">[!Cata::Nom!]</a>
											</li>
										[/STORPROC]
									</ul>
									</li>
								</ul>
							</li>
							
						[/CASE]
						[CASE News]
							[STORPROC [!Test::Alias!]/Categorie|Cata|0|15|Id|ASC]
								<li>
								<a href="/[!Test::Url!]" style="text-transforme:capitalize;" title="[!Test::Titre!]">[!Test::Titre!]</a>
								<!--<ul>
									<li style="text-transform:capitalize;"><a href="/[!Test::Url!]/[!Cata::Link!]" style="text-transform:capitalize;">[!Cata::Nom!]</a></li>
								</ul>-->
								</li>
							[/STORPROC]
						[/CASE]
						[DEFAULT]
							<li style="background:none;background:transparent url('/Skins/Expressiv/Img/Sitemap/L1-right.png') center top no-repeat">
							<a href="/[!Test::Url!]" title="[!Test::Titre!]" onfocus="this.blur()">[!Test::Titre!]</a></li>
						[/DEFAULT]
					[/SWITCH]
					[/IF]
					
				[/STORPROC]
				[STORPROC [!Systeme::Menus!]|Test|0|100|Ordre|ASC]
					[IF [!Test::Affiche!]!=1&&[!Test::Titre!]!=Accueil&&[!Test::Titre!]!=Plan du site&&[!Test::Titre!]!=Fiche client&&[!Test::Titre!]!=Services]
						<li style="background:none;background:transparent url(/Skins/Expressiv/Img/Sitemap/vertical-line.png) no-repeat center top;">
							<a href="/[!Test::Url!]" title="[!Test::Titre!]" onfocus="this.blur()">[!Test::Titre!]</a>
						</li>
					[/IF]
				[/STORPROC]
			</ul>
	
		</div>
	</div>
</div>
	