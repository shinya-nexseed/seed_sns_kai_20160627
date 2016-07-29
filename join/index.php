<?php
    session_start(); // $_SESSIONを使用する際は必ずこの関数を定義、一番上の行であること
    require('../dbconnect.php');
    echo '<br>';
    echo '<br>';

    $error = array();

    // 送信ボタンが押された時
    if (!empty($_POST)) {
        // エラー項目の確認
        if ($_POST['nick_name'] == '') {
            $error['nick_name'] = 'blank';
        }

        if ($_POST['email'] == '') {
            $error['email'] = 'blank';
        }

        if ($_POST['password'] == '') {
            $error['password'] = 'blank';
        }

        // 選択された画像の名前を取得
        $fileName = $_FILES['picture_path']['name']; // $_FILESもスーパーグローバル変数
        echo $fileName;
        // 選択された画像の拡張子チェック
        if (!empty($fileName)) {
            $ext =substr($fileName, -3);
            // 拡張子がjpgもしくはgif以外のデータならエラーを出す
            if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
                $error['image'] = 'type';
            }
        }

        // 重複アカウントのチェック
        if (empty($error)) {
            $sql = sprintf(
                // 条件に一致し取得したデータの数を返すのがCOUNT()
                'SELECT COUNT(*) AS cnt FROM members WHERE email="%s"',
                mysqli_real_escape_string($db,$_POST['email'])
            );
            $record = mysqli_query($db,$sql) or die(mysqli_error($db));
            $table = mysqli_fetch_assoc($record);
            var_dump($table);

            if ($table['cnt'] > 0) {
                $error['email'] = 'duplicate';
            }
        }

        // すべてのフォーム入力チェックを満たした状態でデータが入力されていれば
        // エラーが一件もなければ画像アップロード等の処理をする
        if (empty($error)) {
            // 画像をアップロード
            $image = date('YmdHis') . $_FILES['picture_path']['name'];

            // move_uploaded_file()関数は指定した場所に指定した名前で画像をアップロードします。
            move_uploaded_file($_FILES['picture_path']['tmp_name'], '../member_picture/' . $image);

            // check.phpにデータを受け渡すための処理
            $_SESSION['join'] = $_POST;
            $_SESSION['join']['picture_path'] = $image;
            header('Location: check.php');
            exit(); // PHP言語基盤 この行で処理を停止する
        }
    }

    // 書き直し
    if (isset($_REQUEST['action'])) {
      // $_REQUESTスーパーグローバル変数
      // $_GETと$_POSTなどのスーパーグローバル変数を含む変数です。
      if ($_REQUEST['action'] == 'rewrite') {

        $_POST = $_SESSION['join'];
        $error['rewrite'] = true;
      }
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
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->


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
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>会員登録</legend>
          <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
          <!-- ニックネーム -->
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun">
              <?php if(isset($error['nick_name'])): ?>
                  <?php if($error['nick_name'] == 'blank'): ?>
                      <p class="error">ニックネームを入力して下さい。</p>
                  <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
              <?php if(isset($error['email'])): ?>
                  <?php if($error['email'] == 'blank'): ?>
                      <p class="error">メールアドレスを入力して下さい。</p>
                  <?php endif; ?>
                  <?php if ($error["email"] == 'duplicate'): ?>
                      <p class="error">* 指定されたメールアドレスはすでに登録されています。</p>
                  <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <input type="password" name="password" class="form-control" placeholder="">
              <?php if(isset($error['password'])): ?>
                  <?php if($error['password'] == 'blank'): ?>
                      <p class="error">パスワードを入力して下さい。</p>
                  <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
              <input type="file" name="picture_path" class="form-control">
              <?php if (isset($error['picture_path']) && $error['picture_path'] == 'type'): ?>
                <p class="error">* プロフィール写真には「.gif」「.jpg」「.png」の画像を指定してください。</p>
              <?php endif; ?>
              <?php if (!empty($error)): ?>
                <p class="error">* 恐れ入りますが、画像を改めて指定してください。</p>
              <?php endif; ?>
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
  </body>
</html>
