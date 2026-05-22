<?php
class Controller {
    public $model;
    public $view;
    
    function __construct()
    {
        $this->view = new View();
    }

    protected function getBaseUrl() {
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($scriptDir === '' || $scriptDir === '.') {
            return '';
        }
        return $scriptDir;
    }

    protected function buildUrl($path = '') {
        $baseUrl = $this->getBaseUrl();
        if ($path === '') {
            return $baseUrl === '' ? '/' : $baseUrl . '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return $baseUrl . $path;
    }

    protected function redirectTo($path = '') {
        header('Location: ' . $this->buildUrl($path));
        exit;
    }

    protected function setFlash($message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    protected function pullFlash() {
        $message = $_SESSION['flash_message'] ?? '';
        $type = $_SESSION['flash_type'] ?? '';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return [
            'flashMessage' => $message,
            'flashType' => $type
        ];
    }

    protected function getLayoutData() {
        $currentUser = $_SESSION['user'] ?? null;
        $cartItems = [];
        if (is_array($currentUser) && !empty($currentUser['email'])) {
            $root = dirname(__DIR__, 2);
            require_once $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'model_cart.php';
            $cartModel = new Model_Cart();
            $cartItems = $cartModel->getUserCart($currentUser['email']) ?? [];
        }

        return array_merge([
            'currentUser' => $currentUser,
            'cartItems' => $cartItems,
            'baseUrl' => $this->getBaseUrl()
        ], $this->pullFlash());
    }
}
?>
