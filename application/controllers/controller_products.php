<?php
class Controller_Products extends Controller {
    function __construct()
    {
        parent::__construct();
        $this->model = new Model_Products();
    }

    function action_index() {
        $this->redirectTo('');
    }
}
?>
