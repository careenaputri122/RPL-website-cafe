<?php
require_once 'models/MenuModel.php';
require_once 'models/TestimoniModel.php';

class HomeController {
    private $menuModel;
    private $testimoniModel;

    public function __construct() {
        $this->menuModel    = new MenuModel();
        $this->testimoniModel = new TestimoniModel();
    }

    public function index() {
        $menu_populer  = $this->menuModel->getPopuler(6);
        $testimoni     = $this->testimoniModel->getAll();
        $current_page  = 'home';

        require_once 'views/layouts/header.php';
        require_once 'views/pages/home.php';
        require_once 'views/layouts/footer.php';
    }
}
