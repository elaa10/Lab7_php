<?php
session_start();

// Verificare autentificare
if (!isset($_SESSION["user_name"])) {
    header("Location: index.php");
    exit;
}

$userName = $_SESSION["user_name"];

// Conectare MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "laborator7";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

// Trimitere comentariu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comment"])) {
    $comment = $_POST["comment"];
    $name = $_POST["name"];
    $articleId = $_POST["article"];

    // Verificare dacă utilizatorul există în tabela users
    $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $userCheck = $stmt->get_result()->fetch_assoc();

    if (!$userCheck) {
        echo "<p>Invalid name</p>";
    } else {
        // Verificare dacă articolul există
        $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->bind_param("i", $articleId);
        $stmt->execute();
        $articleCheck = $stmt->get_result()->fetch_assoc();

        if (!$articleCheck) {
            echo "<p>Invalid article</p>";
        } else {
            // Inserare comentariu neaprobat
            $approved = 0;
            $stmt = $conn->prepare("INSERT INTO comments (name, comment, approved, article_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $name, $comment, $approved, $articleId);
            $stmt->execute();
            header("Location: article.php");
            exit;
        }
    }
}

// Afișare articole și comentarii aprobate
function displayArticlesAndComments($conn)
{
    $sql = "SELECT * FROM articles";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<h2>" . htmlspecialchars($row["title"]) . "</h2>";
        echo "<p>" . htmlspecialchars($row["content"]) . "</p>";

        $stmt2 = $conn->prepare("SELECT name, comment FROM comments WHERE approved = 1 AND article_id = ?");
        $stmt2->bind_param("i", $row["id"]);
        $stmt2->execute();
        $comments = $stmt2->get_result();

        echo "<h3>Comentarii</h3><ul>";
        while ($comment = $comments->fetch_assoc()) {
            echo "<li><strong>" . htmlspecialchars($comment["name"]) . ":</strong> " . htmlspecialchars($comment["comment"]) . "</li>";
        }
        echo "</ul><hr>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Articole</title>
</head>
<body>
    <h1>Bine ai venit, <?php echo htmlspecialchars($userName); ?></h1>

    <?php displayArticlesAndComments($conn); ?>

    <h3>Adaugă un comentariu</h3>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="article">Articol:</label>
        <select id="article" name="article" required>
            <?php
            $stmt = $conn->prepare("SELECT id, title FROM articles ORDER BY id");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["title"]) . "</option>";
            }
            ?>
        </select>
        <br>
        <label for="name">Numele tău:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" required>
        <br>
        <label for="comment">Comentariul:</label>
        <textarea id="comment" name="comment" rows="4" cols="47" required></textarea>
        <br>
        <button type="submit">Trimite</button>
    </form>
</body>
</html>
