<?php
class Model {
    function get_connection() {
        $root = dirname(__DIR__, 2);
        require_once $root . DIRECTORY_SEPARATOR . 'queries.php';

        $pdo = new PDO('pgsql:host=localhost;dbname=dbtest;connect_timeout=2', 'postgres', '56914720', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }
}
?>
