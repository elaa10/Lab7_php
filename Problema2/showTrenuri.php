<?php
$con = mysqli_connect("localhost", "root", "", "laborator7");
if (!$con) {
    die('Conexiune eșuată: ' . mysqli_connect_error());
}

$pageLimit = $_GET["limit"];
$pageNr = isset($_GET["nrPag"]) ? intval($_GET["nrPag"]) : 0;

// Validare numerică
if (!preg_match('/^[1-9][0-9]*$/', $pageLimit)) {
    die("Număr invalid de rezultate per pagină.");
}

// Interogare pentru pagină curentă
$offset = $pageNr * $pageLimit;
$sql = "SELECT * FROM trenuri LIMIT $offset, $pageLimit";
$result = mysqli_query($con, $sql);

// Form pentru a selecta numărul de trenuri pe pagină
echo "<form action='showTrenuri.php' method='GET'>
        <input type='hidden' name='nrPag' value='0'>
        <label for='limit'>Nr trenuri/pagină:</label>
        <input list='trenuri' name='limit' required>
        <datalist id='trenuri'>
            <option value='1'>
            <option value='2'>
            <option value='3'>
            <option value='4'>
            <option value='5'>
        </datalist>
        <input type='submit' value='Afișează'>
    </form>";

echo "<hr>";

echo "<table border='1'>
<tr>
    <th>Nr Tren</th>
    <th>Tip Tren</th>
    <th>Plecare</th>
    <th>Sosire</th>
    <th>Ora Plecare</th>
    <th>Ora Sosire</th>
</tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$row['nr_tren']}</td>
        <td>{$row['tip_tren']}</td>
        <td>{$row['departure']}</td>
        <td>{$row['arrival']}</td>
        <td>{$row['hour_departure']}</td>
        <td>{$row['hour_arrival']}</td>
    </tr>";
}
echo "</table><hr>";

// Navigare pagini
echo "<table><tr><td>";

// Prev
if ($pageNr > 0) {
    echo "<form method='GET'>
        <input type='hidden' name='nrPag' value='" . ($pageNr - 1) . "'>
        <input type='hidden' name='limit' value='$pageLimit'>
        <input type='submit' value='Prev'>
    </form>";
} else {
    echo "<button disabled>Prev</button>";
}
echo "</td><td>";

// Next
$sqlNext = "SELECT 1 FROM trenuri LIMIT " . (($pageNr + 1) * $pageLimit) . ", 1";
$nextResult = mysqli_query($con, $sqlNext);
if (mysqli_fetch_assoc($nextResult)) {
    echo "<form method='GET'>
        <input type='hidden' name='nrPag' value='" . ($pageNr + 1) . "'>
        <input type='hidden' name='limit' value='$pageLimit'>
        <input type='submit' value='Next'>
    </form>";
} else {
    echo "<button disabled>Next</button>";
}

echo "</td></tr></table>";

mysqli_close($con);
?>
