CREATE TYPE stato_azienda_enum AS ENUM ('Attiva', 'Fallita', 'Acquisita', 'Rinominata');
CREATE TYPE tipo_modello_enum AS ENUM ('Autobus', 'Tram', 'Treno', 'Metro', 'Filobus', 'Altro');
CREATE TYPE stato_veicolo_enum AS ENUM ('Attivo', 'Abbandonato', 'Demolito', 'Museo', 'Ceduto');
CREATE TYPE stato_possesso_enum AS ENUM ('Attivo', 'Deposito indefinito', 'Venduto');
CREATE TYPE tipo_media_enum AS ENUM ('Immagine', 'Video', 'Documento');
CREATE TYPE licenza_media_enum AS ENUM ('Pubblico dominio', 'Creative Commons', 'Proprietario');
CREATE TYPE stato_modifica_enum AS ENUM ('In attesa', 'Approvato', 'Rifiutato');

CREATE TABLE nazione (
    nome VARCHAR(100) UNIQUE NOT NULL PRIMARY KEY, -- Nome ufficiale della nazione
    codice_iso VARCHAR(3) UNIQUE , -- Codice ISO 3166-1 Alpha-3 (es. ITA, FRA, USA)
    codice_iso2 VARCHAR(2) UNIQUE , -- Codice ISO 3166-1 Alpha-2 (es. IT, FR, US)
    continente VARCHAR(50), -- Continente di appartenenza
    capitale VARCHAR(100), -- Capitale della nazione
    bandiera TEXT -- BLOB
);

CREATE TABLE azienda_costruttrice (
    id_azienda SERIAL PRIMARY KEY,
    nome VARCHAR(255) UNIQUE NOT NULL,
    short_desc VARCHAR(100),
    long_desc TEXT,
    fondazione DATE,
    chiusura DATE,
    sede VARCHAR(255),
    nazione VARCHAR(100),
    sito_web VARCHAR(255),
    stato stato_azienda_enum DEFAULT 'Attiva',
    logo TEXT, --BLOB
    id_successore INT NULL,  -- Azienda che ha acquisito o nuovo nome
    FOREIGN KEY (id_successore) REFERENCES azienda_costruttrice(id_azienda) ON DELETE SET NULL,
    FOREIGN KEY (nazione) REFERENCES nazione(nome) ON DELETE SET NULL
);


CREATE TABLE modello (
    id_modello SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
    tipo tipo_modello_enum ,
    anno_inizio_produzione DATE,
    anno_fine_produzione DATE NULL,
    capienza INT,
    lunghezza DECIMAL(5,2),
    larghezza DECIMAL(5,2),
    altezza DECIMAL(5,2),
    peso DECIMAL(6,2),
    motorizzazione VARCHAR(100),
    velocita_massima DECIMAL(5,2),
    descrizione TEXT,
    totale_veicoli INT DEFAULT 0, -- Numero totale di veicoli prodotti
);

CREATE TABLE variante_modello (
    id_modello_base INT NOT NULL,
    id_modello_variante INT NOT NULL,
    PRIMARY KEY (id_modello_base, id_modello_variante),
    FOREIGN KEY (id_modello_base) REFERENCES modello(id_modello) ON DELETE CASCADE,
    FOREIGN KEY (id_modello_variante) REFERENCES modello(id_modello) ON DELETE CASCADE
);

CREATE TABLE azienda_operatrice (
    id_azienda_operatrice SERIAL PRIMARY KEY,     -- Chiave primaria
    nome_azienda VARCHAR(255) NOT NULL,           -- Nome dell'azienda
    nome_precedente VARCHAR(255),                 -- Nome precedente, se rinominata
    sede_legale VARCHAR(255),                     -- Sede legale
    città VARCHAR(100),                           -- Città dell'azienda
    paese VARCHAR(100),                           -- Paese in cui opera l'azienda
    numero_telefono VARCHAR(20),                  -- Numero di telefono
    email VARCHAR(255),                           -- Email di contatto
    data_inizio_attività DATE,                    -- Data di inizio attività
    descrizione TEXT,                             -- Descrizione dell'azienda
    foto_logo TEXT,                               -- BLOB
    stato_azienda stato_azienda_enum,    -- Stato dell'azienda
    id_successore INT,                            -- ID dell'azienda acquirente, se acquisita
    FOREIGN KEY (id_successore) REFERENCES azienda_operatrice(id_azienda_operatrice) ON DELETE SET NULL,
    FOREIGN KEY (paese) REFERENCES nazione(nome) ON DELETE SET NULL
);

