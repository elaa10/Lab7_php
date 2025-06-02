<?php
session_start();
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: index.php");
    exit;
}
$professorName = $_SESSION['professor_name'] ?? '';

function validateData($data)
{
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, "UTF-8");
}

// Setări MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "catalog";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

$message = "";

// Dacă formularul a fost trimis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = validateData($_POST["id"]);
    $subject = validateData($_POST["subject"]);
    $grade = validateData($_POST["grade"]);

    // Verificăm dacă studentul există
    $checkStudent = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $checkStudent->bind_param("i", $id);
    $checkStudent->execute();
    $resStudent = $checkStudent->get_result();
    if ($resStudent->num_rows === 0) {
        $message = "Studentul nu există!";
    } else {
        // Verificăm dacă materia există
        $checkSubject = $conn->prepare("SELECT * FROM subjects WHERE subject = ?");
        $checkSubject->bind_param("s", $subject);
        $checkSubject->execute();
        $resSubject = $checkSubject->get_result();
        if ($resSubject->num_rows === 0) {
            $message = "Materia nu există!";
        } else {
            // Validăm nota
            if (!is_numeric($grade) || $grade < 1 || $grade > 10 || !ctype_digit($grade)) {
                $message = "Nota trebuie să fie un număr întreg între 1 și 10!";
            } else {
                // Inserăm nota
                $stmt = $conn->prepare("INSERT INTO grades (student_id, subject, grade, professor) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isis", $id, $subject, $grade, $professorName);
                if ($stmt->execute()) {
                    header("Location: professor.php");
                    exit();
                } else {
                    $message = "Eroare la adăugarea notei!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Problema 2 - Adăugare Notă</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        form, select { margin-bottom: 15px; }
        h1 { margin-bottom: 20px; }
        .error { color: red; }
        .field { margin-bottom: 10px; }
    </style>
</head>
<body>

    <h1>Adăugare Notă</h1>
    <p><strong>Bun venit, <?php echo htmlspecialchars($professorName); ?>!</strong></p>

    <?php if (!empty($message)) echo "<p class='error'>$message</p>"; ?>

    <form id="grades" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

        <!-- Select student -->
        <div class="field">
            <label for="id">Student:</label>
            <select name="id" required>
                <option value="" disabled selected>Selectează un student</option>
                <?php
                $result = $conn->query("SELECT * FROM students ORDER BY first_name");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["id"] . "'>" . htmlspecialchars($row["first_name"] . " " . $row["last_name"]) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Select subject -->
        <div class="field">
            <label for="subject">Materie:</label>
            <select name="subject" required>
                <option value="" disabled selected>Selectează o materie</option>
                <?php
                $result = $conn->query("SELECT DISTINCT subject FROM subjects ORDER BY subject");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["subject"] . "'>" . htmlspecialchars($row["subject"]) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Select grade -->
        <div class="field">
            <label for="grade">Notă [1-10]:</label>
            <select name="grade" required>
                <option value="" disabled selected>Selectează o notă</option>
                <?php
                for ($i = 1; $i <= 10; $i++) {
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select>
        </div>

        <input type="submit" value="Notează">

    </form>

</body>
</html>
