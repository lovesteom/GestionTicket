CREATE DATABASE gestion_ticket;

USE gestion_ticket;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    nbr_ticket INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE users_auth (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Identifiant unique pour chaque utilisateur
    name VARCHAR(100) NOT NULL,        -- Nom de l'utilisateur
    email VARCHAR(255) NOT NULL UNIQUE, -- Email unique pour chaque utilisateur
    password VARCHAR(255) NOT NULL,    -- Mot de passe haché
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Date de création de l'utilisateur
);