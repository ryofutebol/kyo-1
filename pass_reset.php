<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
session_start();
$dsn = "mysql:host=localhost; dbname=test_db; charset=utf8";
$mail = $_GET['mail'];
try {
        $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
        echo $e->getMessage();
}
$sql = "SELECT * FROM users WHERE mail = :mail";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
$stmt->execute();
$check = $stmt->fetch();
if (isset($_POST['register'])) {
        $check_flag = bin2hex(random_bytes(16));
        $new_pass = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET pass = :new_pass, check_flag = :check_flag WHERE mail = :mail";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindValue(':new_pass', $new_pass, PDO::PARAM_STR);
        $stmt->bindValue(':check_flag', $check_flag, PDO::PARAM_STR);
        $key = rawurldecode($_GET['key']);
        $expire = $_GET['expire'];
        $hash_key = hash_hmac('sha256', $check['check_flag'], $mail);
        //30以上経っていないかとkeyの部分が一致しているかのチェック
        if (intval($expire) < time() || !hash_equals($key,  $hash_key)) {
                $msg = '不正なアクセスです。';
        } else {
                $stmt->execute();
                $msg = '更新しました';
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
                <h2>パスワード再発行</h2>
                <?php if (isset($_POST['register'])): ?>
                        <h3><?php echo $msg; ?></h3>
                        <a href="login.php" class="btn btn-outline-primary mt-4">ログインページに戻る</a>
                <?php else: ?>
                        <form action="pass_reset.php?key=<?php echo $_GET['key']; ?>&expire=<?php echo $_GET['expire']; ?>&mail=<?php echo $_GET['mail']; ?>" method="post">
                                <div class="form-group">
                                        <label>新しいパスワード</label>
                                        <input type="password" name="new_pass" class="form-control" required>
                                </div>
                                <input type="hidden" name="token" value="<?php echo $check['soft_delete']; ?>">
                                <input type="submit" name="register" class="btn btn-outline-danger" value="登録">
                        </form>
                <?php endif; ?>
        </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>