<?php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'examen1';

$dsn = sprintf('mysql:host=%s;charset=utf8mb4', $dbHost);
$pdo = new PDO($dsn, $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
$pdo->exec("USE `$dbName`");

function seedDatabase(PDO $pdo): void
{
    $pdo->exec('DROP TABLE IF EXISTS users');
    $pdo->exec('CREATE TABLE users (user_id INT AUTO_INCREMENT PRIMARY KEY, user VARCHAR(255), password VARCHAR(255))');
    $pdo->exec("INSERT INTO users (user, password) VALUES
        ('alice', 'password123'),
        ('bob', 'hunter2'),
        ('carol', 's3cr3t'),
        ('dave', 'letmein')");
}

$resetMessage = '';
$shouldReset = isset($_POST['reset_db']);
if ($shouldReset) {
    seedDatabase($pdo);
    $resetMessage = 'Base de datos restaurada.';
}

$existing = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ((int) $existing === 0) {
    seedDatabase($pdo);
}

$input = $_POST['username'] ?? '';
$results = [];
$error = '';
$query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = str_replace('#', '-- ', $input);
    $query = "SELECT user_id, user FROM users WHERE user = '$input'";

    try {
        $results = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SQL Injection Lab (Vulnerable)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background: #f8f8f8;
            color: #222;
        }
        main {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        button {
            margin-top: 1rem;
            padding: 0.6rem 1.4rem;
            border: none;
            border-radius: 6px;
            background: #2b6cb0;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #2c5282;
        }
        .note {
            background: #fefcbf;
            padding: 0.75rem;
            border-radius: 6px;
            margin-top: 1rem;
            font-size: 0.95rem;
        }
        .query {
            margin-top: 1.5rem;
            background: #edf2f7;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: "Courier New", monospace;
            font-size: 0.9rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 0.5rem;
            text-align: left;
        }
        .error {
            color: #c53030;
            margin-top: 1rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
<main>
    <h1>SQL Injection Lab</h1>
    <p>Esta app es vulnerable a SQL injection para practicar técnicas en un entorno local y controlado.</p>

    <form method="post">
        <label for="username">Usuario (vulnerable):</label>
        <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($input, ENT_QUOTES); ?>">
        <button type="submit">Buscar</button>
    </form>
    <form method="post">
        <input type="hidden" name="reset_db" value="1">
        <button type="submit">Restaurar base de datos</button>
    </form>

    <div class="note">
        <strong>Ejemplo:</strong> prueba con <code>' or '1'='1' #</code> o <code>' UNION SELECT 1,VERSION() #</code>.
    </div>

    <?php if ($query) : ?>
        <div class="query">
            <strong>Consulta ejecutada:</strong><br>
            <?php echo htmlspecialchars($query, ENT_QUOTES); ?>
        </div>
    <?php endif; ?>

    <?php if ($error) : ?>
        <div class="error">Error: <?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
    <?php endif; ?>
    <?php if ($resetMessage) : ?>
        <div class="note"><?php echo htmlspecialchars($resetMessage, ENT_QUOTES); ?></div>
    <?php endif; ?>

    <?php if ($results) : ?>
        <table>
            <thead>
                <tr>
                    <th>user_id</th>
                    <th>user</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $row['user_id'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars($row['user'], ENT_QUOTES); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
