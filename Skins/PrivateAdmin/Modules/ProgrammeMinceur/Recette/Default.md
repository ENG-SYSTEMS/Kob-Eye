[INFO [!Query!]|I]
[IF [!I::TypeSearch!]=Child]
	[MODULE ProgrammeMinceur/Recette/List]
[ELSE]
	[MODULE ProgrammeMinceur/Recette/Fiche]
[/IF]