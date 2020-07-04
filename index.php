<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
session_start();
$dsn = "mysql:host=localhost; dbname=test_db; charset=utf8";
try {   
        $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
        echo $e->getMessage();
        exit;
}
if (isset($_SESSION['user_id'])) {//ログインしているとき
        $link = 'post.php';
        $msg = '';
        $user_id = $_SESSION['user_id'];
} else {//ログインしていない時
        $link = 'signup.php';
        $user_id = '';
}
$page = $_GET['page'];
//カウントSQL
$csql = "SELECT count(*) FROM posts INNER JOIN users ON posts.user_id = users.user_id WHERE deleted_flag = 0";
$count = $dbh->prepare($csql);
$count->execute();
//総カウント数取得
$total = $count->fetchColumn();
//取得した件数を1ﾍﾟｰｼﾞで表示したい件数で割り、ceil関数で小数点以下を切り捨てる
$max_page = ceil($total / 10);
//最大のﾍﾟｰｼﾞ数以上を表示させないようにする
$page = min($page, $max_page);

//抽出SQL
//スタートのポジション計算
if ($page > 1) {
        // 例：２ページ目の場合は、『(2 × 10) - 10 = 10』
        $start = ($page * 10) - 10;
} else {
        $start = 0;
}
$ssql = "SELECT * FROM posts INNER JOIN users ON posts.user_id = users.user_id WHERE deleted_flag = 0 ORDER BY id DESC LIMIT {$start}, 10";
$stmt = $dbh->prepare($ssql);
$stmt->execute();
//10件ずつ取得
$result = $stmt->fetchAll();
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
                                <li class="nav-item m-2">
                                        <a href="login.php" class="btn btn-outline-danger">ログイン</a>
                                </li>
                                <li class="nav-item m-2">
                                        <a href="signup.php" class="btn btn-outline-danger">サインアップ</a>
                                </li>
                                <?php endif; ?>
                        </ul>
                </div>
        </nav>
</header>
<div class="jumbotron jumbotron-fluid bg-warning">
  <div class="container p-5">
    <h2 class="display-5">今日イチバンのことをシェアしよう！</h2>
    <p class="lead">嬉しいこと・楽しいこと・悲しいことなど今日一番印象に残ったことを共有する掲示板です</p>
  </div>
</div>
<div class="container pt-5">
<h2>Kyoichiの一覧</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
                <h4><?php echo $msg; ?></h4>
        <?php endif; ?>
<h3><a href="<?php echo $link; ?>" class="btn btn-danger">新規投稿</a></h3>
<table class="table">
        <tr>
                <th>投稿者名</th>
                <th>タイトル</th>
                <th>投稿内容</th>
                <th>投稿日時</th>
        </tr>
        <?php foreach ($result as $value): ?>
                <tr>
                        <td>
                                <a href="user.php?user_id=<?php echo $value['user_id']; ?>"><?php echo $value['name']; ?></a>
                        </td>
                        <td>
                                <?php echo $value['title']; ?>
                                <?php if ($user_id == $value['user_id']): ?>
                                        <a href="edit.php?post_id=<?php echo $value['id']; ?>" class="btn btn-primary btn-sm ml-2">編集</a>
                                        <a href="delete.php?post_id=<?php echo $value['id']; ?>" class="btn btn-primary btn-sm">削除</a>
                                <?php endif; ?>
                        </td>
                        <td><?php echo $value['content']; ?></td>
                        <td><?php echo $value['created_at']; ?></td>
                </tr>
        <?php endforeach; ?>
</table>
<nav aria-label="Page navigation example">
  <ul class="pagination justify-content-center">
    <li class="page-item">
      <a class="page-link" href="index.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
        <span aria-hidden="true">«</span>
      </a>
    </li>
        <?php for ($i = 1; $i <= $max_page; $i++): ?>
                <li class="page-item">
                        <a class="page-link" href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
        <?php endfor; ?>
    <li class="page-item">
      <a class="page-link" href="index.php?page=<?php echo $page + 1; ?>" aria-label="Next">
        <span aria-hidden="true">»</span>
      </a>
    </li>
  </ul>
</nav>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>