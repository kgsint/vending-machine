<?php

namespace App\Controllers;

use App\Models\User;
use Core\View;
use Core\Database;

class AuthController
{
    public function loginView()
    {
        return View::make("auth/login");
    }

    public function login()
    {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $db = Database::getInstance()->getConnection();

        $user = new User($db)->findByEmail($email);

        if (!$user) {
            dd("there is no mail with such user");
        }

        if (!password_verify($password, $user["password"])) {
            dd("invalid password");
        }

        $_SESSION["user"] = [
            "id" => $user["id"],
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
