<?php
	$body = "login";
	$pageTitle = "Login";
	session_start();
	if (isset($_SESSION['name'])) {
		header('Location: index.php'); // Redirect To Home Page
		exit();
	}
	include 'init.php';

	// Check If User Coming From HTTP Post Request

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		$username = $_POST['user'];
		$password = $_POST['pass'];
		$hashedPass = sha1($password);

		// Check If The User Exist In Database

		$stmt = $con->prepare("SELECT 
									ID, name, password 
								FROM 
									admin 
								WHERE 
									user_name = ? 
								AND 
									password = ? 
								LIMIT 1");

		$stmt->execute(array($username, $hashedPass));
		$row = $stmt->fetch();
		$count = $stmt->rowCount();

		// If Count > 0 This Mean The Database Contain Record About This Username

		if ($count > 0) {
			$_SESSION['name'] = $row['name']; // Register Session ID
			$_SESSION['ID'] = $row['ID']; // Register Session ID
			header('Location: index.php'); // Redirect To Dashboard Page
			exit();
		}
	}

?>
	<div class="wrapper">
		<h1 class="mb-3">Login</h1>
		<div class="login-page">
			<div class="form">
				<form class="login-form" method="POST">
					<input type="text" placeholder="username" class="form-control mb-3" name="user"/>
					<input type="password" placeholder="password" class="form-control mb-3" name="pass"/>
					<input type="submit" value="Login" class="btn btn-success w-100"/>
				</form>
			</div>
		</div>
	</div>
<?php include $tpl . 'footer.php'; ?>