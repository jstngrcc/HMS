<?php

class PagesController
{
    public function privacy()
    {
        require_once '../app/views/static/privacy.view.php';
    }

    public function terms()
    {
        require_once '../app/views/static/terms.view.php';
    }
    public function home()
    {
        $logged_in = $this->getAuthState();

        require_once '../app/views/home.view.php';
    }

    public function search()
    {
        require_once '../app/views/rooms/search.view.php';
    }

    public function standard()
    {
        require_once '../app/views/rooms/standard.view.php';
    }

    public function deluxe()
    {
        require_once '../app/views/rooms/deluxe.view.php';
    }

    public function suite()
    {
        require_once '../app/views/rooms/suite.view.php';
    }

    public function notFound()
    {
        $logged_in = $this->getAuthState();
        http_response_code(404);
        require_once '../app/views/static/404.view.php';
    }

    public function getAuthState()
    {
        return isset($_SESSION['logged_in_user_id']);
    }
}