
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Get Student ID
if (!isset($_GET["id"]) || !filter_var($_GET["id"], FILTER_VALIDATE_INT)) {
    die("Invalid student ID.");
}

$studentId = (int) $_GET["id"];

// Fetch Student Details
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, created_at FROM students WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Details | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="logo">Readify.</div>

        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="manage_books.php">📚 Manage Books</a>
            <a href="manage_students.php" class="active">👨‍🎓 Manage Students</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Student Details 🎓</h1>
                <p class="welcome">Registered student information.</p>
            </div>

            <a href="manage_students.php" class="action-btn">
                ← Back to Students
            </a>
        </header>

        <section class="section-card">

            <h2>Student Information</h2>

            <table class="book-table">
                <tr>
                    <th>Student ID</th>
                    <td><?php echo $student["id"]; ?></td>
                </tr>

                <tr>
                    <th>Student Name</th>
                    <td><?php echo htmlspecialchars($student["name"]); ?></td>
                </tr>

                <tr>
                    <th>Email Address</th>
                    <td><?php echo htmlspecialchars($student["email"]); ?></td>
                </tr>

                <tr>
                    <th>Registration Date</th>
                    <td>
                        <?php echo date("d M Y", strtotime($student["created_at"])); ?>
                    </td>
                </tr>
            </table>

        </section>

    </main>

</div>

</body>
</html>
