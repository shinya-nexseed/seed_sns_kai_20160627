<?php
  // PDOを使用してDBの処理をしていました。
  // どんな種類のDBにも使える

  // mysqli関数群を使用してDBの処理をしていきます。
  // mysqlでしか開発をしない環境であればこっちのほうがベター

  $db = mysqli_connect('localhost', 'root', '', 'seed_sns') or die(mysqli_connect_error());
      // LAMPの場合
      // localhost, root, mysql, データベース名
  mysqli_set_charset($db,'utf8');
?>
