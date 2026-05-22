<?php
class Route {
    static function start() {
        $controller_name = 'Main';
        $action_name = 'index';

        $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

        // Remove app base path (e.g. /Site_BD_True) so routes work whether you open /Site_BD_True/ or /Site_BD_True/index.php
        if ($scriptDir !== '' && $scriptDir !== '.' && str_starts_with($uriPath, $scriptDir . '/')) {
            $uriPath = substr($uriPath, strlen($scriptDir) + 1);
        } else {
            $uriPath = ltrim($uriPath, '/');
        }

        $parts = array_values(array_filter(explode('/', $uriPath), static fn($p) => $p !== ''));
        if (!empty($parts[0])) {
            $controller_name = $parts[0];
        }
        if (!empty($parts[1])) {
            $action_name = $parts[1];
        }

        $model_name = 'model_' . $controller_name;
        $controller_class = 'controller_' . $controller_name;
        $action_method = 'action_' . $action_name;

        $root = dirname(__DIR__, 2);

        $model_file = strtolower($model_name) . '.php';
        $model_path = $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model_file;
        if (file_exists($model_path)) {
            include $model_path;
        }

        $controller_file = strtolower($controller_class) . '.php';
        $controller_path = $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller_file;
        if (file_exists($controller_path)) {
            include $controller_path;
        } else {
            self::ErrorPage404();
        }

        if (!class_exists($controller_class)) {
            self::ErrorPage404();
        }

        $controller = new $controller_class;
        if (method_exists($controller, $action_method)) {
            $controller->$action_method();
            return;
        }

        self::ErrorPage404();
    }

    static function ErrorPage404() {
        http_response_code(404);
        exit('404 Not Found');
    }
}
?>
