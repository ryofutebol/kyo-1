<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
session_start();
$dsn = "mysql:host=localhost; dbname=kyoichi; charset=utf8";
date_default_timezone_set('Asia/Tokyo');
try {
        $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
        echo $e->getMessage();
        exit;
}
$login_user_id = $_SESSION['user_id'];
$csql = "SELECT time_check FROM posts INNER JOIN users ON posts.user_id = users.user_id WHERE posts.user_id = $login_user_id ORDER BY id DESC LIMIT 1";
$timestamp = $dbh->prepare($csql);
$timestamp->execute();
$result = $timestamp->fetch();
if (isset($login_user_id)) {//ログインしているとき
        $user_id = $_SESSION['user_id'];
        if (isset($_POST['title']) && isset($_POST['content'])) {
                $title = $_POST['title'];
                $content = $_POST['content'];
                $created_at = date('Y-m-d');
                $time_check = time();
                $sql = "INSERT INTO posts(user_id, title, content, created_at, time_check) VALUES ('$user_id', '$title', '$content', '$created_at', '$time_check')";
                $stmt = $dbh->prepare($sql);
                if (intval($result['time_check'])+86400 <= time()) {
                        $stmt->execute();
                        $msg = '投稿しました';
                } else {
                        $msg = '24時間に1回しか投稿できません';
                }
        }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Kyoichi</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
<header>
        <nav class="navbar navbar-expand-sm navbar-light bg-light">
                <a class="navbar-brand ml-3" href="index.php"><h1>Kyoichi</h1></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <li class="nav-item mr-4">
                                        <h4><?php echo $_SESSION['name']; ?></h4>
                                </li>
                                <li class="nav-item">
                                        <a href="logout.php" class="btn btn-outline-danger">ログアウト</a>
                                </li>
                                <?php else: ?>
                                <li class="nav-item mr-3">
                                        <a href="login.php" class="btn btn-outline-danger">ログイン</a>
                                </li>
                                <li class="nav-item">
                                        <a href="signup.php" class="btn btn-outline-danger">サインアップ</a>
                                </li>
                                <?php endif; ?>
                        </ul>
                </div>
        </nav>
</header>
<div class="container mt-5">
        <div class="jumbotron">
                <?php if (isset($_POST['add'])): ?>
                        <?php if ($result['time_check'] <= time()+86400): ?>
                                <h3 class="text-danger"><?php echo $msg; ?></h3>
                        <?php else: ?>
                                <h3 class="text-danger"><?php echo $msg; ?></h3>
                        <?php endif; ?>
                <?php else: ?>
                        <h2>新規投稿</h2>
                        <form action="post.php" method="post">
                                <div class="form-group">
                                        <label>タイトル：<label>
                                        <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                        <label>投稿内容：<label>
                                        <textarea type="text" name="content" class="form-control" rows="4" cols="40" required></textarea>
                                </div>
                                <input class="btn btn-outline-danger" type="submit" name="add" value="投稿">
                        </form>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-primary mt-4">投稿一覧に戻る</a>
        </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>