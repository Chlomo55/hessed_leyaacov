-- Table pour stocker les demandes d'articles
CREATE TABLE IF NOT EXISTS demande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_article INT NOT NULL,
    id_preteur INT NOT NULL,
    id_emprunteur INT NOT NULL,
    date_retrait DATE NOT NULL,
    date_retour DATE NOT NULL,
    message TEXT NOT NULL,
    statut TINYINT DEFAULT 0, -- 0: en attente, 1: acceptée, 2: refusée, 3: terminée
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_article) REFERENCES article(id),
    FOREIGN KEY (id_preteur) REFERENCES users(id),
    FOREIGN KEY (id_emprunteur) REFERENCES users(id)
);

-- Table pour stocker les messages de la messagerie
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_demande INT NOT NULL,
    id_preteur INT NOT NULL,
    id_emprunteur INT NOT NULL,
    id_expediteur INT NOT NULL, -- celui qui envoie le message
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_demande) REFERENCES demande(id),
    FOREIGN KEY (id_preteur) REFERENCES users(id),
    FOREIGN KEY (id_emprunteur) REFERENCES users(id),
    FOREIGN KEY (id_expediteur) REFERENCES users(id)
);
