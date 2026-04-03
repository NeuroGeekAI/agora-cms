<?php
/**
 * AgoraCMS — Classe Database PDO sécurisée
 * Supporte MySQL (production) + SQLite (démo locale)
 */
defined('AGORA') or die('Accès direct interdit.');
class Database {
    private static ?PDO $pdo = null;
    public static function connect(): PDO {
        if (self::$pdo === null) {
            // ── MODE SQLITE (démo locale) ────────────────────
            $db_type = defined('DB_TYPE') ? DB_TYPE : 'mysql';
            if ($db_type === 'sqlite') {
                $sqlite_path = defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : ROOT_PATH . '/data/souverain-demo.sqlite';
                self::$pdo = new PDO('sqlite:' . $sqlite_path, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$pdo->exec("PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;");
            } else {
                // ── MODE MYSQL (production) ──────────────────
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $opts = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                // MYSQL_ATTR_FOUND_ROWS n'existe que si le driver MySQL PDO est chargé
                if (defined('PDO::MYSQL_ATTR_FOUND_ROWS')) {
                    $opts[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
                }
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
            }
        }
        return self::$pdo;
    }
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public static function fetch(string $sql, array $params = []): ?array {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }
    public static function lastInsertId(): string {
        return self::connect()->lastInsertId();
    }
    public static function count(string $sql, array $params = []): int {
        return (int)(self::query($sql, $params)->fetchColumn() ?? 0);
    }
}

