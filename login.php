<?php
session_start();
require_once 'queries.php';

function redirectToHome() {
    header('Location: index.php');
    exit();
}
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['email'])) { $email = htmlentities($_POST['email']); }
    if (isset($_POST['password'])) { $password = htmlentities($_POST['password']); }

    $stmt = $pdo->prepare(GET_ALL_USERS);
    $stmt->execute([
        ':email' => $email
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['login_error'] = "Пользователь с таким email не зарегестрирован!";
        redirectToHome();
    }

    $stmt = $pdo->prepare(GET_USER_ROLE);
    $stmt->execute([
        ':email' => $email
    ]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($password, $user['password']) || $user['password'] == $password) {
        $name = $user['name'] . " " . $user['patronymic'];
        $_SESSION['login_success'] = "Добро пожаловать, " . $name;
        $_SESSION['user'] = [
            'email' => $email,
            'name' => $user['name'],
            'patronymic' => $user['patronymic'],
            'role' => $role['role_id'] ? $role['role_id'] : null
        ];
        redirectToHome();
    }
    else {
        $_SESSION['login_error'] = "Неверный пароль!";
        redirectToHome();
    }

}
catch (PDOException $e) {
    $_SESSION['login_error'] = $e->getMessage();
    redirectToHome();
}
?>
