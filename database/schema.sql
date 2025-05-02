-- Włącz wymuszanie kluczy obcych (SQLite nie robi tego domyślnie)
PRAGMA foreign_keys = ON;

-- Tabela użytkowników
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabela portfeli
CREATE TABLE IF NOT EXISTS wallets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- Tabela nominałów
CREATE TABLE IF NOT EXISTS nominaly (
    id SERIAL PRIMARY KEY,
    wallet_id INTEGER NOT NULL,
    nominal INTEGER NOT NULL,
    type VARCHAR(20) NOT NULL,
    count INTEGER NOT NULL,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    UNIQUE (wallet_id, nominal, type)
);

-- Tabela transakcji
CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    wallet_id INTEGER NOT NULL,
    type VARCHAR(10) NOT NULL,
    amount NUMERIC(10,2) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id)
);
