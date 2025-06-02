<?php
$con = new mysqli("localhost", "root", "", "laborator7");
if ($con->connect_error) {
    die("Eroare conexiune: " . $con->connect_error);
}

$code = $_GET["code"] ?? '';
if ($code === '') {
    die("Cod invalid.");
}

$sql = "UPDATE users4 SET confirmed = 1 WHERE confirmation_code = ? AND confirmed = 0";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Cont confirmat cu succes!";
} else {
    echo "Cod invalid sau cont deja confirmat.";
}

$stmt->close();
$con->close();
?>
