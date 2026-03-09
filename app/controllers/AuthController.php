<?php
require_once '../app/models/User.php';

class AuthController {
    public function loginForm() {
        require_once '../app/views/auth/login.view.html';
    }

    public function signupForm() {
        require_once '../app/views/auth/signup.view.html';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            $userModel = new User($GLOBALS['conn']);
            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user->PasswordHash)) {
                $_SESSION['user_id'] = $user->UserID;
                $_SESSION['role'] = $user->RoleName;
                header('Location: /home');
                exit;
            } else {
                echo "Invalid email or password.";
            }
        }
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fname = trim($_POST['fname']);
            $lname = trim($_POST['lname']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            $passwordr = password_hash(trim($_POST['passwordr']), PASSWORD_DEFAULT);
            // TODO: Verify all the fields 

            $userModel = new User($GLOBALS['conn']);
            $userModel->createUser($email, $password);

            $userModel->createGuest($userModel->getUserByEmail($email)->UserID, $fname, $lname, $phone);

            header('Location: /login');
            exit;
        }
    }
}

?>