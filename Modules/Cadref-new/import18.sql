set @annee:='2018';
set @cotis:=40;

truncate kbabtel.`kob-Cadref-Annee`;
insert into kbabtel.`kob-Cadref-Annee` (umod,gmod,omod,Annee,Cotisation,EnCours) values (7,7,7,@annee,@cotis,1);

truncate kbabtel.`kob-Cadref-Antenne`;
insert into kbabtel.`kob-Cadref-Antenne` (umod,gmod,omod,Antenne,Libelle,LibelleCourt,Abrege,Adresse1,Adresse2,CP,Ville,Telephone) values
(7,7,7,"A","Alès","Alès","ALE","","","","",""),
(7,7,7,"B","Bagnols sur Cèze","Bagnols","BAG","","","","",""),
(7,7,7,"G","Le Grau du roi","Le Grau","LGR","","","","",""),
(7,7,7,"L","Le Vigan","Le Vigan","VIG","","","","",""),
(7,7,7,"N","Nîmes","Nîmes","NIM","","","","",""),
(7,7,7,"S","Sommières","Sommières","SOM","","","","",""),
(7,7,7,"V","Villeneuve Lez Avignon","Villeneuve","VIL","","","","","");


truncate kbabtel.`kob-Cadref-Jour`;
insert into kbabtel.`kob-Cadref-Jour` (umod,gmod,omod,Id,Jour) values 
(7,7,7,1,'Lundi'),
(7,7,7,2,'Mardi'),
(7,7,7,3,'Mercredi'),
(7,7,7,4,'Jeudi'),
(7,7,7,5,'Vendredi'),
(7,7,7,6,'Samedi'),
(7,7,7,7,'Dimanche');

truncate kbabtel.`kob-Cadref-Vacance`;
insert into kbabtel.`kob-Cadref-Vacance` (umod,gmod,omod,Annee,Type,Libelle,DateDebut,DateFin,JourId,Logo) values 
(7,7,7,@annee,'D','Lundi',unix_timestamp('2018-09-10'),0,1,''),
(7,7,7,@annee,'D','Mardi',unix_timestamp('2018-09-10'),0,2,''),
(7,7,7,@annee,'D','Mercredi',unix_timestamp('2018-09-10'),0,3,''),
(7,7,7,@annee,'D','Jeudi',unix_timestamp('2018-09-10'),0,4,''),
(7,7,7,@annee,'D','Vendredi',unix_timestamp('2018-09-10'),0,5,''),
(7,7,7,@annee,'D','Samedi',unix_timestamp('2019-09-10'),0,6,''),
(7,7,7,@annee,'F','Lundi',unix_timestamp('2019-05-27'),0,1,''),
(7,7,7,@annee,'F','Mardi',unix_timestamp('2019-05-28'),0,2,''),
(7,7,7,@annee,'F','Mercredi',unix_timestamp('2019-06-05'),0,3,''),
(7,7,7,@annee,'F','Jeudi',unix_timestamp('2019-06-06'),0,4,''),
(7,7,7,@annee,'F','Vendredi',unix_timestamp('2019-06-07'),0,5,''),
(7,7,7,@annee,'F','Samedi',unix_timestamp('2019-06-08'),0,6,''),
(7,7,7,@annee,'V','VACANCES DE TOUSSAINT',unix_timestamp('2018-10-20'),unix_timestamp('2018-11-04'),0,'automne'),
(7,7,7,@annee,'V','11 Novembre',unix_timestamp('2018-11-11'),0,0,'11nov'),
(7,7,7,@annee,'V','VACANCES DE NOEL',unix_timestamp('2018-12-22'),unix_timestamp('2019-01-06'),0,'noel'),
(7,7,7,@annee,'V','VACANCES D''HIVER',unix_timestamp('2019-02-23'),unix_timestamp('2019-03-10'),0,'hiver'),
(7,7,7,@annee,'V','VACANCES DE PRINTEMPS',unix_timestamp('2019-04-20'),unix_timestamp('2019-05-05'),0,'printemps'),
(7,7,7,@annee,'V','8 Mai',unix_timestamp('2019-05-08'),0,0,'8mai'),
(7,7,7,@annee,'V','VACANCES DE L''ASCENSION',unix_timestamp('2019-05-29'),unix_timestamp('2019-06-03'),0,'ascension');

