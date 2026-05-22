<?php
class Controller_Admin extends Controller {
    function __construct()
    {
        parent::__construct();
        $this->model = new Model_Admin();
    }

    private function postValue($key, $default = '') {
        return isset($_POST[$key]) ? htmlentities($_POST[$key]) : $default;
    }

    private function redirectToTarget($target) {
        if ($target === '') {
            $this->redirectTo('');
        }
        if (preg_match('#^https?://#i', $target) || str_starts_with($target, '/')) {
            header('Location: ' . $target);
            exit;
        }
        $this->redirectTo($target);
    }

    private function resolveRedirect($defaultTarget) {
        $target = $this->postValue('redirect', $defaultTarget);
        $path = parse_url($target, PHP_URL_PATH) ?? '';

        $homePath = parse_url($this->buildUrl(''), PHP_URL_PATH) ?? '';
        $formPath = parse_url($this->buildUrl('admin/form'), PHP_URL_PATH) ?? '';

        if ($path === $homePath || $path === rtrim($homePath, '/')) {
            return $target;
        }
        if ($path === $formPath) {
            return $target;
        }

        return $defaultTarget;
    }

    private function saveImage($fieldName, $currentImage = '') {
        if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
            return $currentImage;
        }

        $fileName = basename($_FILES[$fieldName]['name']);
        $uploadDir = 'Resources/';
        $uploadPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadPath);

        return $uploadPath;
    }

    private function ensureAdmin() {
        $user = $_SESSION['user'] ?? null;
        if (!$user || (int)($user['role'] ?? 0) !== 2) {
            $this->setFlash('Недостаточно прав', 'error');
            $this->redirectTo('');
        }
        return $user;
    }

    function action_index() {
        $this->ensureAdmin();

        $method = $this->postValue('method', 'add');
        $homeUrl = $this->buildUrl('');
        $formUrl = $this->buildUrl('admin/form');

        if ($method === 'add') {
            $category = $this->postValue('category');
            $title = $this->postValue('title');
            $description = $this->postValue('description');
            $author = $this->postValue('author');
            $price = (int)$this->postValue('price');
            $stock = (int)$this->postValue('stock', '0');
            if ($stock < 0) { $stock = 0; }
            $uploadPath = $this->saveImage('image');

            $this->model->addProduct($title, $description, $author, $price, $category, $uploadPath, $stock);
            $this->setFlash('Товар успешно добавлен!');
            $this->redirectToTarget($this->resolveRedirect($homeUrl));
        }

        if ($method === 'update') {
            $id = (int)$this->postValue('id');
            $category = $this->postValue('category');
            $title = $this->postValue('title');
            $description = $this->postValue('description');
            $author = $this->postValue('author');
            $price = (int)$this->postValue('price');
            $stock = (int)$this->postValue('stock', '0');
            if ($stock < 0) { $stock = 0; }
            $currentImage = $this->postValue('current_image');
            $uploadPath = $this->saveImage('image', $currentImage);

            $this->model->updateProduct($id, $title, $description, $author, $price, $category, $uploadPath, $stock);
            $this->setFlash('Товар успешно изменён!');
            $this->redirectToTarget($this->resolveRedirect($homeUrl));
        }

        if ($method === 'delete') {
            $id = (int)$this->postValue('id');
            $this->model->deleteProduct($id);
            $this->setFlash('Товар успешно удалён!');
            $this->redirectToTarget($this->resolveRedirect($homeUrl));
        }

        $this->setFlash('Неизвестный метод', 'error');
        $this->redirectToTarget($this->resolveRedirect($formUrl));
    }

    function action_form() {
        $this->ensureAdmin();

        $categories = $this->model->getCategories();
        $isEdit = isset($_GET['id']) && $_GET['id'] !== '';
        $product = null;

        if ($isEdit) {
            $id = (int)$_GET['id'];
            $product = $this->model->getProductById($id);
            if (!$product) {
                $this->setFlash('Товар не найден', 'error');
                $this->redirectTo('');
            }
        }

        $data = [
            'categories' => $categories,
            'isEdit' => $isEdit,
            'product' => $product,
            'adminActionUrl' => $this->buildUrl('admin'),
            'homeUrl' => $this->buildUrl(''),
            'baseUrl' => $this->getBaseUrl()
        ];

        $this->view->generate('pages/admin_form.php', $data);
    }
}
?>
