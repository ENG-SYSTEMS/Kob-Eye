<h1>Liste de mes réservations</h1>
[IF [!msg!]]
    <div class="alert alert-[!action!]">[!msg!]</div>
[/IF]
[!Client:=[!Module::Reservations::getCurrentClient()!]!]


        [STORPROC Reservations/Client/[!Client::Id!]/Reservation/Valide=0|RES]
<div class="alert alert-danger">
Mes réservations non complètes
</div>
        [LIMIT 0|10]
<a href="/[!Sys::getMenu(Reservations/Reservation)!]/[!RES::Id!]" class="btn-tennis">
<span class="label label-danger pull-right">[!Utils::getPrice([!RES::getTotal()!])!] €</span>
Complèter ma réservation<br/>
    [IF [!RES::Service!]]
    <small>Le [DATE d/m/Y][!RES::DateDebut!][/DATE] à partir de [DATE H:i][!RES::DateDebut!][/DATE] pour une durée de [!RES::Duree!] minutes et avec [!RES::NbParticipant!] participants.</small>
    [/IF]
    <ul>
        [STORPROC [!RES::getLigneFacture()!]|Lf]
        <li>[!Lf::Quantite!] x [!Lf::Libelle!]</li>
        [/STORPROC]
    </ul>
</a>
        [/LIMIT]
        [/STORPROC]

        [STORPROC Reservations/Client/[!Client::Id!]/Reservation/Valide=1&DateFin>[!TMS::Now!]|RES]
<div class="alert alert-success">
Mes réservations à venir
</div>
        [LIMIT 0|10]
<a href="/[!Sys::getMenu(Reservations/Reservation)!]/[!RES::Id!]" class="btn-tennis">
<span class="label label-success pull-right">[!Utils::getPrice([!RES::getTotal()!])!] €</span>
Consulter ma réservation<br/>
    [IF [!RES::Service!]]
    <small>Le [DATE d/m/Y][!RES::DateDebut!][/DATE] à partir de [DATE H:i][!RES::DateDebut!][/DATE] pour une durée de [!RES::Duree!] minutes et avec [!RES::NbParticipant!] participants.</small>
    [/IF]
    <ul>
        [STORPROC [!RES::getLigneFacture()!]|Lf]
        <li>[!Lf::Quantite!] x [!Lf::Libelle!]</li>
        [/STORPROC]
    </ul>
    [COUNT Reservations/Reservation/[!RES::Id!]/StatusReservation|NbPart]
    [COUNT Reservations/Reservation/[!RES::Id!]/StatusReservation/Present=Oui|NbPartOk]
    [COUNT Reservations/Reservation/[!RES::Id!]/StatusReservation/Present=Non|NbPartKo]
    [IF [!NbPart!] > 0!]
        Partenaires ayant confirmé : [!NbPartOk!]/[!NbPart!] <br/>
        Partenaires ayant refusé : [!NbPartKo!]/[!NbPart!]
    [/IF]
</a>
        [/LIMIT]
        [/STORPROC]

[STORPROC Reservations/Client/[!Client::Id!]/Reservation/DateFin<[!TMS::Now!]|RES]
<div class="alert alert-info">
    Mes réservations passées
</div>
[LIMIT 0|10]
<a href="/[!Sys::getMenu(Reservations/Reservation)!]/[!RES::Id!]" class="btn-tennis">
    <span class="label label-primary pull-right">[!Utils::getPrice([!RES::getTotal()!])!] €</span>
    Consulter ma réservation<br/>
    [IF [!RES::Service!]]
    <small>Le [DATE d/m/Y][!RES::DateDebut!][/DATE] à partir de [DATE H:i][!RES::DateDebut!][/DATE] pour une durée de [!RES::Duree!] minutes et avec [!RES::NbParticipant!] participants.</small>
    [/IF]
    <ul>
        [STORPROC [!RES::getLigneFacture()!]|Lf]
        <li>[!Lf::Quantite!] x [!Lf::Libelle!]</li>
        [/STORPROC]
    </ul>
</a>
[/LIMIT]
[/STORPROC]