[STORPROC [!Query!]|R|0|1]

    [IF [!Valider!]=Valider la réservation]
        <div class="alert alert-success">
            La réservation a été validée avec succès.
        </div>
        //Validation de la réservation
        [!R::setValide()!]
    [/IF]

    [IF [!Valider!]=Payer en carte bleue]
        [!RES::Save()!]
        [COOKIE Set|RES|RES]
        <div class="alert alert-success">
        La réservation a étée validée avec succès.
        </div>
        [REDIRECT][!Sys::getMenu(TennisForever/Reservation)!]/[!RES::Id!]/Payer[/REDIRECT]
    [/IF]

    <div class="row">
    <div class="col-md-12">
        <form action="" method="POST">
            <h1>Détail de votre réservation</h1>
            [!Service:=[!R::getService()!]!]
            [!Court:=[!R::getCourt()!]!]
            <h3><b>Description: </b>[!Service::Titre!] pour [!Court::Titre!]</h3>
            <h3><b>Date: </b>le [DATE d/m/Y][!R::DateDebut!][/DATE]</h3>

            [!Client:=[!Module::TennisForever::getCurrentClient()!]!]
            [STORPROC TennisForever/TypeCourt/Court/[!Court::Id!]|TC|0|1][/STORPROC]

            [SWITCH [!TC::GestionInvite!]|=]
                [CASE Quantitatif]
                    [COUNT TennisForever/Reservation/[!R::Id!]/Partenaire|Pa]
                    <h3><b>Partenaire(s): [!Pa!]</b>
                [/CASE]
                [CASE Nominatif]
                    [STORPROC TennisForever/Reservation/[!R::Id!]/Partenaire|Pa]
                    <h3><b>Partenaire(s):</b>
                        <ul>
                            [LIMIT 0|100]
                            <li>[!Pa::Nom!] <span class="label label-primary" >[!Pa::Email!]</span></li>
                            [/LIMIT]
                        </ul></h3>
                    [/STORPROC]
                [/CASE]
            [/SWITCH]
            <h3><b>Total :</b></h3>
            [STORPROC TennisForever/Reservation/[!R::Id!]/LigneFacture|Lf]
            <div class="alert alert-info">[!Lf::Libelle!] (x[!Lf::Quantite!]) <span class="label label-primary pull-right" >[!Utils::getPrice([!Lf::MontantTTC!])!] €</span></div>
            [/STORPROC]
            </ul></h3>
        <h3><b>Total à payer:</b><span class="label label-success" >[!Utils::getPrice([!R::getTotal()!])!] €</span></h3>

            [IF [!R::Valide!]=]
                [IF [!R::getTotal()!]>0]
                    [IF [!R::DateFin!]>[!TMS::Now!]]
                        <input type="submit" class="btn btn-success btn-large btn-block" name="Valider" value="Payer en carte bleue" />
                    [/IF]
                    <a href="/[!Sys::getMenu(TennisForever/Reservation)!]/[!R::Id!]/Supprimer" class="btn btn-warning btn-large btn-block" >Annuler la réservation</a>
                [ELSE]
                    [IF [!R::DateFin!]>[!TMS::Now!]]
                        <input type="submit" class="btn btn-success btn-large btn-block" name="Valider" value="Valider la réservation">
                    [/IF]
                        <a href="/[!Sys::getMenu(TennisForever/Reservation)!]/[!R::Id!]/Supprimer" class="btn btn-warning btn-large btn-block" >Annuler la réservation</a>
                 [/IF]
            [ELSE]
                <div class="alert alert-success">
                    Cette réservation est validée.
                </div>
                [IF [!R::getTotal()!]>0]
                    <div class="alert alert-success">
                        Cette réservation est payée.
                    </div>
                [ELSE]
                    [IF [!R::DateFin!]>[!TMS::Now!]]
                        <a href="/[!Sys::getMenu(TennisForever/Reservation)!]/[!R::Id!]/Supprimer" class="btn btn-warning btn-large btn-block" >Annuler la réservation</a>
                    [/IF]
                [/IF]
            [/IF]
                <a href="/" class="btn btn-danger btn-large btn-block">Retour à l'accueil</a>
        </form>
    </div>
    </div>
[/STORPROC]