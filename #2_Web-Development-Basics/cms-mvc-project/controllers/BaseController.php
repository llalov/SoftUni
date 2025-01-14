<?php

abstract class BaseController {

    protected $controllerName;
    protected $layoutName = DEFAULT_LAYOUT;
    protected $isViewRendered = false;
    protected $isPost = false;
    protected $isLoggedIn;
    protected $userRole;


    function __construct($controllerName) {
        $this->controllerName = $controllerName;
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->isPost = true;
        }

        if(isset ($_SESSION['username'])) {
            $this->isLoggedIn = true;
        }
        if(isset ($_SESSION['userRole']) ) {
            if ($_SESSION['userRole'] == 'admin') {
                $this->userRole = 'admin';
            }
        }
        $this->onInit();
    }

    public function onInit() {
        //Implement initializing logic in the subclasses
    }

    public function index() {
        //Implement the default action in the subclasses
    }

    public function renderView($viewName = "Index", $includeLayout = true) {
        if(! $this->isViewRendered) {

            $viewFileName = 'views/' . $this->controllerName . '/' . $viewName . '.php';

            if ($includeLayout) {
                $headerFile = 'views/layouts/'. $this->layoutName. '/header.php';
                include_once($headerFile);
            }
            include_once($viewFileName);

            if ($includeLayout) {
                $footerFile = 'views/layouts/'. $this->layoutName.'/footer.php';
                include_once($footerFile);
            }
            $this->isViewRendered = true;
        }
    }

    public function redirectToUrl($url) {
        header("Location: ".$url);
        die;
    }
    public function redirect($controllerName, $actionName = null, $params = null) {
        $url = '/'.urlencode($controllerName);
        if($actionName != null) {
            $url .= '/'.urlencode($actionName);
        }
        if ($params != null) {
            $encodedParams = array_map($params, 'urlencode');
            $url .= implode('/',$encodedParams );
        }
        $this->redirectToUrl($url);
    }


    public function authorize() {
        if (! $this->isLoggedIn) {
            $this->addErrorMessage("Please login first");
            $this->redirect("account", "login");
        }
    }

    public function isAdmin() {
        if (! $this->isLoggedIn) {
            $this->addErrorMessage("Please login first");
            $this->redirect("account", "login");
        }
        if ( $this->userRole != 'admin') {
            $this->addErrorMessage("You don't have permissions");
            $this->redirect("torrents", "index");
        }
    }

    public function isEditor() {
        if (! $this->isLoggedIn) {
            $this->addErrorMessage("Please login first");
            $this->redirect("account", "login");
        }
        if ( $this->userRole != 'editor') {
            $this->addErrorMessage("You don't have permissions");
            $this->redirect("torrents", "index");
        }
    }

    function addMessage($msg, $type) {
        if (!isset($_SESSION['messages'])) {
            $_SESSION['messages'] = array();
        };
        array_push($_SESSION['messages'], array('text' => $msg, 'type' => $type));
    }

    function addInfoMessage($msg) {
        $this->addMessage($msg, 'alert alert-success');
    }

    function addErrorMessage($msg) {
        $this->addMessage($msg, 'alert alert-danger');
    }


}