truncate kbabtel.`kob-Cadref-Lieu`;
insert into kbabtel.`kob-Cadref-Lieu` (umod,gmod,omod,Ville,Adresse1,Adresse2,Type,Lieu,GPS,AntenneId,OldNotes) values 
(7,7,7,"Sommières","Espace Lawrence Durrell","245, bd. Ernest François","L","SLD","43.781674,4.092405999999983",6,""),
(7,7,7,"Bagnols","Centre P Mendes France","av. de la Mayre","L","BMF","44.1571286,4.622954299999947",2,""),
(7,7,7,"Bagnols","Maison Laure Pailhon","8 rue Léon Alègre","L","BLP","44.1629355,4.621609700000022",2,""),
(7,7,7,"Bagnols","Maison des Associations","95 av. François Mitterand","L","BMA","44.1727855,4.619900199999961",2,""),
(7,7,7,"Uzès","Golf club d'Uzès","Pont des Charettes","L","BGU","",2,""),
(7,7,7,"St Gervais","Salle la Coquillone","ch. de la Coquillone","L","BSC","44.18632179999999,4.568395600000031",2,""),
(7,7,7,"Villeneuve","Salle Frédéric Mistral","19 bd. Frédéric Mistral","L","VFM","43.9713349,4.7955667999999605",7,""),
(7,7,7,"Les Angles","Salle Boris Vian","rue de l'école","L","VBV","42.5772819,2.07358499999998",7,""),
(7,7,7,"Le Vigan","Lycée","1 av. Pasteur","L","LVL","43.990522,3.6007893999999396",4,""),
(7,7,7,"Ganges","Salle de l'Horloge","Mairie Plan de l'Ormeau","L","LVH","43.93581,3.7088587000000643",4,""),
(7,7,7,"Alès","Ecole des Mines","6 av. de Clavières","L","AEM","44.1328582,4.088220099999944",1,""),
(7,7,7,"Alès","Salle du Capitole","10 Place de l'Hôtel de ville","L","ASC","44.1249942,4.076990600000045",1,""),
(7,7,7,"Alès","Pôle Scientifique et Culturel","155 rue du Faubourg de Rochebelle","L","APS","44.1302922,4.068972400000007",1,""),
(7,7,7,"Alès","Espace André Chamson","2 bd. Louis Blanc, Place Henry Barbusse","L","AAC","44.126705,4.079328000000032",1,""),
(7,7,7,"Alès","Golf club Alès Ribaute","Puech Serrier, Ghemin du Golf","L","AGR","",1,""),
(7,7,7,"Grau du Roi","Salle Christophe Colomb","14 bis rue de l'Egalité","L","GCC","",3,""),
(7,7,7,"Grau du Roi","Salle de réunion du Centre Technique","rue des Médards","L","GCT","",3,""),
(7,7,7,"Nîmes","CADREF","249 rue de Bouillargues","L","N00","43.833561,4.371489",5,""),
(7,7,7,"Nîmes","Maison Diocésaine","6 rue Salomon Reinach","L","NMD","43.8340875,4.375364399999967",5,""),
(7,7,7,"Nîmes","Archives Départementales","365 rue du Forez","L","NAD","43.8248603,4.367995199999996",5,""),
(7,7,7,"Nîmes","Piscine Le Fenouillet","7 rue Léo Lagrange","L","NPF","43.8451777,4.376619300000016",5,""),
(7,7,7,"Nîmes","Piscine Nemausa","120 av. de la Bouvine","L","NPN","43.817878,4.359027999999967",5,""),
(7,7,7,"Nîmes","Piscine Aquatropic","39 rue de l'Hostellerie","L","NPA","43.8128629,4.3454704000000675",5,""),
(7,7,7,"Nîmes","Piscine Bodypur","48 rue Louis Lumière","L","NPB","43.81492679999999,4.31342189999998",5,""),
(7,7,7,"Nîmes","Gymnase Gaston Lessut","102 rue de Mascard","L","NGL","43.8155732,4.330290999999988",5,""),
(7,7,7,"Nîmes","Cabinet Mazurier","6 rue Cart","L","NCM","43.8325683,4.3595017999999754",5,""),
(7,7,7,"Nîmes","Salle du Billard Club Nîmois","123 av. de la Bouvine","L","NBC","43.81652880000001,4.359267300000056",5,""),
(7,7,7,"Nîmes","Golf de Vacquerolles","1075 ch. du Golf","L","NGV","43.8502649,4.300112500000068",5,""),
(7,7,7,"Nîmes","Golf de Campagne","1360 ch. du Mas de Campagne","L","NGC","43.7624891,4.389048300000013",5,""),
(7,7,7,"Alès","Garage Durand","738 av. Frères Lumière","R","AGD","44.1386981,4.0972609000000375",0,"GARAGE DURAND"),
(7,7,7,"Alès","Gare routière","","R","AGR","44.1274883,4.0832717000000684",0,"GARE ROUTIERE"),
(7,7,7,"La Calmette","Rond point du Casino","av. Charles de Gaulle","R","LCC","43.92476629999999,4.257490599999983",0,"LA CALMETTE"),
(7,7,7,"Nîmes","Route de Sauve","148 route de Sauve","R","NRS","43.8407894,4.318510800000013",0,"ROUTE DE SAUVE"),
(7,7,7,"Nîmes","Arrêt de bus Place Séverine","","R","NBS","43.829858,4.352028",0,"SEVERINE"),
(7,7,7,"Nîmes","Stade des Costières","123 av. de la Bouvine","R","NSC","43.816025,4.359255599999983",0,"COSTIERES");
update kbabtel.`kob-Cadref-Lieu` set Libelle=concat(Ville,', ',Adresse1);

