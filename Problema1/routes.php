<?php

function validateData($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$departure = validateData($_GET['departure'] ?? '');
$arrival = validateData($_GET['arrival'] ?? '');
$intermediate = isset($_GET['checkbox']);

$servername = "localhost";
$username = "root";
$password = "";
$database = "laborator7";

// Conectare la MySQL
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

// Rute directe
$sql = "SELECT * FROM trenuri WHERE departure = ? AND arrival = ? ORDER BY hour_departure ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $departure, $arrival);
$stmt->execute();
$result = $stmt->get_result();

echo '<h2>Rute directe</h2>';
while ($row = $result->fetch_assoc()) {
    echo '<li>' . $row['nr_tren'] . ' ' . $row['tip_tren'] . ' ' . $row['departure'] . ' → ' . $row['arrival'] . ' ' . $row['hour_departure'] . ' - ' . $row['hour_arrival'] . '</li>';
}

// Rute intermediare
if ($intermediate) {
    echo '<h2>Rute cu oprire intermediară</h2>';

    $sql = "SELECT
                t1.nr_tren AS tren1, t1.tip_tren AS tip1, t1.departure AS dep1, t1.arrival AS arr1, t1.hour_departure AS hdep1, t1.hour_arrival AS harr1,
                t2.nr_tren AS tren2, t2.tip_tren AS tip2, t2.departure AS dep2, t2.arrival AS arr2, t2.hour_departure AS hdep2, t2.hour_arrival AS harr2
            FROM trenuri t1
            JOIN trenuri t2 ON t1.arrival = t2.departure
            WHERE t1.departure = ? AND t2.arrival = ? AND t1.hour_arrival < t2.hour_departure
            ORDER BY t1.hour_departure ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $departure, $arrival);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<li>' .
            $row['tren1'] . ' ' . $row['tip1'] . ' ' . $row['dep1'] . ' → ' . $row['arr1'] . ' ' . $row['hdep1'] . ' - ' . $row['harr1'] .
            ' <strong>→</strong> ' .
            $row['tren2'] . ' ' . $row['tip2'] . ' ' . $row['dep2'] . ' → ' . $row['arr2'] . ' ' . $row['hdep2'] . ' - ' . $row['harr2'] .
            '</li>';
    }
}

$conn->close();
