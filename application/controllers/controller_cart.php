<?php
class Controller_Cart extends Controller {
    function __construct()
    {
        parent::__construct();
        $this->model = new Model_Cart();
    }

    private function postValue($key, $default = '') {
        return isset($_POST[$key]) ? htmlentities($_POST[$key]) : $default;
    }

    private function currentUserEmail() {
        return $_SESSION['user']['email'] ?? '';
    }

    private function resolveRedirect($defaultTarget) {
        $target = $this->postValue('redirect', $defaultTarget);
        $path = parse_url($target, PHP_URL_PATH) ?? '';

        $homePath = parse_url($this->buildUrl(''), PHP_URL_PATH) ?? '';
        $cartPath = parse_url($this->buildUrl('cart/view'), PHP_URL_PATH) ?? '';

        if ($path === $homePath || $path === rtrim($homePath, '/')) {
            return $target;
        }
        if ($path === $cartPath) {
            return $target;
        }

        return $defaultTarget;
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

    private function calculateCartTotal($items) {
        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item['price'] * (int)$item['quantity'];
        }
        return $total;
    }

    function action_index() {
        $email = $this->currentUserEmail();
        if ($email === '') {
            $this->setFlash('Сначала войдите в аккаунт', 'error');
            $this->redirectTo('?modal=login');
        }

        $method = $this->postValue('method');
        $id = (int)$this->postValue('id');

        $homeUrl = $this->buildUrl('');
        $cartViewUrl = $this->buildUrl('cart/view');

        if ($method === 'insert') {
            $result = $this->model->addToCart($email, $id);
            if (!$result['success']) {
                if ($result['reason'] === 'out_of_stock') {
                    $this->setFlash('Товара нет в наличии', 'error');
                    $this->redirectToTarget($this->resolveRedirect($homeUrl));
                }
                if ($result['reason'] === 'limit') {
                    $this->setFlash('Нельзя добавить больше: недостаточно товара на складе', 'error');
                    $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
                }
            }

            $this->setFlash('Товар добавлен в корзину');
            $this->redirectToTarget($this->resolveRedirect($homeUrl));
        }

        if ($method === 'update') {
            $result = $this->model->increaseQuantity($email, $id);
            if (!$result['success']) {
                if ($result['reason'] === 'out_of_stock') {
                    $this->setFlash('Товара нет в наличии', 'error');
                }
                if ($result['reason'] === 'limit') {
                    $this->setFlash('Нельзя добавить больше: недостаточно товара на складе', 'error');
                }
            }
            $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
        }

        if ($method === 'decrease') {
            $this->model->decreaseQuantity($email, $id);
            $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
        }

        if ($method === 'remove') {
            $this->model->removeProduct($email, $id);
            $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
        }

        if ($method === 'clear') {
            $this->model->clearCart($email);
            $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
        }

        if ($method === 'checkout') {
            $result = $this->model->checkout($email);
            if (!$result['success']) {
                if ($result['reason'] === 'empty') {
                    $this->setFlash('Корзина пуста', 'error');
                } else if ($result['reason'] === 'insufficient') {
                    $this->setFlash('Недостаточно товара на складе для оформления заказа', 'error');
                }
                $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
            }

            $this->setFlash('Заказ оформлен! Спасибо за покупку');
            $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
        }

        $this->setFlash('Неизвестный метод', 'error');
        $this->redirectToTarget($this->resolveRedirect($cartViewUrl));
    }

    function action_view() {
        $email = $this->currentUserEmail();
        if ($email === '') {
            $this->setFlash('Сначала войдите в аккаунт', 'error');
            $this->redirectTo('?modal=login');
        }

        $cartItems = $this->model->getUserCart($email);
        $cartTotal = $this->calculateCartTotal($cartItems);

        $layoutData = $this->getLayoutData();
        $data = array_merge($layoutData, [
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal,
            'homeUrl' => $this->buildUrl(''),
            'cartEndpoint' => $this->buildUrl('cart'),
            'cartViewUrl' => $this->buildUrl('cart/view')
        ]);

        $this->view->generate('pages/cart.php', $data);
    }
}
?>
