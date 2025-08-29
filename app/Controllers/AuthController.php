<?php

namespace App\Controllers;

use App\FormRequests\LoginFormRequest;
use App\Models\User;
use Core\View;
use Core\Database;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function loginView()
    {
        return View::make("auth/login");
    }

    public function login()
    {
        $email = $_POST["email"];
        $password = $_POST["password"];
        $user = new User($this->db)->findByEmail($email);

        $request = LoginFormRequest::validate($_POST);

        if (!$user) {
            $request->setError("email", "Credentials do not match")->throw();
        }

        if (!password_verify($password, $user["password"])) {
            $request->setError("email", "Credentials do not match")->throw();
        }

        $_SESSION["user"] = [
            "id" => $user["id"],
            "username" => $user["username"],
            "email" => $user["email"],
            "role" => $user["role"]
        ];

        return redirect("/");
    }

    public function logout()
    {
        $_SESSION = [];
        unset($_SESSION["user"]);
        // remove browser cookies
        $params = session_get_cookie_params();
        setcookie(
            "PHPSESSID",
            "",
            time() - 1,
            $params["path"],
            $params["domain"],
        );

        return redirect("/login");
    }
}
