<?php
class Model_Auth extends Model {

    function getUserByEmail($email) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(GET_ALL_USERS);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function userExists($email) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            return $stmt->fetchColumn() !== false;
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function getUserRole($email) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(GET_USER_ROLE);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function insertNewUser($surname, $name, $patronymic, $email, $password) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(INSERT_NEW_USER);
            $stmt->execute([
                ':surname' => $surname,
                ':name' => $name,
                ':patronymic' => $patronymic,
                ':email' => $email,
                ':password' => $password
            ]);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function insertNewUR($email, $role) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(INSERT_NEW_UR);
            $stmt->execute([
                ':user' => $email,
                ':role' => $role
            ]);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
}
?>