truncate kbabtel.`kob-Cadref-WebSection`;
insert into kbabtel.`kob-Cadref-WebSection` (umod,gmod,omod,WebSection,Libelle) values 
(7,7,7,'AA','Activités artistiques'),
(7,7,7,'AP','Activités Physiques'),
(7,7,7,'HI','Histoire'),
(7,7,7,'IN','Informatique'),
(7,7,7,'LG','Langues'),
(7,7,7,'SH','Sciences humaines et sociales');

truncate kbabtel.`kob-Cadref-WebDiscipline`;
insert into kbabtel.`kob-Cadref-WebDiscipline` (umod,gmod,omod,WebSection,WebDiscipline,Libelle,CodeDiscipline) values 
(7,7,7,"IN","DE","Découverte et prise en main de l'ordinateur","INDE"),
(7,7,7,"IN","IN","Internet","ININ"),
(7,7,7,"IN","SP","Sites pratiques","INSP"),
(7,7,7,"IN","SE","Sécuriser son ordinateur sur Internet","INSE"),
(7,7,7,"IN","GO","Google : nouvelles fonctions et recherches multimédia","INGO"),
(7,7,7,"IN","JI","Création de site internet ""en ligne"" avec Jimdo","INJI"),
(7,7,7,"IN","TT","Traitement de texte (Word) ","INTT"),
(7,7,7,"IN","TB","Tableur (Excel) ","INTB"),
(7,7,7,"IN","AC","Création d'une base de données avec Access","INAC"),
(7,7,7,"IN","WI","Windows 7 ou 10 ","INWI"),
(7,7,7,"IN","PW","Passage à Windows 10 : mise à niveau","INPW"),
(7,7,7,"IN","MU","Utilisation multimédia de Windows 10","INMU"),
(7,7,7,"IN","FS","Fonctions secrètes de Windows 10","INFS"),
(7,7,7,"IN","SK","Skype : messagerie instantanée","INSK"),
(7,7,7,"IN","FA","Facebook","INFA"),
(7,7,7,"IN","SI","Bien scanner et imprimer","INSI"),
(7,7,7,"IN","SR","Sauvegarde et récupération de ses données","INSR"),
(7,7,7,"IN","PC","Configuration et anomalies PC","INPC"),
(7,7,7,"IN","GA","Généalogie : ancestrologie et internet","INGA"),
(7,7,7,"IN","RP","Retouche photos et graphisme : PhotoFiltre","INRP"),
(7,7,7,"IN","PN","Photos numériques : Stockage et classement","INPN"),
(7,7,7,"IN","LP","Création d'un ""livre-photo"" sur internet","INLP"),
(7,7,7,"IN","GP","Google photo","INGP"),
(7,7,7,"IN","PP","PowerPoint : création de diaporamas","INPP"),
(7,7,7,"IN","PU","Mise en page avec Publisher","INPU"),
(7,7,7,"IN","MV","Montage vidéo : Windows (live) Movie Maker","INMV"),
(7,7,7,"IN","TA","Tablettes android","INTA"),
(7,7,7,"IN","TI","Tablettes iPad","INTI"),
(7,7,7,"HI","AR","Histoire de l'art","HIAR"),
(7,7,7,"HI","CI","Histoire des civilisations","HICI"),
(7,7,7,"HI","RL","Histoire des religions","HIRL"),
(7,7,7,"HI","RE","Histoire régionale","HIRE"),
(7,7,7,"HI","CO","Histoire contemporaine","HICO"),
(7,7,7,"HI","ME","Histoire médiévale et moderne","HIME"),
(7,7,7,"HI","RO","Les grandes étapes de la Rome antique","HIRO"),
(7,7,7,"HI","MY","Histoire des Mythologies","HIMY"),
(7,7,7,"HI","MC","Histoire des mythes et des civilisations","HIMC"),
(7,7,7,"HI","MO","Histoire moderne","HIMO"),
(7,7,7,"HI","CN","Histoire du Cinéma","HICN"),
(7,7,7,"SH","DP","Découverte de la philosophie","SHDP"),
(7,7,7,"SH","PH","Philosophie","SHPH"),
(7,7,7,"SH","AS","Astronomie astrophysique","SHAS"),
(7,7,7,"SH","PL","De la peinture à la littérature","SHPL"),
(7,7,7,"SH","PA","Philosophie de l'Art contemporain","SHPA"),
(7,7,7,"SH","GR","La grande Russie","SHGR"),
(7,7,7,"SH","PY","Psychologie","SHPY"),
(7,7,7,"SH","EC","Economie","SHEC"),
(7,7,7,"SH","GE","Géopolitique","SHGE"),
(7,7,7,"SH","CJ","Initiation à la culture japonaise","SHCJ"),
(7,7,7,"SH","PM","Partager la musique","SHPM"),
(7,7,7,"SH","BO","Botanique","SHBO"),
(7,7,7,"SH","AR","Archéologie régionale","SHAR"),
(7,7,7,"SH","LI","Littérature et Cinéma","SHLI"),
(7,7,7,"LG","AN","Anglais","LGAN"),
(7,7,7,"LG","ES","Espagnol","LGES"),
(7,7,7,"LG","IT","Italien","LGIT"),
(7,7,7,"LG","OC","Occitan","LGOC"),
(7,7,7,"AA","DP","Dessin-peinture toutes techniques","AADP"),
(7,7,7,"AA","AQ","Aquarelle","AAAQ"),
(7,7,7,"AA","PH","Peinture à l'huile","AAPH"),
(7,7,7,"AA","DE","Dessin d'après nature","AADE"),
(7,7,7,"AA","SC","Sculpture - modelage","AASC"),
(7,7,7,"AA","AP","Atelier pluridisciplinaire artistique","AAAP"),
(7,7,7,"AA","ET","Expression théâtrale","AAET"),
(7,7,7,"AA","CH","Atelier chant","AACH"),
(7,7,7,"AP","AQ","Aquagym","APAQ"),
(7,7,7,"AP","BK","Aquabike","APBK"),
(7,7,7,"AP","AM","Aquamix","APAM"),
(7,7,7,"AP","GY","Gym douce","APGY"),
(7,7,7,"AP","YO","Yoga","APYO"),
(7,7,7,"AP","YD","Yoga doux","APYD"),
(7,7,7,"AP","GD","Gym dance","APGD"),
(7,7,7,"AP","SO","Sophrologie","APSO"),
(7,7,7,"AP","GC","Gym tonic","APGC"),
(7,7,7,"AP","GS","Gym tonus","APGS"),
(7,7,7,"AP","PI","Pilates","APPI"),
(7,7,7,"AP","BZ","Body zen","APBZ"),
(7,7,7,"AP","YR","Yoga du rire","APYR"),
(7,7,7,"AP","BI","Billard français","APBI"),
(7,7,7,"AP","GO","Golf","APGO");
update kbabtel.`kob-Cadref-WebDiscipline` d
inner join kbabtel.`kob-Cadref-WebSection` s on s.WebSection=d.WebSection
set d.WebSectionId=s.Id;





