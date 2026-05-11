<?php
require_once 'queries.php';
session_start();

function redirectToHome() {
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['surname'])) { $surname = htmlentities($_POST['surname']); }
    if (isset($_POST['name'])) { $name = htmlentities($_POST['name']); }
    if (isset($_POST['patronymic'])) { $patronymic = htmlentities($_POST['patronymic']); }
    else { $patronymic = ""; }
    if (isset($_POST['email'])) { 
        $email = htmlentities($_POST['email']); 
        if (str_contains($email, "moderator") or str_contains($email, "admin")) $role = 2;
        else $role = 1;
    }
    if (isset($_POST['password'])) { $password = htmlentities($_POST['password']); }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $users = $pdo->query("SELECT email FROM users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        if ($user["email"] == $email) {
            $_SESSION['register_error'] = "Пользователь с таким email уже зарегистрирован!";
            redirectToHome();
        }
    }

    $stmt = $pdo->prepare(INSERT_NEW_USER);
    $stmt->execute([
        ':surname' => $surname,
        ':name' => $name,
        ':patronymic' => $patronymic,
        ':email' => $email,
        ':password' => $passwordHash
    ]);

    $stmt2 = $pdo->prepare(INSERT_NEW_UR);
    $stmt2->execute([
        ':user' => $email,
        ':role' => $role
    ]);

    $_SESSION['user'] = [
        'email' => $email,
        'name' => $name,
        'patronymic' => $patronymic,
        'role' => $role
    ];
    redirectToHome();

}
catch (PDOException $e) {
    $_SESSION['register_error'] = $e->getMessage();
    redirectToHome();
}
?>
