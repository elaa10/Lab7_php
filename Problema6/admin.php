<?php
session_start();

// Check if the user is authenticated
if (!isset($_SESSION["user_name"])) {
    header("Location: index.php");
    exit;
}

// Conectare MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "laborator7"; // Numele bazei tale de date

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

// Verifică dacă este administrator
$isAdmin = false;
if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
    $isAdmin = true;
}

// Aprobare comentariu
if ($isAdmin && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["approve"])) {
    $commentId = intval($_POST["approve"]);
    $sql = "UPDATE comments SET approved = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Ștergere comentariu
if ($isAdmin && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $commentId = intval($_POST["delete"]);
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Afișare comentarii neaprobate
function displayUnapprovedComments($conn)
{
    $sql = "SELECT * FROM articles";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $articles = $stmt->get_result();

    while ($article = $articles->fetch_assoc()) {
        echo "<h3>Articol: " . htmlspecialchars($article["title"]) . "</h3>";

        $sql2 = "SELECT id, name, comment FROM comments WHERE approved = 0 AND article_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $article["id"]);
        $stmt2->execute();
        $comments = $stmt2->get_result();

        echo "<h4>Comentarii</h4><ul>";
        while ($comment = $comments->fetch_assoc()) {
            echo "<li><strong>" . htmlspecialchars($comment["name"]) . ":</strong> " . htmlspecialchars($comment["comment"]);
            echo "<form method='POST' style='display:inline;' action='admin.php'>";
            echo "<input type='hidden' name='approve' value='" . $comment["id"] . "'>";
            echo "<button type='submit'>Aprobă</button>";
            echo "</form>";
            echo "<form method='POST' style='display:inline; margin-left:10px;' action='admin.php'>";
            echo "<input type='hidden' name='delete' value='" . $comment["id"] . "'>";
            echo "<button type='submit'>Șterge</button>";
            echo "</form></li>";
        }
        echo "</ul><hr>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
</head>

<body>
    <h1>Admin Panel</h1>

    <h2>Comentarii neaprobate</h2>
    <?php
    displayUnapprovedComments($conn);
    $conn->close();
    ?>
</body>

</html>