truncate kbabtel.`kob-Cadref-Profession`;
insert into kbabtel.`kob-Cadref-Profession` (umod,gmod,omod,Profession,Libelle)
select 7,7,7,n.Categorie,n.Libelle
from cadref18.Categories n
where n.`Type`='0';

truncate kbabtel.`kob-Cadref-Cursus`;
insert into kbabtel.`kob-Cadref-Cursus` (umod,gmod,omod,Cursus,Libelle)
select 7,7,7,n.Categorie,n.Libelle
from cadref18.Categories n
where n.`Type`='1';

truncate kbabtel.`kob-Cadref-Situation`;
insert into kbabtel.`kob-Cadref-Situation` (umod,gmod,omod,Situation,Libelle)
select 7,7,7,n.Categorie,n.Libelle
from cadref18.Categories n
where n.`Type`='6';

truncate kbabtel.`kob-Cadref-Commune`;
insert into kbabtel.`kob-Cadref-Commune` (umod,gmod,omod,Commune,CP,Agglomeration)
select 7,7,7,Ville,CP,Agglo
from cadref18.Communes;



truncate kbabtel.`kob-Cadref-Enseignant`;
insert into kbabtel.`kob-Cadref-Enseignant` (umod,gmod,omod,Code,Nom,Prenom,Adresse1,Adresse2,CP,Ville,Telephone1,Telephone2,Notes,Mail)
select 7,7,7,Code,Nom,Prenom,Adr1,Adr2,CP,Ville,aatel(Tel1),aatel(Tel2),Notes,eMail from cadref18.Enseignants;

truncate kbabtel.`kob-Cadref-Section`;
insert into kbabtel.`kob-Cadref-Section` (umod,gmod,omod,Section,Libelle)
select 7,7,7,Sect,Libelle from cadref18.Sections;

truncate kbabtel.`kob-Cadref-Discipline`;
insert into kbabtel.`kob-Cadref-Discipline` (umod,gmod,omod,Section,Discipline,CodeDiscipline,Libelle,Visite,Certificat,SectionId)
select 7,7,7,d.Sect,d.Discipline,concat(d.Sect,d.Discipline),d.Libelle,ifnull(d.Visites='O',0),d.Certificat<>0,s.id
from cadref18.Disciplines d
left join kbabtel.`kob-Cadref-Section` s on s.Section=d.Sect;

update kbabtel.`kob-Cadref-Discipline` n
inner join kbabtel.aaniveau nn on nn.os=n.Section and nn.od=n.Discipline
inner join kbabtel.`kob-Cadref-WebDiscipline` d on d.CodeDiscipline=nn.nd
set n.WebDisciplineId=d.Id, n.WebDiscipline=d.CodeDiscipline;


