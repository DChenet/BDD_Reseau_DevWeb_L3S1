CREATE TABLE client (
    no_client INT PRIMARY KEY,
    prenom VARCHAR(50),
    nom VARCHAR(50),
    mdp VARCHAR(150),
    mail VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE reservation (
    no_reservation INT PRIMARY KEY,
    date_reservation DATE,
    nb_voyageurs INT,
    nb_bagages INT,
    moyen_paiement VARCHAR(50),
    montant INT,
    no_client INT REFERENCES client(no_client)
);

CREATE TABLE trajet (
    no_trajet INT PRIMARY KEY,
    date_trajet DATE,
    heure_depart TIME,
    heure_arrivee TIME,
    prix INT,
    etat VARCHAR(50)
);

CREATE TABLE incident (
    no_incident INT PRIMARY KEY,
    date_incident DATE,
    heure_incident TIME,
    type VARCHAR(50),
    resolu BOOLEAN
);

CREATE TABlE gare (
    nom_gare VARCHAR(50) PRIMARY KEY,
    nb_quai INT
);

CREATE TABlE train (
    no_train INT PRIMARY KEY,
    modele VARCHAR(50),
    conducteur VARCHAR(50),
    capacite INT,
    etat_train BOOLEAN
);

CREATE TABlE part_de (
    nom_gare_depart VARCHAR(50) REFERENCES gare(nom_gare),
    no_trajet INT REFERENCES trajet,
    quai_depart INT,
    PRIMARY KEY(nom_gare_depart, no_trajet)
);

CREATE TABlE arrive_a (
    nom_gare_arrivee VARCHAR(50) REFERENCES gare(nom_gare),
    no_trajet INT REFERENCES trajet,
    quai_arrive INT,
    PRIMARY KEY(nom_gare_arrivee, no_trajet)
);

CREATE TABlE correspond (
    no_trajet INT REFERENCES trajet,
    no_reservation INT REFERENCES reservation,
    PRIMARY KEY(no_reservation, no_trajet)
);

CREATE TABLE se_produit (
    no_incident INT REFERENCES incident,
    no_trajet INT REFERENCES trajet,
    PRIMARY KEY(no_incident, no_trajet)
);

CREATE TABLE effectue_trajet (
    no_train INT REFERENCES train,
    no_trajet INT REFERENCES trajet,
    PRIMARY KEY(no_train, no_trajet)
);

INSERT INTO client VALUES 
(1, 'Hugo', 'DUCLY', 'azerty', 'hugo.ducly@gmail.com'),
(2, 'Dorian', 'CHENET', 'azerty2', 'dorian.chenet@gmail.com'),
(3, 'Albert', 'TABLE', 'azerty3', 'albert.atable@gmail.com');

INSERT INTO trajet VALUES
(1, '2020-01-01', '09:30:00', '10:45:00', 20, 'A_VENIR'),
(2, '2020-01-01', '10:46:00', '12:00:00', 30, 'A_VENIR'),
(3, '2020-01-01', '09:30:00', '10:45:00', 25, 'A_VENIR'),
(4, '2020-01-01', '10:46:00', '12:00:00', 50, 'A_VENIR'),
(5, '2020-01-01', '07:00:00', '07:10:00', 10, 'A_VENIR'),
(6, '2020-01-01', '07:10:00', '07:15:00', 10, 'A_VENIR'),
(7, '2020-01-01', '07:15:00', '07:20:00', 10, 'A_VENIR'),
(8, '2020-01-01', '09:00:00', '09:10:00', 10, 'A_VENIR'),
(9, '2020-01-01', '09:11:00', '09:20:00', 10, 'A_VENIR'),
(10, '2020-01-01', '09:20:00', '09:30:00', 10, 'A_VENIR'),
(11, '2020-01-02', '09:30:00', '10:45:00', 20, 'A_VENIR'),
(12, '2020-01-02', '10:46:00', '12:00:00', 30, 'A_VENIR'),
(13, '2020-01-02', '09:30:00', '10:45:00', 25, 'A_VENIR'),
(14, '2020-01-02', '10:46:00', '12:00:00', 50, 'A_VENIR'),
(15, '2020-01-02', '07:00:00', '07:10:00', 10, 'A_VENIR'),
(16, '2020-01-02', '07:10:00', '07:15:00', 10, 'A_VENIR'),
(17, '2020-01-02', '07:15:00', '07:20:00', 10, 'A_VENIR'),
(18, '2020-01-02', '09:00:00', '09:10:00', 10, 'A_VENIR'),
(19, '2020-01-02', '09:11:00', '09:20:00', 10, 'A_VENIR'),
(20, '2020-01-02', '09:20:00', '09:30:00', 10, 'A_VENIR'),
(21, '2019-12-01', '09:30:00', '10:45:00', 20, 'PASSE'),
(22, '2019-12-02', '09:20:00', '10:45:00', 20, 'PASSE');

INSERT INTO incident VALUES
(1, '2019-12-01', '09:32:00', 'malaise voyageur', TRUE),
(2, '2019-12-01', '09:34:00', 'malaise voyageur', TRUE),
(3, '2019-12-01', '09:36:00', 'animal sur les voies', TRUE),
(4, '2019-12-2', '09:42:00', 'conducteur evanoui', TRUE),
(5, '2019-12-2', '09:44:00', 'malaise voyageur', TRUE);

INSERT INTO se_produit VALUES
(1, 21),
(2, 21),
(3, 21),
(4, 22),
(5, 22);


INSERT INTO reservation(no_reservation, date_reservation, nb_voyageurs, nb_bagages, moyen_paiement, montant, no_client) VALUES
(1, '2020-01-01', 1, 1, 'CB', '120', 1),
(2, '2020-01-01', 1, 2, 'PAYPAL', '100', 1),
(3, '2020-01-01', 1, 2, 'PAYPAL', '100', 1),
(4, '2020-01-02', 1, 1, 'CB', '120', 2),
(5, '2020-01-02', 1, 2, 'PAYPAL', '100', 2),
(6, '2020-01-02', 1, 2, 'PAYPAL', '100', 3),
(7, '2019-12-01', 1, 2, 'PAYPAL', '100', 3);

INSERT INTO correspond VALUES
(1, 1),
(2, 2),
(3, 3),
(1, 4),
(2, 5),
(3, 6),
(22, 7);

INSERT INTO gare VALUES
('GARE DU NORD', 10),
('LILLE', 5),
('ANGOULEME', 5),
('BORDEAUX', 5),
('CAEN', 5),
('PERSAN BEAUMONT', 5),
('CHAMPAGNE', 5),
('VALMONDOIS', 5),
('PONTOISE', 5),
('CHERBOURG', 5),
('CERGY PREF', 5),
('CONFLANS', 5),
('NANTERRE', 5),
('CHATELET', 5),
('ARRAS', 5);

INSERT INTO part_de VALUES
('GARE DU NORD', 1, 1),
('ARRAS', 2, 1),
('GARE DU NORD', 3, 1),
('CAEN', 4, 1),
('PERSAN BEAUMONT', 5, 1),
('CHAMPAGNE', 6, 1),
('VALMONDOIS', 7, 1),
('CERGY PREF', 8, 1),
('CONFLANS', 9, 1),
('NANTERRE', 10, 1),
('GARE DU NORD', 11, 1),
('ARRAS', 12, 1),
('GARE DU NORD', 13, 1),
('CAEN', 14, 1),
('PERSAN BEAUMONT', 15, 1),
('CHAMPAGNE', 16, 1),
('VALMONDOIS', 17, 1),
('CERGY PREF', 18, 1),
('CONFLANS', 19, 1),
('NANTERRE', 20, 1),
('NANTERRE', 21, 1),
('PERSAN BEAUMONT', 22, 1);


INSERT INTO arrive_a VALUES
('ARRAS', 1, 1),
('LILLE', 2, 1),
('CAEN', 3, 1),
('CHERBOURG', 4, 1),
('CHAMPAGNE', 5, 1),
('VALMONDOIS', 6, 1),
('PONTOISE', 7, 1),
('CONFLANS', 8, 1),
('NANTERRE', 9, 1),
('CHATELET', 10, 1),
('ARRAS', 11, 1),
('LILLE', 12, 1),
('CAEN', 13, 1),
('CHERBOURG', 14, 1),
('CHAMPAGNE', 15, 1),
('VALMONDOIS', 16, 1),
('PONTOISE', 17, 1),
('CONFLANS', 18, 1),
('NANTERRE', 19, 1),
('CHATELET', 20, 1),
('CHATELET', 21, 1),
('GARE DU NORD', 22, 1);

INSERT INTO train VALUES
(1, 'TER', 'Andre ARGH', 3, TRUE),
(2, 'TGV', 'Gilbert OUILLE', 3, TRUE),
(3, 'TGV', 'Almamy CAMERA', 3, TRUE),
(4, 'RER', 'Alexis CHALET', 3, TRUE);

INSERT INTO effectue_trajet VALUES 
(2, 1),
(2, 2),
(3, 3),
(3, 4),
(1, 5),
(1, 6),
(1, 7),
(4, 8),
(4, 9),
(4, 10),
(2, 11),
(2, 12),
(3, 13),
(3, 14),
(1, 15),
(1, 16),
(1, 17),
(4, 18),
(4, 19),
(4, 20),
(1, 21),
(1, 22);