<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
session_start();
$dsn = "mysql:host=localhost; dbname=kyoichi; charset=utf8";
$id = $_GET['user_id'];
try {
        $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
        echo $e->getMessage();
}
$sql = "SELECT * FROM users WHERE user_id = $id";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$user = $stmt->fetch();
if (isset($_POST['upload_btn'])) {
        $edit_comment = $_POST['comment'];
        $image = $user['image_name'];//データベースに元々入っている値
        $img_name = uniqid(mt_rand());//ファイル名をユニーク化
        $img_name .= '.' . substr(strrchr($_FILES['image']['name'], '.'), 1);//アップロードされたファイルの拡張子>を取得
        $file = "images/$img_name";
        $error = '';
        if (!empty($_FILES['image']['name'])) {//ファイルが選択されていれば$imageにファイル名を代入
                move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $img_name);//imagesディレクトリにファイル保存
                if (exif_imagetype($file)) {//画像ファイルかのチェック
                        $image = $img_name;
                } else {
                        $error = '画像ファイルを選択してください';
                }
        }
        $sql = "UPDATE users SET comment = :edit_comment, image_name = :image WHERE user_id = $id";
        $upd = $dbh->prepare($sql);
        $upd->bindValue(':image', $image, PDO::PARAM_STR);
        $upd->bindValue(':edit_comment', $edit_comment, PDO::PARAM_STR);
        if ($user['user_id'] == $_SESSION['user_id']) {
                $upd->execute();
                $msg ='更新しました';
        } else {
                $msg = 'ユーザーを確認してください';
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
                <h2>ユーザー情報編集</h2>
                <?php if (isset($_POST['upload_btn'])): ?>
                        <?php if ($error): ?>
                                <h3 class="text-danger"><?php echo $error; ?></h3>
                                <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-outline-primary mt-4">ユーザー情報編集へ</a>
                        <?php else: ?>
                                <h3 class="text-danger"><?php echo $msg; ?></h3>
                                <a href="user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-outline-primary mt-4">ユーザー情報ページへ</a>
                        <?php endif;?>
                <?php else: ?>
                        <form method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                        <p>ユーザー画像</p>
                                        <input type="file" name="image">
                                </div>
                                <div class="form-group">
                                        <p>一言コメント</p>
                                        <textarea name="comment" rows="4" cols="40" class="form-control"><?php echo $user['comment']; ?></textarea>
                                </div>
                                <input class="btn btn-outline-danger" type="submit" name="upload_btn" value="更新">
                        </form>
                <?php endif;?>
        </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>