truncate kbabtel.`kob-Cadref-Niveau`;
insert into kbabtel.`kob-Cadref-Niveau` (umod,gmod,omod,Antenne,Section,Discipline,Niveau,CodeNiveau,Libelle,AntenneId,SectionId,DisciplineId)
select 7,7,7,n.Antenne,n.Sect,n.Discipline,n.Niveau,concat(n.Antenne,n.Sect,n.Discipline,n.Niveau),n.Libelle,a.id,s.id,d.id
from cadref18.Niveaux n
left join kbabtel.`kob-Cadref-Antenne` a on a.Antenne=n.Antenne
left join kbabtel.`kob-Cadref-Section` s on s.Section=n.Sect
left join kbabtel.`kob-Cadref-Discipline` d on d.SectionId=s.Id and d.Discipline=n.Discipline;

truncate kbabtel.`kob-Cadref-Classe`;
insert into kbabtel.`kob-Cadref-Classe` (umod,gmod,omod,CodeClasse,Antenne,Section,Discipline,Niveau,Classe,Annee,
JourId,HeureDebut,HeureFin,Notes,Places,Inscrits,Attentes,Prix,Seances,Lieu,CycleDebut,CycleFin,AntenneId,SectionId,DisciplineId,NiveauId)
select 7,7,7,concat(c.Antenne,c.Sect,c.Discipline,c.Niveau,c.Classe),c.Antenne,c.Sect,c.Discipline,c.Niveau,c.Classe,@annee,
Jour,Debut,Fin,Notes,Places,Inscrits,Attentes,Prix,Seances,Lieu,Date1,Date2,a.id,s.id,d.id,n.id
from cadref18.Classes c
left join kbabtel.`kob-Cadref-Antenne` a on a.Antenne=c.Antenne
left join kbabtel.`kob-Cadref-Section` s on s.Section=c.Sect
left join kbabtel.`kob-Cadref-Discipline` d on d.SectionId=s.Id and d.Discipline=c.Discipline
left join kbabtel.`kob-Cadref-Niveau` n on n.Antenne=c.Antenne and n.Section=c.Sect and n.Discipline=c.Discipline and n.Niveau=c.Niveau;

update kbabtel.`kob-Cadref-Classe` c
left join kbabtel.aaclasse cl on cl.classe=c.CodeClasse
left join kbabtel.`kob-Cadref-Lieu` l on l.Lieu=cl.lieu
set c.LieuId=l.Id
where l.Id is not null;

truncate kbabtel.`kob-Cadref-ClasseDate`;
insert into kbabtel.`kob-Cadref-ClasseDate`(umod,gmod,omod,ClasseId,Annee,DateCours)
select 7,7,7,c.Id,'2018',unix_timestamp(d0) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d0>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d1) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d1>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d2) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d2>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d3) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d3>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d4) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d4>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d5) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d5>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d6) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d6>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d7) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d7>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d8) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d8>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d9) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d9>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d10) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d10>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d11) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d11>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d12) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d12>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d13) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d13>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d14) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d14>'0000-00-00'
union all select 7,7,7,c.Id,'2018',unix_timestamp(d15) from kbabtel.`kob-Cadref-Classe` c left join kbabtel.aadates d on d.cod=c.CodeClasse where d15>'0000-00-00';

update kbabtel.`kob-Cadref-Classe` c
inner join kbabtel.`kob-Cadref-ClasseDate` cd on cd.ClasseId=c.id
set c.Programmation=1;

delete s
from kbabtel.`kob-Cadref-Section`s
left join kbabtel.`kob-Cadref-Discipline` d on s.id=d.SectionId
left join kbabtel.`kob-Cadref-Niveau` n on d.id=n.DisciplineId
left join kbabtel.`kob-Cadref-Classe` c on n.Id=c.NiveauId
where d.Id is null;
delete d
from kbabtel.`kob-Cadref-Discipline` d
left join kbabtel.`kob-Cadref-Niveau` n on d.id=n.DisciplineId
left join kbabtel.`kob-Cadref-Classe` c on n.Id=c.NiveauId
where d.Id is null;
delete s
from kbabtel.`kob-Cadref-Section`s
left join kbabtel.`kob-Cadref-Discipline` d on s.id=d.SectionId
where d.Id is null;
delete d
from kbabtel.`kob-Cadref-Discipline` d
left join kbabtel.`kob-Cadref-Niveau` n on d.Id=n.DisciplineId
where n.Id is null;
delete n
from kbabtel.`kob-Cadref-Niveau` n
left join kbabtel.`kob-Cadref-Classe` c on n.Id=c.NiveauId
where c.Id is null;


truncate kbabtel.`kob-Cadref-ClasseEnseignants`;
insert into kbabtel.`kob-Cadref-ClasseEnseignants` (umod,gmod,omod,Classe,EnseignantId)
select 7,7,7,n.Id,e.Id
from cadref18.Classes c
left join kbabtel.`kob-Cadref-Classe` n on n.Antenne=c.Antenne and n.Section=c.Sect and n.Discipline=c.Discipline and n.Niveau=c.Niveau and n.Classe=c.Classe
left join kbabtel.`kob-Cadref-Enseignant` e on e.Code=c.Ens1
where c.Ens1<>'';
insert into kbabtel.`kob-Cadref-ClasseEnseignants` (umod,gmod,omod,Classe,EnseignantId)
select 7,7,7,n.Id,e.Id
from cadref18.Classes c
left join kbabtel.`kob-Cadref-Classe` n on n.Antenne=c.Antenne and n.Section=c.Sect and n.Discipline=c.Discipline and n.Niveau=c.Niveau and n.Classe=c.Classe
left join kbabtel.`kob-Cadref-Enseignant` e on e.Code=c.Ens2
where c.Ens2<>'';