CREATE TABLE veicolo (
    id_veicolo SERIAL PRIMARY KEY,          -- ID del veicolo
    id_modello INT NOT NULL,                -- Collegamento al modello
    anno_produzione INT,                    -- Anno di produzione
    numero_targa VARCHAR(50),                -- Numero di targa
    descrizione TEXT,                        -- Descrizione del veicolo
    stato_veicolo stato_veicolo_enum, -- Stato del veicolo
    FOREIGN KEY (id_modello) REFERENCES modello(id_modello) ON DELETE CASCADE -- Collegamento al modello
);

CREATE TABLE possesso_veicolo (
    id_possesso SERIAL PRIMARY KEY,          -- ID del possesso
    id_veicolo INT NOT NULL,                 -- ID del veicolo (riferimento alla tabella veicolo)
    id_azienda_operatrice INT NOT NULL,      -- ID dell'azienda operatrice che possiede il veicolo
    data_inizio_possesso DATE ,      -- Data di inizio del possesso
    data_fine_possesso DATE,                 -- Data di fine del possesso (NULL se ancora posseduto)
    stato_veicolo_azienda stato_possesso_enum ,-- Stato del veicolo durante il possesso
    FOREIGN KEY (id_veicolo) REFERENCES veicolo(id_veicolo) ON DELETE CASCADE,
    FOREIGN KEY (id_azienda_operatrice) REFERENCES azienda_operatrice(id_azienda_operatrice) ON DELETE CASCADE
);

CREATE TABLE media (
    id_media SERIAL PRIMARY KEY,
    tipo_media tipo_media_enum NOT NULL, -- Supporto per più tipi di file
    url_media TEXT NOT NULL, -- Percorso del file
    descrizione TEXT, -- Descrizione dell'immagine
    copyright VARCHAR(255), -- Nome del proprietario dei diritti
    licenza licenza_media_enum NOT NULL, -- Licenza del file
    data_caricamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE media_entita (
    id_media INT NOT NULL,
    entita_tipo VARCHAR(50), -- Tipo di entità collegata (es. veicolo, azienda, etc.) 
    id_entita INT not NULL, -- ID dell'entità collegata, NULL se è un media generico
    ruolo VARCHAR(50) ,
    PRIMARY KEY (id_media, entita_tipo, id_entita),
    FOREIGN KEY (id_media) REFERENCES media(id_media) ON DELETE CASCADE
);

CREATE TABLE stato_modello_azienda (
    id_azienda INT NOT NULL,
    id_modello INT NOT NULL,
    stato_veicolo stato_veicolo_enum NOT NULL,
    totale INT DEFAULT 0,
    FOREIGN KEY (id_azienda) REFERENCES azienda_operatrice(id_azienda_operatrice) ON DELETE CASCADE,
    FOREIGN KEY (id_modello) REFERENCES modello(id_modello) ON DELETE CASCADE,
    PRIMARY KEY (id_azienda, id_modello, stato_veicolo)
);

CREATE TABLE modifiche_in_sospeso (
    id_modifica SERIAL PRIMARY KEY,
    id_gruppo_modifica INT NOT NULL, -- ID unico per raggruppare più campi modificati in una singola operazione
    tabella_destinazione VARCHAR(50) NOT NULL, -- Nome della tabella modificata
    id_entita TEXT NULL, -- ID dell'entità da modificare
    campo_modificato VARCHAR(50) NOT NULL, -- Nome del campo modificato
    valore_nuovo TEXT NOT NULL, -- Nuovo valore proposto
    valore_vecchio TEXT NULL, -- Valore attuale (per riferimento)
    stato stato_modifica_enum DEFAULT 'In attesa',
    autore VARCHAR(255), -- Utente che ha fatto la richiesta
    data_richiesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE modello_azienda (
    id_modello INT NOT NULL,
    id_azienda INT NOT NULL,
    ruolo VARCHAR(100), -- ad esempio: "Produttore principale", "Collaboratore", "Su licenza", ecc.
    PRIMARY KEY (id_modello, id_azienda),
    FOREIGN KEY (id_modello) REFERENCES modello(id_modello) ON DELETE CASCADE,
    FOREIGN KEY (id_azienda) REFERENCES azienda_costruttrice(id_azienda) ON DELETE CASCADE
);

CREATE TABLE veicolo_azienda_produttrice (
    id_veicolo INT NOT NULL,
    id_azienda INT NOT NULL,
    ruolo VARCHAR(100), -- ad esempio: "Costruttore", "Assemblatore", "Restauratore"
    PRIMARY KEY (id_veicolo, id_azienda),
    FOREIGN KEY (id_veicolo) REFERENCES veicolo(id_veicolo) ON DELETE CASCADE,
    FOREIGN KEY (id_azienda) REFERENCES azienda_costruttrice(id_azienda) ON DELETE CASCADE
);
