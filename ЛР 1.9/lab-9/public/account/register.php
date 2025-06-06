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

    if (empty($_POST['lastname'])) {
        $_SESSION['errors']['lastname'][] = 'Фамилия обязательна к заполнению';
    }
    if (empty($_POST['firstname'])) {
        $_SESSION['errors']['firstname'][] = 'Имя обязательно к заполнению';
    }
    if (empty($_POST['email'])) {
        $_SESSION['errors']['email'][] = 'Почта обязательна к заполнению';
    }
    if (empty($_POST['password'])) {
        $_SESSION['errors']['password'][] = 'Пароль обязателен к заполнению';
    }
    if (empty($_POST['password_confirmation'])) {
        $_SESSION['errors']['password_confirmation'][] = 'Подтверждение пароля обязательно к заполнению';
    }
    if ($_POST['password'] !== $_POST['password_confirmation']) {
        $_SESSION['errors']['password_confirmation'][] = 'Пароли не совпадают';
    }

    if (empty($_SESSION['errors'])) {
        $user = new User();
        if ($user -> getByEmail($_POST['email']) -> getAttribute('email')) {
            $_SESSION['errors']['email'][] = 'Пользователь с такой почтой уже существует';
        } else {
            $user -> createAndSet([
                'lastname' => $_POST['lastname'],
                'firstname' => $_POST['firstname'],
                'patronymic' => $_POST['patronymic'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
            ]);

            header('Location: /account/login.php');
            exit();
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
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/login.php">Войти</a></li>
            <li><a class="block px-4 py-2 bg-cyan-700 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/register.php">Регистрация</a></li>
        </ul>
    </header>
    <section class="flex justify-center md:mt-10">
        <div class="w-full bg-white rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                    Регистрация в <span class="animate-pulse">блогах</span>
                </h1>
                <form class="space-y-4 md:space-y-6" method="post">
                    <div>
                        <label for="lastname" class="block mb-2 text-sm font-medium text-gray-900">Фамилия</label>
                        <input value="<?php print(htmlspecialchars($_POST['lastname'] ?? '')); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="text" name="lastname" id="lastname" placeholder="Иванов" required>
                        <?php if (isset($_SESSION['errors']['lastname'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['lastname'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <div>
                        <label for="firstname" class="block mb-2 text-sm font-medium text-gray-900">Имя</label>
                        <input value="<?php print(htmlspecialchars($_POST['firstname'] ?? '')); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="text" name="firstname" id="firstname" placeholder="Иван" required>
                        <?php if (isset($_SESSION['errors']['firstname'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['firstname'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <div>
                        <label for="patronymic" class="block mb-2 text-sm font-medium text-gray-900">Отчество (при наличии)</label>
                        <input value="<?php print(htmlspecialchars($_POST['patronymic'] ?? '')); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="text" name="patronymic" id="patronymic" placeholder="Иванович">
                        <?php if (isset($_SESSION['errors']['patronymic'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['patronymic'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
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
                        <?php if (isset($_SESSION['errors']['password'])) { ?>isset
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['password'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Повторите пароль</label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="password" name="password_confirmation" id="password_confirmation" placeholder="Тс-с-с! Тут что-то хитрое... опять..." required>
                        <?php if (isset($_SESSION['errors']['password_confirmation'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['password_confirmation'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <button type="submit" class="w-full text-white bg-cyan-600 hover:bg-cyan-800 focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>

<?php unset($_SESSION['errors']); ?>