#update kbabtel.`kob-Cadref-Classe` c
#set c.Reduction1=38,c.Reduction2=38,c.DateReduction1=unix_timestamp('20190101'),c.DateReduction2=unix_timestamp('20190301')
#where Prix=115 and CycleDebut=''
#update kbabtel.`kob-Cadref-Classe` c
#set c.Reduction1=34,c.Reduction2=34,c.DateReduction1=unix_timestamp('20190101'),c.DateReduction2=unix_timestamp('20190301')
#where Prix=102 and CycleDebut=''


/*
truncate kbabtel.`kob-Cadref-Adherent`;
insert into kbabtel.`kob-Cadref-Adherent` (umod,gmod,omod,Numero,Nom,Prenom,Adresse1,Adresse2,CP,Ville,Telephone1,Telephone2,Notes,Mail,
NotesAnnuelles,Naissance,Inscription,Sexe,ProfessionId,CursusId,SituationId,Adherent,Annee,Etoiles,Origine,DateCertificat,ClasseId,
Cotisation,Cours,Reglement,Differe,Regularisation)
select 7,7,7,Numero,Nom,Prenom,Adr1,Adr2,CP,Ville,Tel1,Tel2,e.Notes,eMail,
NotesTemp,Naissance,Inscription,Sexe,p.Id,u.Id,s.Id,Adherent,e.Annee,Etoiles,Origine,if(Certificat<@annee,null,unix_timestamp(Certificat)),c.Id,
Cotisation,Montant,Reglement,Differe,Regul
from cadref18.Eleves e
left join kbabtel.`kob-Cadref-Classe` c on c.CodeClasse=concat(substr(e.Delegue,1,1),substr(e.Delegue,2,2),substr(e.Delegue,4,2),substr(e.Delegue,6,1),substr(e.Delegue,7,1))
left join kbabtel.`kob-Cadref-Profession` p on p.Profession=e.Profession
left join kbabtel.`kob-Cadref-Cursus` u on u.Cursus=e.Cursus
left join kbabtel.`kob-Cadref-Situation` s on s.Situation=e.Situation;
*/

truncate kbabtel.`kob-Cadref-Adherent`;
insert into kbabtel.`kob-Cadref-Adherent` (umod,gmod,omod,Numero,Nom,Prenom,Adresse1,Adresse2,CP,Ville,Telephone1,Telephone2,Notes,Mail,
NotesAnnuelles,Naissance,Inscription,Sexe,ProfessionId,CursusId,SituationId,Adherent,Annee,Etoiles,Origine,DateCertificat,ClasseId,
Cotisation,Cours,Reglement,Differe,Regularisation,Utilisateur,DateModification)
select 7,7,7,e.Numero,e.Nom,e.Prenom,e.Adr1,e.Adr2,e.CP,e.Ville,aatel(e.Tel1),aatel(e.Tel2),e.Notes,eMail,
e.NotesTemp,e.Naissance,e.Inscription,e.Sexe,p.Id,u.Id,s.Id,Adherent,e.Annee,e.Etoiles,e.Origine,if(e.Certificat<@annee,null,unix_timestamp(e.Certificat)),c.Id,
e.Cotisation,e.Montant,e.Reglement,e.Differe,e.Regul,e.Utilisateur,unix_timestamp(DateModif)
from cadref18.Eleves e
left join kbabtel.`kob-Cadref-Classe` c on c.CodeClasse=concat(substr(e.Delegue,1,1),substr(e.Delegue,2,2),substr(e.Delegue,4,2),substr(e.Delegue,6,1),substr(e.Delegue,7,1))
left join kbabtel.`kob-Cadref-Profession` p on p.Profession=e.Profession
left join kbabtel.`kob-Cadref-Cursus` u on u.Cursus=e.Cursus
left join kbabtel.`kob-Cadref-Situation` s on s.Situation=e.Situation;

update kbabtel.`kob-Cadref-Adherent` set NomPrenom=concat(Nom,' ',Prenom);
update kbabtel.`kob-Cadref-Adherent` a
inner join kbabtel.aaname p on p.old=a.Prenom
set a.Prenom=p.name;
update kbabtel.`kob-Cadref-Enseignant` a
inner join kbabtel.aaname p on p.old=a.Prenom
set a.Prenom=p.name;


