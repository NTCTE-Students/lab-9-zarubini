<?php
namespace App\Middleware;
use App\Models\User;
class Auth {
    protected ?User $user = null;
    public function __construct() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            $this -> user = new User($_SESSION['user_id']);
        }
    }
    public function login(string $email, string $password) {
        $user = (new User()) -> getByEmail($email);
        if ($user && password_verify($password, $user-> getAttribute('password_hash'))) {
            $_SESSION['user_id'] = $user -> getAttribute('id');
            $this -> user = $user;
            return true;
        }
        return false;
    }
    public function check() : bool {
        return $this -> user !== null;
    }
    public function user() : ?User {
        return $this -> user;
    }
    public function logout() : void {
        unset($_SESSION['user_id']);
        $this -> user = null;
    }
}