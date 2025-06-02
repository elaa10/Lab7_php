<?php
session_start();

$con = new mysqli("localhost", "root", "", "laborator7");
if ($con->connect_error) {
    die("Conexiunea a eșuat: " . $con->connect_error);
}

$username = $_POST["username"] ?? '';
$password = $_POST["password"] ?? '';
$email = $_POST["email"] ?? '';

$patternUser = '/^[a-zA-Z][a-zA-Z0-9-_\.]{1,30}$/';
$patternPass = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$/';

if (!preg_match($patternUser, $username)) {
    echo "Username invalid.";
    exit;
}
if (!preg_match($patternPass, $password)) {
    echo "Parola nu respectă cerințele.";
    exit;
}

// Verifică dacă utilizatorul există deja
$sqlCheck = "SELECT id FROM users4 WHERE username = ? OR email = ?";
$stmt = $con->prepare($sqlCheck);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Username sau email deja existent.";
    exit;
}

// Generează un cod de confirmare
$confirmation_code = bin2hex(random_bytes(16));

// Salvează utilizatorul cu status neconfirmat (confirmed = 0)
$sqlInsert = "INSERT INTO users4 (username, password, email, confirmed, confirmation_code) VALUES (?, ?, ?, 0, ?)";
$stmt = $con->prepare($sqlInsert);
$stmt->bind_param("ssss", $username, $password, $email, $confirmation_code);
if ($stmt->execute()) {
    $link = "http://localhost/Lab7_php/Problema4/confirm.php?code=$confirmation_code";

    $subject = "Confirmare cont";
    $message = "<p>Buna $username,</p><p>Te rugam sa confirmi contul tau accesand urmatorul link:</p><a href=\"$link\">$link</a>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@example.com";

    if (mail($email, $subject, $message, $headers)) {
        echo "Email de confirmare trimis. Verifică inbox-ul.";
    } else {
        echo "Nu s-a putut trimite emailul.";
    }
} else {
    echo "Eroare la înregistrare.";
}

$con->close();
?>