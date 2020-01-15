-- Requete pour savoir si un trajet existe avec les carcateristiques demandees

-- si on a donne depart et arrivee
SELECT * FROM (SELECT no_trajet FROM (part_de NATURAL JOIN arrive_a) WHERE 
nom_gare_arrivee='CAEN' AND 
nom_gare_depart='GARE DU NORD' 
) AS bon_trajet NATURAL JOIN trajet ORDER BY date_trajet ASC;

-- si on a donne depart arrivee heure_depart et date
SELECT * FROM
(SELECT no_trajet FROM (part_de NATURAL JOIN arrive_a) WHERE 
nom_gare_arrivee='Montpelier' AND 
nom_gare_depart='Gare du Nord' 
) AS bon_trajet NATURAL JOIN trajet WHERE 
heure_depart='10:30:00' AND 
date_trajet='2019-02-03'
ORDER BY prix ASC;

-- si on a donne depart arrivee heure_depart
SELECT * FROM
(SELECT no_trajet FROM (part_de NATURAL JOIN arrive_a) WHERE 
nom_gare_arrivee='CHERBOURG' AND 
nom_gare_depart='CAEN' 
) AS bon_trajet NATURAL JOIN trajet WHERE 
heure_depart='10:30:00'
ORDER BY heure_depart ASC;

-- si on a donne depart arrivee date
SELECT * FROM
(SELECT no_trajet FROM (part_de NATURAL JOIN arrive_a) WHERE 
nom_gare_arrivee='Montpelier' AND 
nom_gare_depart='Gare du Nord' 
) AS bon_trajet NATURAL JOIN trajet WHERE 
date_trajet='2019-02-03'
ORDER BY date_trajet ASC;

------------------------------------------------------

-- Requete pour savoir si un trajet a encore de la place
-- Besoin d'un numero de trajet

SELECT (capacite - somme) as Nombre_places_restantes 
FROM 
(SELECT capacite FROM effectue_trajet NATURAL JOIN train WHERE no_trajet = 1) AS c,
(SELECT COUNT(no_reservation) AS somme FROM correspond WHERE no_trajet = 1) AS r;

------------------------------------------------------

-- Requete pour connaitre le classement des depenses des clients

SELECT prenom, nom, SUM(montant) AS depenses FROM
client NATURAL JOIN prend NATURAL JOIN reservation
GROUP BY nom, prenom
ORDER BY depenses DESC;

------------------------------------------------------

-- Requete pour connaitre le classement des trajets qui provoquent 
-- le plus d accdients

SELECT COUNT(no_incident) AS nombre_incidents, nom_gare_depart AS gare_depart, nom_gare_arrivee AS gare_arrivee  FROM 
se_produit NATURAL JOIN trajet NATURAL JOIN arrive_a NATURAL JOIN part_de 
GROUP BY nom_gare_depart, nom_gare_arrivee
ORDER BY nombre_incidents DESC; 

------------------------------------------------------

-- Requete pour connaitre le trafic d'une gare groupé par train

SELECT COUNT(no_trajet) AS nbre, modele AS type_train,
extract(MONTH from date_trajet)  AS mois , extract(YEAR from date_trajet) AS annee FROM
arrive_a  NATURAL JOIN part_de NATURAL JOIN train NATURAL JOIN effectue_trajet NATURAL JOIN trajet 
WHERE nom_gare_arrivee = 'GARE DU NORD' OR nom_gare_depart = 'GARE DU NORD'
GROUP BY type_train, mois, annee;

------------------------------------------------------

-- Recette de l'entreprise sur une periode

SELECT SUM(montant) AS recette, extract(MONTH from date_reservation)  AS mois , extract(YEAR from date_reservation) AS annee
FROM reservation 
GROUP BY mois, annee
ORDER BY mois, annee DESC;

------------------------------------------------------

-- Pour un client donne, historique des reservations

SELECT date_reservation,nb_voyageurs,nb_bagages,montant,moyen_paiement,date_trajet,heure_depart,heure_arrivee,nom_gare_depart,nom_gare_arrivee,quai_depart,quai_arrive FROM (
SELECT * FROM (SELECT no_reservation,date_reservation,nb_voyageurs,nb_bagages,montant,moyen_paiement FROM reservation WHERE no_client=1) AS j1
NATURAL JOIN correspond NATURAL JOIN trajet NATURAL JOIN part_de NATURAL JOIN arrive_a) AS j2;

------------------------------------------------------

-- Nombre d'heures de travail dun conducteur

SELECT  EXTRACT(YEAR FROM date_trajet) AS annee, 
EXTRACT(MONTH FROM date_trajet) AS mois,
SUM(heure_arrivee-heure_depart) AS temps_de_travail FROM
trajet NATURAL JOIN effectue_trajet NATURAL JOIN train
WHERE conducteur = 'Andre ARGH'
GROUP BY mois, annee