truncate kbabtel.`kob-Cadref-Inscription`;
insert into kbabtel.`kob-Cadref-Inscription` (umod,gmod,omod,Numero,CodeClasse,Antenne,Annee,DateInscription,Attente,DateAttente,Prix,Soutien,Reduction,
Supprime,DateSupprime,AdherentId,ClasseId,Utilisateur)
select 7,7,7,i.Numero,concat(i.Antenne,i.Sect,i.Discipline,i.Niveau,i.Classe),i.Antenne,@annee,if(Creation<@annee,null,unix_timestamp(Creation)),
Attente,if(DateAtte<@annee,null,unix_timestamp(DateAtte)),i.Prix,i.Reduction,i.Reduc2,
Supprime,if(DateSuppr<@annee,null,unix_timestamp(DateSuppr)),a.Id,c.Id,i.Utilisateur
from cadref18.Inscriptions i
left join kbabtel.`kob-Cadref-Adherent` a on a.Numero=i.Numero
left join kbabtel.`kob-Cadref-Classe` c on c.CodeClasse=concat(i.Antenne,i.Sect,i.Discipline,i.Niveau,i.Classe)
order by i.Numero,i.Code;



truncate kbabtel.`kob-Cadref-Visite`;
insert into kbabtel.`kob-Cadref-Visite` (umod,gmod,omod,Visite,Libelle,Annee,DateVisite,Places,Inscrits,Attentes,Prix,Utilisateur,Web)
select 7,7,7,Visite,Libelle,@annee,unix_timestamp(DateVis),Places,Inscrits,Attentes,Prix1,Utilisateur,1
from cadref18.Visites;

update  kbabtel.`kob-Cadref-Visite` v
inner join kbabtel.aavisite l on l.c=v.Visite
set v.Description = l.l;

truncate kbabtel.`kob-Cadref-Depart`;
insert into kbabtel.`kob-Cadref-Depart` (umod,gmod,omod,VisiteId,LieuId)
select 7,7,7,v.Id,l.Id
from kbabtel.`kob-Cadref-Visite` v
left join kbabtel.`kob-Cadref-Lieu` l on l.`Type`='R';

truncate kbabtel.`kob-Cadref-Reservation`;
insert into kbabtel.`kob-Cadref-Reservation` (umod,gmod,omod,Numero,Visite,Annee,Prix,Reduction,Attente,DateAttente,DateInscription,AdherentId,VisiteId,Utilisateur,Notes,ModeReglement)
select 7,7,7,r.Numero,r.Visite,@annee,r.Prix,r.Reduction,r.Attente,if(r.DateAtte<@annee,null,unix_timestamp(r.DateAtte)),unix_timestamp(r.Creation),a.Id,v.Id,r.Utilisateur,r.Notes,'B'
from cadref18.Reservations r
left join kbabtel.`kob-Cadref-Adherent` a on a.Numero=r.Numero
left join kbabtel.`kob-Cadref-Visite` v on v.Visite=r.Visite
order by r.Numero,r.Visite;

update `kob-Cadref-Reservation` set Notes='ROUTE DE SAUVE' where Notes='CASTANET';
update `kob-Cadref-Reservation` set Notes='GARAGE DURAND' where Notes like 'GAARA%' or Notes like 'GARAR%';
#select r.Notes,l.Id,l.OldNotes,d.Id from
update `kob-Cadref-Reservation` r
inner join `kob-Cadref-Lieu` l on l.OldNotes=r.Notes
inner join `kob-Cadref-Depart` d on d.LieuId=l.Id and d.VisiteId=r.VisiteId
set r.DepartId=d.Id
where r.Notes<>'';
update `kob-Cadref-Reservation` set Notes='';




truncate kbabtel.`kob-Cadref-Reglement`;
insert into kbabtel.`kob-Cadref-Reglement` (umod,gmod,omod,Numero,Annee,DateReglement,Montant,ModeReglement,Notes,Differe,Encaisse,Supprime,Utilisateur,AdherentId)
select 7,7,7,r.Numero,@annee,unix_timestamp(r.DateRegl),Somme,`Mode`,r.Notes,r.Differe,if(r.Differe,r.Encaisse,1),r.Supprime,r.Utilisateur,a.Id
from cadref18.Reglements r
left join kbabtel.`kob-Cadref-Adherent` a on a.Numero=r.Numero
order by r.Numero,r.DateRegl;

/*
insert into kbabtel.`kob-Cadref-Reglement` (umod,gmod,omod,Numero,AdherentId,ReservationId,Visite,Montant,Utilisateur,DateReglement,Differe,Encaisse,ModeReglement,Annee)
select 7,7,7,r.Numero,r.AdherentId,r.Id,r.Visite,r.Prix,r.Utilisateur,v.DateVisite,1,from_unixtime(v.DateVisite)<now(),'B',@annee
from kbabtel.`kob-Cadref-Reservation` r
inner join kbabtel.`kob-Cadref-Visite` v on v.Id=r.VisiteId;
*/

