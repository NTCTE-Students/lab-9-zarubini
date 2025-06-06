<?php

require_once __DIR__ . '/../../autoload.php';

use App\Middleware\Auth;

(new Auth())
    -> logout();

header('Location: /');