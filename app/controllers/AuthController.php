<?php
require_once '../app/models/User.php';

class AuthController
{
    public function loginForm()
    {
        require_once '../app/views/auth/auth.view.php';
    }

    public function signupForm()
    {
        require_once '../app/views/auth/auth.view.php';
    }

    public function forgotPasswordForm()
    {
        require_once '../app/views/auth/forgot_password.view.php';
    }

    public function showResetForm($token = null)
    {
        if (!$token) {
            echo "Invalid or missing token.";
            return;
        }
        // You could also check if token exists in DB and is not expired here

        require_once '../app/views/auth/reset_password.view.php';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            if (empty($email) || empty($password)) {
                echo "Please enter both email and password.";
                return;
            }

            $userModel = new User($GLOBALS['conn']);
            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user->PasswordHash)) {
                $_SESSION['logged_in_user_id'] = $user->UserID;
                $_SESSION['logged_in_user_name'] = $user->FirstName;

                header('Location: /home');
                exit;
            } else {
                echo "Invalid email or password.";
            }
        }
    }

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fname = trim($_POST['fname']);
            $lname = trim($_POST['lname']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $passwordr = trim($_POST['passwordr']);

            if (empty($fname) || empty($lname) || empty($email) || empty($password) || empty($passwordr)) {
                echo "Please fill in all fields.";
                return;
            }

            if ($password !== $passwordr) {
                echo "Passwords do not match.";
                return;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $userModel = new User($GLOBALS['conn']);

            try {
                $userModel->createGuestUser(
                    $email,
                    $email,
                    $hash,
                    $fname,
                    $lname,
                    $phone
                );

                $_POST['email'] = $email;
                $_POST['password'] = $password;
                $this->login();

                header('Location: /home');
                exit;

            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function logout()
    {
        session_destroy();
        header('Location: /home');
        exit;
    }


    public function resetPasswordForm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];

            // Check if user exists
            $userModel = new User($GLOBALS['conn']);
            $user = $userModel->getUserByEmail($email);

            if ($user) {
                $token = $userModel->createPasswordResetToken($user->UserID);

                $userModel->sendPasswordResetEmail($user->Email, $token);

                header('Location: /login');
                exit;
            } else {
                echo "Email not found in our system.";
            }
        }
    }
    public function sendResetLinkk()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];

            $userModel = new User($GLOBALS['conn']);
            $user = $userModel->getUserByEmail($email);

            if ($user) {
                $token = $userModel->createPasswordResetToken($user['UserID']);
                if ($userModel->sendPasswordResetEmail($user['Email'], $token)) {
                    echo "Recovery link sent!";
                } else {
                    echo "Failed to send email.";
                }
            } else {
                echo "Email not found in our system.";
            }
        }
    }
    public function resetPassword()
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            echo "Missing token.";
            return;
        }

        $userModel = new User($GLOBALS['conn']);
        $user = $userModel->getUserByResetToken($token);

        if (!$user) {
            echo "Invalid or expired token.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = trim($_POST['password']);

            // password validation
            if (
                strlen($newPassword) < 8
                || !preg_match('/[A-Z]/', $newPassword)
                || !preg_match('/[a-z]/', $newPassword)
                || !preg_match('/[0-9]/', $newPassword)
                || !preg_match('/[\W]/', $newPassword)
            ) {
                echo "Password must be at least 8 chars, include upper/lowercase, number, and special char.";
                return;
            }

            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $userModel->updatePasswordByID($user->UserID, $hash);
            $userModel->deleteResetToken($token); // invalidate token

            echo "Password updated successfully!";
            header('Location: /login');
            exit;
        }
    }
}

?>