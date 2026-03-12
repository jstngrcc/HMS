<?php

class PagesController {
    public function privacy() {
        require_once '../app/views/static/privacy.view.html';
    }

    public function terms() {
        require_once '../app/views/static/terms.view.html';
    }
    public function home() {
        session_start();
    
        $userID = $_SESSION['user_id'] ?? null;
    
        require_once '../app/views/home.view.php';
    }
}
?>