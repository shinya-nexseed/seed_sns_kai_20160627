<?php
  // DBやセッションの設定
  require('dbconnect.php');
  session_start();

  // デバッグ用
  echo '<br>';
  echo '<br>';
  echo '================ デバッグ用表示エリア ================';
  echo '<br>';

  // ログイン判定
  if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
      $_SESSION['time'] = time();
      $sql = sprintf('SELECT * FROM `members` WHERE `member_id`=%d',
          mysqli_real_escape_string($db, $_SESSION['id'])
        );
      $record = mysqli_query($db, $sql) or die (mysqli_error());
      $member = mysqli_fetch_assoc($record);
  } else {
      header('Location: login.php');
      exit();
  }

  $error = Array();
  // $_POSTがある場合 (更新ボタンが押された際の処理)
  if (!empty($_POST)) {

      // 入力必須である「現在のパスワード」と「DBに登録されているパスワード」の暗号化したものが一致すれば処理実行
      if (sha1($_POST['password']) == $member['password']) {
          echo 'パスワード一致 - 処理を開始します。';
          echo '<br>';

          // ↓↓↓↓↓このif文の中に各項目のバリデーションやエラーが無かった際のアップデート処理を記述していく

          // ニックネームの空チェック
          if ($_POST['nick_name'] == '') {
              // TODO : ニックネームが空の場合のエラー
              $error['nick_name'] = 'blank';
          }

          // TODO : メールアドレスの空チェック
          if ($_POST['email'] == '') {
              $error['email'] = 'blank';
          }

          // 新規パスワードが空でなければ処理実行
          if (!empty($_POST['new_password'])) {

              // TODO : 新規パスワードが4文字以上かチェック
              echo strlen($_POST['new_password']);
              if (strlen($_POST['new_password']) < 4) {
                  $error['new_password'] = 'length';
              }

              // TODO : 新規パスワードと確認用パスワードが一致するかチェック
              if ($_POST['new_password'] != $_POST['confirm_password']) {
                  $error['new_password'] = 'incorrect';
              }

          // 新規パスワードが空の場合は入力された現在のパスワードを代入
          } else {
              $_POST['new_password'] = $_POST['password'];
          }

          // 画像バリデーション
          $fileName = $_FILES['picture_path']['name'];
          if (!empty($fileName)) {
              $ext = substr($fileName, -3);

              // TODO : 画像の拡張子が「jpg」、「gif」、「png」、「JPG」、「PNG」かどうかチェック
              if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png' && $ext != 'JPG' && $ext != 'PNG') {
                  $error['picture_path'] = 'type';
              }
          }

          // 重複アカウントのチェック
          if (empty($error)) {
              // 入力されているメールアドレスと、DBに存在するログインユーザーのメールアドレスが違っていれば処理
              if ($_POST['email'] != $member['email']) {
                  $sql = sprintf(
                      'SELECT COUNT(*) AS cnt FROM members WHERE email="%s"',
                      mysqli_real_escape_string($db,$_POST['email'])
                    );
                  $record = mysqli_query($db,$sql) or die(mysqli_error($db));
                  $table = mysqli_fetch_assoc($record);
                  // TODO : アカウントの取得結果が0以上かチェック
                  if ($table['cnt'] > 0) {
                      $error['email'] = 'duplicate';
                  }
              }
          }

          // エラーがなければ
          if (empty($error)) {

              // 画像が選択されていれば
              if (!empty($fileName)) {
                  // 画像のアップロード
                  $picture = date('YmdHis') . $_FILES['picture_path']['name'];
                  move_uploaded_file($_FILES['picture_path']['tmp_name'], 'member_picture/' . $picture);

              // 画像が選択されていなければDBの情報を代入
              } else {
                  $picture = $member['picture_path'];
              }

              // TODO : アップデート処理
              // $sql = sprintf('UPDATE `members` SET `nick_name`="%s", `email`="%s", `password`="%s", `picture_path`="%s", modified= WHERE ``=%d',

              //   );
    $sql = sprintf('UPDATE `members` SET `nick_name`="%s", `email`="%s", `password`="%s", `picture_path`="%s", modified=NOW() WHERE `member_id`=%d',
                      $_POST['nick_name'],
                      $_POST['email'],
                      sha1($_POST['new_password']),
                      $picture,
                      $_SESSION['id']
                  );
              echo $sql;
              mysqli_query($db, $sql) or die(mysqli_error($db));
          }

      // 現在のパスワードが間違っていた場合
      } else {
          $error['password'] = 'incorrect';
      }
  }

  // ユーザー情報の取得
  $sql = sprintf('SELECT * FROM `members` WHERE `member_id`=%d',
      mysqli_real_escape_string($db, $_SESSION['id'])
    );
  $record = mysqli_query($db, $sql) or die(mysqli_error($db));
  $member = mysqli_fetch_assoc($record);

  echo '<br>';
  echo '==================================================';
