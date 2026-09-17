CREATE TABLE IF NOT EXISTS database_migrations (
    version INT NOT NULL PRIMARY KEY,
    applied_at DATETIME NOT NULL
);
