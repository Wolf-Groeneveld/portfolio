-- mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS portfolio
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portfolio;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS projects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(120)  NOT NULL,
    summary     VARCHAR(300)  NOT NULL,
    description TEXT          NULL,
    tech_stack  VARCHAR(255)  NOT NULL,  -- kommagescheiden, bijv. "PHP,MySQL,JavaScript"
    repo_url     VARCHAR(255) NULL,
    live_url     VARCHAR(255) NULL,
    download_url VARCHAR(255) NULL,     -- bijv. assets/downloads/game.zip
    image_path  VARCHAR(255)  NULL,      -- bijv. assets/img/project1.png
    is_featured TINYINT(1)    NOT NULL DEFAULT 0,
    is_visible  TINYINT(1)    NOT NULL DEFAULT 1,
    sort_order  INT           NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS skills (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category   VARCHAR(60) NOT NULL,   -- bijv. "Talen", "Databases", "Tools"
    name       VARCHAR(60) NOT NULL,
    sort_order INT         NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    message    TEXT         NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------------
-- Projecten

INSERT INTO projects (title, summary, description, tech_stack, repo_url, live_url, download_url, image_path, is_featured, sort_order) VALUES
('Sylensial''s Nightmare',
 'Tower defense, zelf gebouwd in Python. Speelbaar hier in de browser.',
 'Zelf ontworpen en gebouwd: game-logica, levels, vijanden, waves en balans. Direct speelbaar in de browser via WebAssembly (pygbag).',
 'Python,Pygame',
 NULL, 'play.php', NULL, 'assets/img/sylensial-nightmare.png', 1, 1),

('Deze portfolio-website',
 'Deze site: PHP en MySQL, zonder frameworks. Projecten en skills komen uit de database.',
 'Dynamische portfolio waarbij projecten en skills uit MySQL komen, met een contactformulier dat berichten opslaat in de database en een eigen responsive dark design.',
 'PHP,MySQL,JavaScript,CSS',
 'https://github.com/Wolf-Groeneveld/portfolio', NULL, NULL, NULL, 0, 2);

-- ---------------------------------------------------------------------------
-- Skills

INSERT INTO skills (category, name, sort_order) VALUES
('Talen', 'PHP',        1),
('Talen', 'JavaScript', 2),
('Talen', 'Python',     3),
('Talen', 'C#',         4),
('Talen', 'HTML & CSS', 5),
('Frameworks & libraries', 'React',        1),
('Frameworks & libraries', 'Laravel',      2),
('Frameworks & libraries', 'Node.js',      3),
('Frameworks & libraries', 'jQuery',       4),
('Frameworks & libraries', 'AJAX',         5),
('Frameworks & libraries', 'Bootstrap',    6),
('Frameworks & libraries', 'Tailwind CSS', 7),
('Frameworks & libraries', 'p5.js',        8),
('Frameworks & libraries', 'OOP',          9),
('Databases', 'MySQL', 1),
('Tools', 'Git & GitHub', 1);
