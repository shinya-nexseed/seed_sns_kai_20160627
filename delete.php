<?php
		session_start();
		require('dbconnect.php');

		// ログインしていれば処理
		if (isset($_SESSION['id'])) {
				// URLのパラメータ上にあるidの値 → 削除したいtweetデータのid
				$id = $_REQUEST['tweet_id'];

				// 投稿を検査する
				$sql = sprintf('SELECT * FROM tweets WHERE tweet_id=%d',
					mysqli_real_escape_string($db, $id)
				);
				$record = mysqli_query($db, $sql) or die(mysqli_error($db));
				$table = mysqli_fetch_assoc($record);
				// 削除しようとしているデータの持ち主とログインユーザーが等しければ処理
				if ($table['member_id'] == $_SESSION['id']) {
						// 削除
						$sql = sprintf('DELETE FROM tweets WHERE tweet_id=%d', mysqli_real_escape_string($db, $id)
						);
						mysqli_query($db, $sql) or die(mysqli_error($db));
				}
		}

		header('Location: index.php');
		exit();
?>
