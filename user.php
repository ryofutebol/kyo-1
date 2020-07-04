<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
session_start();
$dsn = "mysql:host=localhost; dbname=test_db; charset=utf8";
$id = $_GET['user_id'];
$user_id = '';
try {
        $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
        echo $e->getMessage();
}
$sql = "SELECT * FROM users WHERE user_id = $id";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$user = $stmt->fetch();
if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
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
                <h2>ユーザー情報</h2>
                <h3><?php echo $user['name']; ?></h3>
                <?php if (!empty($user['image_name'])): ?>
                        <img src="images/<?php echo $user['image_name']; ?>" class="img-fluid" height="300" width="300">
                <?php else: ?>
                        <p class="text-danger">画像未登録</p>
                <?php endif; ?>
                <?php if (!empty($user['comment'])): ?>
                        <p class="mt-3">一言コメント：<?php echo $user['comment']; ?></p>
                <?php else: ?>
                        <p class="text-danger mt-3">一言コメント未登録</p>
                <?php endif; ?>
                <?php if ($user['user_id'] == $user_id): ?>
                        <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-outline-primary mt-4">ユーザー情報編集</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-primary mt-4">投稿一覧に戻る</a>
        </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>