truncate kbabtel.`kob-Cadref-AdherentAnnee`;
insert into kbabtel.`kob-Cadref-AdherentAnnee` (umod,gmod,omod,AdherentId,Numero,Annee,NotesAnnuelles,Adherent,ClasseId,
Cotisation,Cours,Reglement,Differe,Regularisation)
select 7,7,7,a.Id,e.Numero,e.Annee,NotesTemp,e.Adherent,c.Id,
e.Cotisation,e.Montant,e.Reglement,e.Differe,e.Regul
from cadref18.Eleves e
inner join kbabtel.`kob-Cadref-Adherent` a on a.Numero=e.Numero
left join kbabtel.`kob-Cadref-Classe` c on c.CodeClasse=concat(substr(e.Delegue,1,1),substr(e.Delegue,2,2),substr(e.Delegue,4,2),substr(e.Delegue,6,1),substr(e.Delegue,7,1))
where e.Annee=@annee;

update `kob-Cadref-AdherentAnnee` a
set a.Cours=(select ifnull(sum(ifnull(Prix-Reduction-Soutien,0)),0) from `kob-Cadref-Inscription` i where i.AdherentId=a.AdherentId and i.Annee=@annee and i.Supprime=0 and i.Attente=0),
a.Visites=(select ifnull(sum(ifnull(Prix-Reduction,0)),0) from `kob-Cadref-Reservation` r where r.AdherentId=a.AdherentId and r.Annee=@annee and r.Supprime=0 and r.Attente=0),
a.Reglement=(select ifnull(sum(ifnull(Montant,0)),0) from `kob-Cadref-Reglement` r where r.AdherentId=a.AdherentId and r.Annee=@annee and r.Supprime=0 and (r.Differe=0 or r.Encaisse=1)),
a.Differe=(select ifnull(sum(ifnull(Montant,0)),0) from `kob-Cadref-Reglement` r where r.AdherentId=a.AdherentId and r.Annee=@annee and r.Supprime=0 and (r.Differe=1 and r.Encaisse=0))
where a.Annee=@annee;

update kbabtel.`kob-Cadref-AdherentAnnee` a
left join (
select AdherentId,min(DateReglement) as dt
from kbabtel.`kob-Cadref-Reglement`
where Annee=@annee
group by AdherentId
) t on t.AdherentId=a.AdherentId
set a.DateCotisation=t.dt
where a.Annee=@annee;

/*
# PRENOMS
DROP FUNCTION IF EXISTS aaprenom; 
SET GLOBAL  log_bin_trust_function_creators=TRUE; 
DELIMITER | 
CREATE FUNCTION aaprenom( str VARCHAR(128) ) 
RETURNS VARCHAR(128) 
BEGIN 
  DECLARE c CHAR(1); 
  DECLARE s VARCHAR(128); 
  DECLARE i INT DEFAULT 1; 
  DECLARE bool INT DEFAULT 1; 
  DECLARE punct CHAR(18) DEFAULT ' -\'';
  SET s = LCASE( str ); 
  WHILE i <= LENGTH( str ) DO 
    BEGIN 
      SET c = SUBSTRING( s, i, 1 ); 
      IF LOCATE( c, punct ) > 0 THEN 
        SET bool = 1; 
      ELSEIF bool=1 THEN  
        BEGIN 
          IF c >= 'a' AND c <= 'z' THEN  
            BEGIN 
              SET s = CONCAT(LEFT(s,i-1),UCASE(c),SUBSTRING(s,i+1)); 
              SET bool = 0; 
            END; 
          ELSEIF c >= '0' AND c <= '9' THEN 
            SET bool = 0; 
          END IF; 
        END; 
      END IF; 
      SET i = i+1; 
    END; 
  END WHILE; 
  RETURN s; 
END; 
| 
delimiter ;
update `kob-Cadref-Adherent` set Prenom=aaprenom(Prenom);
update `kob-Cadref-Enseignant` set Prenom=aaprenom(Prenom);
*/

DROP procedure IF EXISTS aareduc; 
DELIMITER | 
CREATE procedure aareduc(p0 double,p1 double,p2 double) 
BEGIN 
	update kbabtel.`kob-Cadref-Classe` c
	inner join kbabtel.`kob-Cadref-Niveau` n on n.Id=c.NiveauId
	inner join kbabtel.`kob-Cadref-Discipline` d on d.Id=n.DisciplineId
	set c.Reduction1=(p0-p1),c.Reduction2=(p0-p2),c.DateReduction1=unix_timestamp('2019-01-01'),c.DateReduction2=unix_timestamp('2019-03-01')
	where c.Prix=p0 and d.WebDiscipline not like 'IN%'; 
END; 
| 
delimiter ;
call kbabtel.aareduc(51,34,17);
call kbabtel.aareduc(102,68,34);
call kbabtel.aareduc(80,52,26);
call kbabtel.aareduc(122,80,40);
call kbabtel.aareduc(75,50,25);
call kbabtel.aareduc(115,76,38);
call kbabtel.aareduc(217,144,72);
call kbabtel.aareduc(120,80,40);
call kbabtel.aareduc(135,90,45);
call kbabtel.aareduc(185,122,61);
call kbabtel.aareduc(160,106,53);
call kbabtel.aareduc(162,108,54);
call kbabtel.aareduc(87,58,29);
update kbabtel.`kob-Cadref-Classe` set Reduction1=0, DateReduction1=null where CodeClasse='NHIRE11';




