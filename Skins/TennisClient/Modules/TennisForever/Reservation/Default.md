[INFO [!Query!]|I]
[IF [!I::TypeSearch!]=Direct]
    [MODULE TennisForever/Reservation/Fiche]
[ELSE]
    [MODULE TennisForever/Reservation/List]
[/IF]