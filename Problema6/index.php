<?php
session_start();
session_destroy(); // destroy session for each leave or refresh
session_start();

if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true) {
    header("Location: user.php");
    exit;
}

function validateData($data)
{
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, "UTF-8");
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

    // Search in users
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_role"] = "user";
        header("Location: article.php");
        exit();
    }

    // Search in admins
    $sql = "SELECT * FROM admins WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin) {
        $_SESSION["user_name"] = $admin["name"];
        $_SESSION["user_role"] = "admin";
        header("Location: admin.php");
        exit();
    }

    $error = "Nume de utilizator sau parolă incorectă.";
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Problema 6</title>
</head>

<body>
	<h1>Autentificare</h1>

	<?php
	if (isset($error)) {
		echo "<p>" . htmlspecialchars($error) . "</p>";
	}
	?>
	<!-- Formularul de autentificare (cu metoda POST)
	htmlspecialchars($_SERVER["PHP_SELF"]) este o metodă de securitate pentru a preveni atacurile de tip XSS (Cross Site Scripting) -->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<div style="display: inline-block; text-align: right;">
			<label for="username">Nume de utilizator:</label>
			<input type="text" id="username" name="username" required><br>

			<label for="password">Parolă:</label>
			<input type="password" id="password" name="password" required><br>
		</div>
		<input type="submit" value="Autentificare">
	</form>
</body>

</html>