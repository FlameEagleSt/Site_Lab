<?php
header('Content-Type: application/json');
function register() {
    if (isset($_POST['surname'])) { $surname = htmlentities($_POST['surname']); }
    if (isset($_POST['name'])) { $name = htmlentities($_POST['name']); }
    if (isset($_POST['patronymic'])) { $patronymic = htmlentities($_POST['patronymic']); }
    else { $patronymic = ""; }
    if (isset($_POST['email'])) { $email = htmlentities($_POST['email']); }
    if (isset($_POST['password'])) { $password = htmlentities($_POST['password']); }
    $file = fopen("users.txt", "a");
    fwrite($file, "\n$surname|$name|$patronymic|$email|$password");
    fclose($file);
    return;
}
register();
header ('Location: index.html');
?>