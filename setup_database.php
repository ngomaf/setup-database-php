<?php
/**
 * setup_fcaujes.php
 * Cria o banco fcaujes, cria as tabelas e popula os dados
 */

$host = 'localhost';
$db   = 'db_name';
$user = 'user_name';
$pass = 'password';
$charset = 'utf8';

$sqlFile = __DIR__ . '/database_file.sql';

if (!file_exists($sqlFile)) {
    die("❌ Arquivo SQL não encontrado.\n");
}

try {
    // 1. Conecta ao servidor (sem banco)
    $pdo = new PDO(
        "mysql:host=$host;charset=$charset",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
        ]
    );

    echo "✅ Conectado ao servidor MariaDB.\n";

    // 2. Cria o banco
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `$db`
        CHARACTER SET utf8
        COLLATE utf8_general_ci
    ");

    echo "✅ Banco de dados '$db' verificado/criado.\n";

    // 3. Seleciona o banco
    $pdo->exec("USE `$db`");

    // 4. Lê o SQL
    $sql = file_get_contents($sqlFile);
    if (!$sql) {
        throw new Exception("Falha ao ler o arquivo SQL.");
    }

    // 5. Executa tudo em transação
    $pdo->beginTransaction();
    $pdo->exec($sql);
    $pdo->commit();

    echo "🎉 Estrutura e dados importados com sucesso!\n";

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("❌ Erro: " . $e->getMessage() . "\n");
}
