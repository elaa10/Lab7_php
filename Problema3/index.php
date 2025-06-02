<?php
session_start();
session_destroy();
session_start();

if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true) {
	header("Location: professor.php");
	exit;
}

function validateData($data) {
	return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, "UTF-8");
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "catalog";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
	die("Conexiune eșuată: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$user = validateData($_POST["username"]);
	$pass = validateData($_POST["password"]);

	$sql = "SELECT * FROM professors WHERE username = ? AND password = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $user, $pass);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();

	if ($result) {
		$_SESSION["authenticated"] = true;
		$_SESSION['professor_name'] = $result['name'];
		header("Location: professor.php");
		exit;
	} else {
		$error = "Nume de utilizator sau parolă incorectă.";
	}
}

$sql = "SELECT * FROM grades ORDER BY id ASC";
$results = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Problema 3 - Autentificare profesor</title>
	<style>
		body { font-family: Arial, sans-serif; padding: 20px; background-color: #f0f0f0; }
		form { background: #fff; padding: 20px; border-radius: 8px; width: 300px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
		table { margin-top: 30px; border-collapse: collapse; width: 100%; background: #fff; }
		th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
		th { background: #eee; }
		.error { color: red; }
	</style>
</head>
<body>

	<h1>Autentificare Profesor</h1>

	<?php if (isset($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>

	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<label for="username">Nume de utilizator:</label><br>
		<input type="text" id="username" name="username" required><br><br>

		<label for="password">Parolă:</label><br>
		<input type="password" id="password" name="password" required><br><br>

		<input type="submit" value="Autentificare">
	</form>

	<h2>Note Studenți:</h2>

	<table>
		<tr>
			<th>ID Student</th>
			<th>Materie</th>
			<th>Notă</th>
			<th>Profesor</th>
		</tr>
		<?php while ($row = $results->fetch_assoc()) { ?>
			<tr>
				<td><?php echo htmlspecialchars($row['student_id']); ?></td>
				<td><?php echo htmlspecialchars($row['subject']); ?></td>
				<td><?php echo htmlspecialchars($row['grade']); ?></td>
				<td><?php echo htmlspecialchars($row['professor']); ?></td>
			</tr>
		<?php } ?>
	</table>

</body>
</html>
