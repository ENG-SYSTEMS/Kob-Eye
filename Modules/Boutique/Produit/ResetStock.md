[STORPROC Boutique/Produit|Prod]
	[!Promo:=[!Prod::getPromo()!]!]
	[IF [!Promo!]][ELSE]
		[STORPROC Boutique/Reference|Ref]
			[IF [!Ref::StockPermanent!]]
				[!Ref::Quantite:=0!]
				[!Ref::StockOrigine:=0!]
				[!Ref::QuantiteVendue:=0!]
				[!Ref::Save()!]
				<h2>RESET STOCK ([!Ref::Reference!])</h2>
			[/IF]
		[/STORPROC]
	[/IF]
[/STORPROC]
