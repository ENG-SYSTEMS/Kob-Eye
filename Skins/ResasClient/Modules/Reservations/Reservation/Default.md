[INFO [!Query!]|I]
[IF [!I::TypeSearch!]=Direct]
    [MODULE Reservations/Reservation/Fiche]
[ELSE]
    [MODULE Reservations/Reservation/List]
[/IF]