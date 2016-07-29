<?php
    session_start();
    require('dbconnect.php');

    if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
        // ログインしている
        $_SESSION['time'] = time();

        $sql = sprintf('SELECT * FROM members WHERE member_id=%d',
          mysqli_real_escape_string($db, $_SESSION['id'])
        );
        $record = mysqli_query($db, $sql) or die(mysqli_error($db));
        // ログインしているメンバー情報すべてが入る
        $member = mysqli_fetch_assoc($record);
    } else {
        // ログインしていない
        header('Location: login.php');
        exit();
    }

    // 投稿を記録する
    if (!empty($_POST)) {
        if ($_POST['tweet'] != '') {
            $sql = sprintf('INSERT INTO tweets SET member_id=%d, tweet="%s", reply_tweet_id=%d, created=NOW()',
              mysqli_real_escape_string($db, $member['member_id']),
              mysqli_real_escape_string($db, $_POST['tweet']),
              mysqli_real_escape_string($db, $_POST['reply_tweet_id'])
            );
            mysqli_query($db, $sql) or die(mysqli_error($db));
            header('Location: index.php');
            exit();
        }
    }

    // 投稿を取得する
    $page = '';
    if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
    }

    if ($page == '') {
        $page = 1;
    }

    // max(10,2,8,21,5) → 21
    $page = max($page, 1); // 最低でも1が$pageには入る

    // 最終ページを取得する
    $sql = 'SELECT COUNT(*) AS cnt FROM tweets';
    $recordSet = mysqli_query($db, $sql) or die(mysqli_error($db));
    $tweets = mysqli_fetch_assoc($recordSet);
    // ceil(2.098765456) → 3
    $maxPage = ceil($tweets['cnt'] / 5);
    // min(10,2,8,21,5) → 2
    $page = min($page, $maxPage); // 最大のページ数を$pageに入れる

    $start = ($page - 1) * 5;
    $start = max(0, $start);
    // 最小単位0 → 1件目からデータを取得する (1ページ目のデータ)

    $sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM members m, tweets t WHERE m.member_id=t.member_id ORDER BY t.created DESC LIMIT %d, 5',
          $start
      );
    // LIMIT 何件目からか, 何件取得するか

    $posts = mysqli_query($db, $sql) or die(mysqli_error($db));

    // 返信の場合　
    if (isset($_REQUEST['res'])) {
      $sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM members m, tweets t WHERE m.member_id=t.member_id AND t.tweet_id=%d ORDER BY t.created DESC',
        mysqli_real_escape_string($db, $_REQUEST['res'])
      );
      $record = mysqli_query($db, $sql) or die(mysqli_error($db));
      $table = mysqli_fetch_assoc($record);
      $tweet = '@' . $table['nick_name'] . ' ' . $table['tweet'] . '->';
    }
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
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ <?php echo $member['nick_name']; ?> さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <?php if (isset($tweet)): ?>
                    <textarea name="tweet" cols="50" rows="5" class="form-control"><?php echo htmlspecialchars($tweet, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <input type="hidden" name="reply_tweet_id" value="<?php echo htmlspecialchars($_REQUEST['res'], ENT_QUOTES, 'UTF-8'); ?>" />
                <?php else: ?>
                    <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php endif; ?>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
            &nbsp;&nbsp;&nbsp;&nbsp;
            <li><a href="index.php?page=<?php print($page-1); ?>" class="btn btn-default">前</a></li>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <li><a href="index.php?page=<?php print($page+1); ?>" class="btn btn-default">次</a></li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
        <!-- 検索窓設置 -->
        <form method="get" action="" class="form-horizontal" role="form">
          <?php // URLにパラメータとしてsearch_wordをURLに渡す ?>
          <input type="text" name="search_word" value="">&nbsp;&nbsp;

          <input type="submit" class="btn btn-success btn-xs" value="検索">
        </form>
        <?php while($post = mysqli_fetch_assoc($posts)): ?>
        <div class="msg">
          <img src="member_picture/<?php echo $post['picture_path']; ?>" width="48" height="48">
          <p>
            <?php echo $post['tweet']; ?><span class="name"> (<?php echo $post['nick_name']; ?>) </span>
            [<a href="index.php?res=<?php echo $post['tweet_id']; ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo $post['tweet_id']; ?>">
              <?php echo $post['created']; ?>
            </a>
            [<a href="edit.php?tweet_id=<?php echo $post['tweet_id']; ?>" style="color: #00994C;">編集</a>]
            [<a href="delete.php?tweet_id=<?php echo $post['tweet_id']; ?>" style="color: #F33;">削除</a>]
          </p>
        </div>
        <?php endwhile; ?>

      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
