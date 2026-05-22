<?php
class Controller_Main extends Controller {
    function __construct()
    {
        parent::__construct();
        $root = dirname(__DIR__, 2);
        require_once $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'model_products.php';
        $this->model = new Model_Products();
    }

    private function calculateCartTotal($items) {
        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item['price'] * (int)$item['quantity'];
        }
        return $total;
    }

    private function buildCurrentBaseUrl($homeUrl, $page) {
        if ($page === 'about') {
            return $homeUrl . '?page=about';
        }
        return $homeUrl;
    }

    function action_index() {
        $page = isset($_GET['page']) ? (string)$_GET['page'] : '';
        $modal = isset($_GET['modal']) ? (string)$_GET['modal'] : '';

        $homeUrl = $this->buildUrl('');
        $currentBaseUrl = $this->buildCurrentBaseUrl($homeUrl, $page);

        $authLoginUrl = $this->buildUrl('auth/login');
        $authRegisterUrl = $this->buildUrl('auth/register');
        $authLogoutUrl = $this->buildUrl('auth/logout');
        $cartEndpoint = $this->buildUrl('cart');
        $cartViewUrl = $this->buildUrl('cart/view');
        $adminActionUrl = $this->buildUrl('admin');
        $adminFormUrl = $this->buildUrl('admin/form');

        $layoutData = $this->getLayoutData();
        $currentUser = $layoutData['currentUser'] ?? null;
        $cartItems = $layoutData['cartItems'] ?? [];
        $cartTotal = $this->calculateCartTotal($cartItems);

        $products = $this->model->getAllProducts();
        $categories = $this->model->getCategories();

        $sectionIds = [
            1 => 'paintings',
            2 => 'ceramics',
            3 => 'jewelry',
            4 => 'textiles',
            5 => 'gifts'
        ];

        $managerCategories = [];
        $editProduct = null;

        if ($currentUser && (int)($currentUser['role'] ?? 0) === 2) {
            $root = dirname(__DIR__, 2);
            require_once $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'model_admin.php';
            $adminModel = new Model_Admin();
            $managerCategories = $adminModel->getCategories();
            if ($modal === 'edit-product' && isset($_GET['id'])) {
                $editProduct = $adminModel->getProductById((int)$_GET['id']);
            }
        }

        $showLoginModal = ($modal === 'login');
        $showRegisterModal = ($modal === 'register');
        $showAddProductModal = ($modal === 'add-product');
        $showEditProductModal = ($modal === 'edit-product');

        $overlayVisible = $showLoginModal || $showRegisterModal || $showAddProductModal || $showEditProductModal;
        $bodyOverflow = $overlayVisible ? 'hidden' : 'auto';

        $profileText = 'Профиль';
        if (is_array($currentUser)) {
            $profileText = trim(($currentUser['name'] ?? '') . ' ' . ($currentUser['patronymic'] ?? ''));
            if ($profileText === '') {
                $profileText = 'Профиль';
            }
        }

        $data = array_merge($layoutData, [
            'homeUrl' => $homeUrl,
            'currentBaseUrl' => $currentBaseUrl,
            'cartViewUrl' => $cartViewUrl,
            'cartEndpoint' => $cartEndpoint,
            'authLoginUrl' => $authLoginUrl,
            'authRegisterUrl' => $authRegisterUrl,
            'authLogoutUrl' => $authLogoutUrl,
            'adminActionUrl' => $adminActionUrl,
            'adminFormUrl' => $adminFormUrl,
            'page' => $page,
            'products' => $products,
            'categories' => $categories,
            'sectionIds' => $sectionIds,
            'managerCategories' => $managerCategories,
            'editProduct' => $editProduct,
            'showLoginModal' => $showLoginModal,
            'showRegisterModal' => $showRegisterModal,
            'showAddProductModal' => $showAddProductModal,
            'showEditProductModal' => $showEditProductModal,
            'overlayVisible' => $overlayVisible,
            'bodyOverflow' => $bodyOverflow,
            'profileText' => $profileText,
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal
        ]);

        $this->view->generate('layout/header.php', $data);
        $this->view->generate('main/home.php', $data);
        $this->view->generate('layout/footer.php', $data);
    }
}
?>
