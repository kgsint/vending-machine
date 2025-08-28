<?php

namespace App\Models;

use DateTime;

class User
{
    private $db;
    private $table = "users";

    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $role;
    public DateTime $created_at;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all($order_by = "created_at", $sort_by = "DESC")
    {
        $query = "SELECT * FROM ${$this->table} ORDER BY ${$order_by} ${$sort_by}";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByEmail(string $email)
    {
        $query = "SELECT * FROM users WHERE email = :email";

        $stmt = $this->db->prepare($query);
        $stmt->execute(["email" => $email]);

        return $stmt->fetch();
    }
}
