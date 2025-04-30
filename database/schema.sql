-- Włącz wymuszanie kluczy obcych (SQLite nie robi tego domyślnie)
PRAGMA foreign_keys = ON;

-- Tabela użytkowników
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
);

-- Tabela portfeli
CREATE TABLE IF NOT EXISTS wallets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    currency TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- Tabela nominałów
CREATE TABLE IF NOT EXISTS nominals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wallet_id INTEGER NOT NULL,
    value INTEGER NOT NULL,
    type TEXT NOT NULL,
    count INTEGER DEFAULT 0,
    FOREIGN KEY(wallet_id) REFERENCES wallets(id)
);

-- Tabela historii transakcji
CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wallet_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    amount INTEGER NOT NULL,
    currency TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(wallet_id) REFERENCES wallets(id)
);