?>



<!DOCTYPE html">
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" type="text/css" href="style.css">
  <title>会員情報編集</title>
</head>
<body>
<div id="wrap">
<div id="head">
<h1>会員情報編集</h1>
</div>

<div id="content">
<form action="" method="post" enctype="multipart/form-data">
  <dl>
    <dt>ニックネーム<span class="required">必須</span></dt>
    <dd>
      <?php if (isset($_POST['nick_name'])): ?>
          <input type="text" name="nick_name" size="35" maxlength="255" value="<?php echo $_POST['nick_name']; ?>">
      <?php else: ?>
          <input type="text" name="nick_name" size="35" maxlength="255" value="<?php echo $member['nick_name']; ?>">
      <?php endif; ?>
      <?php if (isset($error['nick_name']) && $error['nick_name'] == 'blank'): ?>
          <p class="error">* ニックネームを入力してください。</p>
      <?php endif; ?>
    </dd>
    <dt>メールアドレス<span class="required">必須</span></dt>
    <dd>
      <?php if (isset($_POST['email'])): ?>
          <input type="email" name="email" size="35" maxlength="255" value="<?php echo $_POST['email']; ?>">
      <?php else: ?>
          <input type="email" name="email" size="35" maxlength="255" value="<?php echo $member['email']; ?>">
      <?php endif; ?>
      <?php if (isset($error['email']) && $error['email'] == 'blank'): ?>
          <p class="error">* メールアドレスを入力してください。</p>
      <?php endif; ?>
      <?php if (isset($error['email']) && $error["email"] == 'duplicate'): ?>
          <p class="error">* 指定されたメールアドレスはすでに登録されています。</p>
      <?php endif; ?>
    </dd>
    <dt>パスワード<span class="required">必須</span></dt>
    <dd>
      <input type="password" name="password" size="10" maxlength="20">
      <?php if (isset($error['password']) && $error['password'] == 'blank'): ?>
          <p class="error">* パスワードを入力してください。</p>
      <?php endif; ?>
      <?php if (isset($error['password']) && $error['password'] == 'incorrect'): ?>
          <p class="error">* パスワードが間違っています。</p>
      <?php endif; ?>
    </dd>
    <dt>新規パスワード</dt>
    <dd>
      <input type="password" name="new_password" size="10" maxlength="20">
      <?php if (isset($error['new_password']) && $error['new_password'] == 'length'): ?>
          <p class="error">* パスワードは4文字以上で入力してください。</p>
      <?php endif; ?>
      <?php if (isset($error['new_password']) && $error['new_password'] == 'incorrect'): ?>
          <p class="error">* 確認用パスワードと一致しません。</p>
      <?php endif; ?>
    </dd>
    <dt>確認用パスワード</dt>
    <dd>
      <input type="password" name="confirm_password" size="10" maxlength="20">
    </dd>
    <dt>写真</dt>
    <dd>
      <img src="member_picture/<?php echo $member['picture_path']; ?>" width="100" height="100">
      <input type="file" name="picture_path" size="35">
      <?php if (isset($error['picture_path']) && $error['picture_path'] == 'type'): ?>
          <p class="error">* プロフィール写真には「.gif」「.jpg」「.png」の画像を指定してください。</p>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
          <p class="error">* 画像を指定していた場合は恐れ入りますが、画像を改めて指定してください。</p>
      <?php endif; ?>
    </dd>
  </dl>
  <div><input type="submit" value="会員情報更新" /></div>
</form>
</div>

<div id="foot">
<p><img src="../images/txt_copyright.png" width="136" height="15" alt="(C) H2O SPACE, Mynavi" /></p>
</div>

</div>
</body>
</html>
