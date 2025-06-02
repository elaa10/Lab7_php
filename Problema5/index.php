<?php
session_start();
session_destroy();
session_start();

if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true) {
	header("Location: user.php");
	exit;
}

function validateData($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
	return $data;
}

// Conectare la MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "laborator7";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
	die("Conexiune eșuată: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = validateData($_POST["username"]);
	$password = validateData($_POST["password"]);

	$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $username, $password);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();

	if ($result) {
		$_SESSION["authenticated"] = true;
		$_SESSION['user_name'] = $result['name'];
		header("Location: user.php");
		exit;
	} else {
		$error = "Nume de utilizator sau parolă incorectă.";
	}
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
	<meta charset="UTF-8" />
	<title>Problema 5 - Autentificare user</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<style>
		body { font-family: Arial; padding: 20px; }
		form { background: #f8f8f8; padding: 20px; border-radius: 8px; width: 300px; }
		.error { color: red; }
	</style>
</head>

<body>
	<h1>Autentificare</h1>

	<?php if (isset($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>

	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<label for="username">Nume utilizator:</label><br>
		<input type="text" id="username" name="username" required><br><br>

		<label for="password">Parolă:</label><br>
		<input type="password" id="password" name="password" required><br><br>

		<input type="submit" value="Autentificare">
	</form>
</body>
</html>
