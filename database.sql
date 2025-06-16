-- Structure de la base de données pour MonBudgetEtudiant

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des revenus
CREATE TABLE revenus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mode VARCHAR(20) NOT NULL, -- 'avec_alternance' ou 'sans_alternance'
    libelle VARCHAR(100) NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des dépenses
CREATE TABLE depenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mode VARCHAR(20) NOT NULL, -- 'avec_alternance' ou 'sans_alternance'
    libelle VARCHAR(100) NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    categorie VARCHAR(50) NOT NULL, -- 'Logement', 'Alimentation', 'Transport', etc.
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des simulations APL
CREATE TABLE simulations_apl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    revenus DECIMAL(10, 2) NOT NULL,
    loyer DECIMAL(10, 2) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    nb_personnes INT NOT NULL,
    estimation_apl DECIMAL(10, 2) NOT NULL,
    date_simulation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);