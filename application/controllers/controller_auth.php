<?php
class Controller_Auth extends Controller {
    function __construct()
    {
        parent::__construct();
        $this->model = new Model_Auth();
    }

    private function postValue($key, $default = '') {
        return isset($_POST[$key]) ? htmlentities($_POST[$key]) : $default;
    }

    function action_login() {
        $email = $this->postValue('email');
        $password = $this->postValue('password');

        if ($email === '' || $password === '') {
            $this->setFlash('Введите почту и пароль', 'error');
            $this->redirectTo('?modal=login');
        }

        $user = $this->model->getUserByEmail($email);
        if (!$user) {
            $this->setFlash('Пользователь с таким email не зарегистрирован!', 'error');
            $this->redirectTo('?modal=login');
        }

        $role = $this->model->getUserRole($email);
        $storedPassword = $user['password'] ?? '';
        if (password_verify($password, $storedPassword) || $storedPassword === $password) {
            $name = trim(($user['name'] ?? '') . ' ' . ($user['patronymic'] ?? ''));
            $this->setFlash('Добро пожаловать, ' . $name);
            $_SESSION['user'] = [
                'email' => $email,
                'name' => $user['name'] ?? '',
                'patronymic' => $user['patronymic'] ?? '',
                'role' => $role['role_id'] ?? null
            ];
            $this->redirectTo('');
        }

        $this->setFlash('Неверный пароль!', 'error');
        $this->redirectTo('?modal=login');
    }

    function action_register() {
        $surname = $this->postValue('surname');
        $name = $this->postValue('name');
        $patronymic = $this->postValue('patronymic', '');
        $email = $this->postValue('email');
        $password = $this->postValue('password');

        if ($email === '') {
            $this->setFlash('Введите почту', 'error');
            $this->redirectTo('?modal=register');
        }

        $role = (str_contains($email, 'moderator') || str_contains($email, 'admin')) ? 2 : 1;

        if ($this->model->userExists($email)) {
            $this->setFlash('Пользователь с таким email уже зарегистрирован!', 'error');
            $this->redirectTo('?modal=register');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $this->model->insertNewUser($surname, $name, $patronymic, $email, $passwordHash);
        $this->model->insertNewUR($email, $role);

        $_SESSION['user'] = [
            'email' => $email,
            'name' => $name,
            'patronymic' => $patronymic,
            'role' => $role
        ];

        $this->setFlash('Пользователь успешно создан!');
        $this->redirectTo('');
    }

    function action_logout() {
        unset($_SESSION['user']);
        $this->redirectTo('');
    }
}
?>
