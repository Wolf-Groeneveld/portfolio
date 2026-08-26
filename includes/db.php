<?php
require_once __DIR__ . '/config.php';

function db(): ?PDO
{
    static $pdo = null;
    static $failed = false;

    if ($pdo !== null || $failed) {
        return $pdo;
    }

    try {
        if (getenv('SQLITE_PATH') || defined('SQLITE_PATH')) {
            $path = getenv('SQLITE_PATH') ?: SQLITE_PATH;
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } else {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        try {
            $pdo->exec("UPDATE projects SET title = 'Sylensial''s Nightmare' WHERE title = 'Sylensials Nightmare'");
        } catch (PDOException $e) {
            error_log('project title update skipped: ' . $e->getMessage());
        }
    } catch (PDOException $e) {
        $failed = true;
        error_log('DB connection failed: ' . $e->getMessage());
    }

    return $pdo;
}

function get_projects(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    try {
        return $pdo->query(
            'SELECT title, summary, tech_stack, image_path, repo_url, live_url, download_url, is_featured
             FROM projects WHERE is_visible = 1
             ORDER BY is_featured DESC, sort_order ASC, created_at DESC'
        )->fetchAll();
    } catch (PDOException $e) {
        error_log('get_projects failed: ' . $e->getMessage());
        return [];
    }
}

function get_skills_grouped(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    try {
        $rows = $pdo->query(
            "SELECT category, name FROM skills
             ORDER BY CASE category
                WHEN 'Talen' THEN 1
                WHEN 'Frameworks & libraries' THEN 2
                WHEN 'Databases' THEN 3
                WHEN 'Tools' THEN 4
                ELSE 5 END,
                      sort_order, name"
        )->fetchAll();
    } catch (PDOException $e) {
        error_log('get_skills_grouped failed: ' . $e->getMessage());
        return [];
    }

    $grouped = [];
    foreach ($rows as $row) {
        $grouped[$row['category']][] = $row['name'];
    }
    return $grouped;
}

function save_message(string $name, string $email, string $message): bool
{
    $pdo = db();
    if (!$pdo) {
        return false;
    }
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO messages (name, email, message) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$name, $email, $message]);
    } catch (PDOException $e) {
        error_log('save_message failed: ' . $e->getMessage());
        return false;
    }
}
