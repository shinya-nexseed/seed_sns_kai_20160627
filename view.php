<?php
    session_start();
    require('dbconnect.php');

    // URLにパラメータidが存在しなければindex.phpへ強制遷移
    if (empty($_REQUEST['tweet_id'])) {
        header('Location: index.php');
        exit();
    }

    // 投稿を取得する
    $sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM members m, tweets t WHERE m.member_id=t.member_id AND t.tweet_id=%d ORDER BY t.created DESC',
      mysqli_real_escape_string($db, $_REQUEST['tweet_id'])
    );
    $posts = mysqli_query($db, $sql) or die(mysqli_error($db));
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.html">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4 content-margin-top">
        <?php if ($post = mysqli_fetch_assoc($posts)): ?>
            <div class="msg">
              <img src="member_picture/<?php echo $post['picture_path']; ?>" width="100" height="100">
              <p>投稿者 : <span class="name"> <?php echo $post['nick_name']; ?> </span></p>
              <p>
                つぶやき : <br>
                <?php echo $post['tweet']; ?>
              </p>
              <p class="day">
                <?php echo $post['created']; ?>
                [<a href="delete.php?tweet_id=<?php echo $post['tweet_id']; ?>" style="color: #F33;">削除</a>]
              </p>
            </div>
            <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
        <?php else: ?>
            <p>その投稿は削除されたか、URLが間違えています</p>
        <?php endif; ?>
      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
