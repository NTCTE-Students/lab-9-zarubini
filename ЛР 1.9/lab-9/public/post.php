<?php

require_once __DIR__ . '/../autoload.php';

use App\Config\Database;
use App\Middleware\Auth;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;

$auth = new Auth();
$post = new Post((int) $_GET['id']);

if (!$post -> getAttribute('id')) {
    header('Location: /');
    exit();
}

if ($auth -> check() && isset($_GET['where'])) {
    $_SESSION['errors'] = [];

    if (empty($_POST['body'])) {
        $_SESSION['errors']['body'][] = 'Тело комментария обязательно для заполнения.';
    }

    switch ($_GET['where']) {
        case 'root':
            if (empty($_SESSION['errors'])) {
                (new Comment())
                    -> createAndSet([
                        'user_id' => $auth -> user() -> getAttribute('id'),
                        'post_id' => $post -> getAttribute('id'),
                        'body' => $_POST['body'],
                    ]);
            }
        break;
        case 'answer':
            if (empty($_GET['comment_id'])) {
                $_SESSION['errors']['comment_id'][] = 'Непонятно, куда отправлять комментарий...';
            }

            if (empty($_SESSION['errors'])) {
                (new Comment())
                    -> createAndSet([
                        'user_id' => $auth -> user() -> getAttribute('id'),
                        'post_id' => $post -> getAttribute('id'),
                        'parent_id' => (int) $_GET['comment_id'],
                        'body' => $_POST['body'],
                    ]);
            }
        break;
        case 'like':
            if (empty($_GET['id'])) {
                $_SESSION['errors']['id'][] = 'НЕпонятно куда ставить лайк';
            }
            $like = new Like();
            $like -> getByWhere([
                ['likeable_type', '=', 'posts'],
                ['likeable_id', '=', $post -> getAttribute('id')],
                ['user_id', '=', $auth -> user() -> getAttribute('id')],
            ]);

            if (is_null($like -> getAttribute('id'))) {
                $like->createAndSet([
                    'user_id' => $auth ->user()-> getAttribute('id'),
                    'likeable_type' => 'posts',
                    'likeable_id' => $_GET['id'],
                ]);
                
            } else {
                $like->delete();
            }
            header("Location: /post.php?id={$_GET['id']}");
        break;
        case 'like_comment':
            if (empty($_GET['comment_id'])) {
                $_SESSION['errors']['comment_id'][] = 'НЕпонятно куда ставить лайк';
            }
            $like = new Like();
            $like -> getByWhere([
                ['likeable_type', '=', 'comments'],
                ['likeable_id', '=', $_GET['comment_id']],
                ['user_id', '=', $auth -> user() -> getAttribute('id')],
            ]);
            if (is_null($like -> getAttribute('id'))) {
                $like->createAndSet([
                    'user_id' => $auth ->user()-> getAttribute('id'),
                    'likeable_type' => 'comments',
                    'likeable_id' => $_GET['comment_id'],
                ]);
                
            } else {
                $like->delete();
            }
            header("Location: /post.php?id={$_GET['id']}");

        break;
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Блог</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="max-w-6xl xl:mx-auto md:mx-10">
    <header class="flex md:my-5 flex-col md:flex-row justify-between">
        <ul class="flex md:space-x-2 flex-col md:flex-row">
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/">Главная</a></li>
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/feed.php">Лента</a></li>
        </ul>
        <ul class="flex md:space-x-2 flex-col md:flex-row">
            <?php if ($auth -> check()) { ?>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account">Кабинет</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/logout.php">Выйти</a></li>
            <?php } else { ?>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/login.php">Войти</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/register.php">Регистрация</a></li>
            <?php } ?>
        </ul>
    </header>
    <main class="mt-10 my-8 md:m-0 mx-8">
        <h1 class="text-3xl font-bold my-16"><?php print($post -> getAttribute('heading')); ?></h1>
        <?php if ($auth -> user() ?-> getAttribute('id') === $post -> getAttribute('user_id')) { ?>
            <a class="inline-flex items-center mb-16 px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/post.php?id=<?php print($post -> getAttribute('id')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                    <path d="m15 5 4 4"/>
                </svg>
                Редактировать
            </a>
        <?php } ?>
        <a class="inline-flex items-center mb-16 px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/post.php?id=<?php print($post -> getAttribute('id')); ?>&where=like">
            <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
            </svg>
            Лайк!
        </a>
        <?php 
        $like = new Like();
        $like -> getByWhere([
             ['likeable_type', '=', 'posts'],
             ['likeable_id', '=', $post -> getAttribute('id')],
             ['user_id', '=', $auth -> user() -> getAttribute('id')],
         ]); 
        print($like -> countlike());
        ?>
        <article>
            <?php print(nl2br($post -> getAttribute('body'))); ?>
        </article>
        <div class="flex flex-col">
            <p class="mt-16 mb-2 inline-flex items-center text-gray-400 italic">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <path d="M8 2v4"/>
                    <path d="M16 2v4"/>
                    <rect width="18" height="18" x="3" y="4" rx="2"/>
                    <path d="M3 10h18"/>
                </svg>
                <?php print(date('d.m.Y', strtotime($post -> getAttribute('created_at')))); ?>
            </p>
            <p class="mb-8 inline-flex items-center text-gray-400 italic">
                <svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <path d="M11.5 15H7a4 4 0 0 0-4 4v2"/>
                    <path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/>
                    <circle cx="10" cy="7" r="4"/>
                </svg>
                <a class="hover:underline" href="/blog.php?id=<?php print($post -> getAttribute('user_id')); ?>">
                    <?php
                        $author = $post -> author();
                        print("{$author -> getAttribute('lastname')} {$author -> getAttribute('firstname')} {$author -> getAttribute('patronymic')}");
                    ?>
                </a>
            </p>
        </div>
        <section class="bg-white py-8 lg:py-16 antialiased">
            <div class="max-w-2xl mx-auto px-4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg lg:text-2xl font-bold">Комментарии</h2>
                </div>
                <?php if ($auth -> check()) { ?>
                    <form class="mb-6" method="post" action="/post.php?id=<?php print($post -> getAttribute('id')); ?>&where=root">
                        <div class="py-2 px-4 mb-4 bg-white rounded-lg rounded-t-lg border border-gray-200">
                            <label for="own_comment" class="sr-only">Ваш комментарий</label>
                            <textarea id="own_comment" name="body" required rows="6" class="px-0 w-full text-sm text-gray-900 border-0 focus:ring-0 focus:outline-none" placeholder="Опишите ваши мысли..."></textarea>
                            <?php if (isset($_SESSION['errors']['body'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['body'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                        </div>
                        <button type="submit" class="inline-flex items-center py-2.5 px-4 text-xs font-medium text-center text-white bg-cyan-700 rounded-lg focus:ring-4 focus:ring-cyan-200 hover:bg-cyan-800 cursor-pointer">
                            Прокомментировать
                        </button>
                    </form>
                <?php } else { ?>
                    <p class="text-gray-500 italic">Чтобы оставить комментарий, вам нужно авторизоваться.</p>
                <?php }
                    $dbase = new Database();
                    $comments = $dbase
                        -> read('comments', where: [['post_id', '=', $post -> getAttribute('id')], ['parent_id', 'IS', 'NULL']]);
                    foreach ($comments as $comment) {
                        $author = new User($comment['user_id']); ?>
                            <article class="p-6 text-base">
                                <section class="flex justify-between items-center mb-2">
                                    <div class="flex items-center">
                                        <p class="mr-3 text-sm text-gray-900 font-semibold"><?php print("{$author -> getAttribute('lastname')} {$author -> getAttribute('firstname')} {$author -> getAttribute('patronymic')}"); ?></p>
                                        <p class="text-sm text-gray-600"><?php print(date('d.m.Y', strtotime($comment['created_at']))); ?></p>
                                    </div>
                                </section>
                                <p class="text-gray-500"><?php print(nl2br($comment['body'])); ?></p>
                                <?php if ($auth -> check()) { ?>
                                    <div>
                                        <div class="peer flex justify-between mt-4 space-x-4">
                                            <label for="comment-<?php print($comment['id']); ?>" class="cursor-pointer flex items-center text-sm text-gray-500 hover:underline font-medium">
                                                <input type="checkbox" id="comment-<?php print($comment['id']); ?>" class="hidden">
                                                <svg class="mr-1.5 w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 18">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h5M5 8h2m6-3h2m-5 3h6m2-7H2a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h3v5l5-5h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1Z"/>
                                                </svg>
                                                Ответить
                                            </label>
                                            <a href="/post.php?id=<?php print($post -> getAttribute('id')) ?>&where=like_comment&comment_id=<?php print($comment['id']); ?>" class="cursor-pointer flex items-center text-sm text-gray-500 hover:underline font-medium">Лайк</a>
                                        </div>
                                        <form method="post" action="/post.php?id=<?php print($post -> getAttribute('id')); ?>&where=answer&comment_id=<?php print($comment['id']); ?>" class="peer-has-checked:block mt-4 hidden">
                                            <label for="answer-<?php print($comment['id']); ?>" class="sr-only">Ваш ответ</label>
                                            <div class="flex items-center px-3 py-2 rounded-lg bg-gray-50">
                                                <textarea name="body" id="answer-<?php print($comment['id']); ?>" rows="1" class="block mx-4 p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 " placeholder="Ваш ответ..."></textarea>
                                                <button type="submit" class="inline-flex justify-center p-2 text-cyan-600 rounded-full cursor-pointer hover:bg-cyan-100 ">
                                                    <svg class="w-5 h-5 rotate-90 rtl:-rotate-90" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                                        <path d="m17.914 18.594-8-18a1 1 0 0 0-1.828 0l-8 18a1 1 0 0 0 1.157 1.376L8 18.281V9a1 1 0 0 1 2 0v9.281l6.758 1.689a1 1 0 0 0 1.156-1.376Z"/>
                                                    </svg>
                                                    <span class="sr-only">Ответить</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php } ?>
                            </article>
                    <?php 
                        $inner_comments = $dbase -> read('comments', where: [['post_id', '=', $post -> getAttribute('id')], ['parent_id', '=', $comment['id']]]);
                        foreach ($inner_comments as $inner_comment) {
                            $author = new User($inner_comment['user_id']); ?>
                            <article class="p-6 mb-3 lg:ml-12 text-base">
                                <section class="flex justify-between items-center mb-2">
                                    <div class="flex items-center">
                                    <p class="mr-3 text-sm text-gray-900 font-semibold"><?php print("{$author -> getAttribute('lastname')} {$author -> getAttribute('firstname')} {$author -> getAttribute('patronymic')}"); ?></p>
                                    <p class="text-sm text-gray-600"><?php print(date('d.m.Y', strtotime($inner_comment['created_at']))); ?></p>
                                    </div>
                                </section>
                                <p class="text-gray-500"><?php print(nl2br($inner_comment['body'])); ?></p>
                                <div class="peer flex justify-end mt-4 space-x-4">
                                    <a href="/post.php?id=<?php print($post -> getAttribute('id')) ?>&where=like_comment&comment_id=<?php print($inner_comment['id']); ?>" class="cursor-pointer flex items-center text-sm text-gray-500 hover:underline font-medium">Лайк</a>
                                </div>
                            </article>
                        <?php }
                    } ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>

<?php unset($_SESSION['errors']); ?>