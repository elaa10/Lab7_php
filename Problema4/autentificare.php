<?php
session_start();

$con = new mysqli("localhost", "root", "", "laborator7");
if ($con->connect_error) {
    die("Conexiunea a eșuat: " . $con->connect_error);
}

$username = $_POST["username"] ?? '';
$password = $_POST["password"] ?? '';

// verificare pattern username + parolă
$patternUser = '/^[a-zA-Z][a-zA-Z0-9-_\.]{1,30}$/';
$patternPass = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$/';

if (!preg_match($patternUser, $username) || !preg_match($patternPass, $password)) {
    echo "Date invalide.<br>";
    echo file_get_contents("index.html");
    exit;
}

// verificare existență și confirmare
$sql = "SELECT * FROM users4 WHERE username = ? AND password = ? AND confirmed = 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $_SESSION["user"] = $username;
    echo "User logat cu succes!";
    // redirectare opțională:
    // header("Location: welcome.php");
} else {
    echo "Utilizator inexistent, parolă greșită sau cont neconfirmat.<br>";
    echo file_get_contents("index.html");
}

$stmt->close();
$con->close();
?>
