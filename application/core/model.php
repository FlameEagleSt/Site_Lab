<?php
class Model {
    function get_connection() {
        require_once 'queries.php';
        $pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
?>