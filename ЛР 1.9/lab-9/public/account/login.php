<?php

require_once __DIR__ . '/../../autoload.php';

use App\Middleware\Auth;
use App\Models\User;

$auth = new Auth();

if ($auth -> check()) {
    header('Location: /');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['errors'] = [];

    if (empty($_POST['email'])) {
        $_SESSION['errors']['email'][] = 'Почта обязательна к заполнению';
    }
    if (empty($_POST['password'])) {
        $_SESSION['errors']['password'][] = 'Пароль обязателен к заполнению';
    }

    if (empty($_SESSION['errors'])) {
        if ($auth -> login($_POST['email'], $_POST['password'])) {
            header('Location: /');
            exit();
        } else {
            $_SESSION['errors']['email'][] = 'Не удалось войти. Проверьте почту и пароль';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Блог</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
</head>
<body class="max-w-6xl xl:mx-auto md:mx-10">
    <header class="flex md:my-5 flex-col md:flex-row justify-between">
        <ul class="flex md:space-x-2 flex-col md:flex-row">
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/">Главная</a></li>
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/feed.php">Лента</a></li>
        </ul>
        <ul class="flex md:space-x-2 flex-col md:flex-row">
            <li><a class="block px-4 py-2 bg-cyan-700 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/login.php">Войти</a></li>
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/register.php">Регистрация</a></li>
        </ul>
    </header>
    <section class="flex justify-center md:mt-10">
        <div class="w-full bg-white rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                    Войти в <span class="animate-pulse">блоги</span>
                </h1>
                <form class="space-y-4 md:space-y-6" method="post">
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Почта</label>
                        <input value="<?php print(htmlspecialchars($_POST['email'] ?? '')); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="email" name="email" id="email" placeholder="ivan@ivanov.com" required>
                        <?php if (isset($_SESSION['errors']['email'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['email'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Пароль</label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="password" name="password" id="password" placeholder="Тс-с-с! Тут что-то хитрое..." required>
                        <?php if (isset($_SESSION['errors']['password'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['password'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <button type="submit" class="w-full text-white bg-cyan-600 hover:bg-cyan-800 focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer">Войти</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>

<?php unset($_SESSION['errors']); ?>