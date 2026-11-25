
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Check Student ID
if (!isset($_GET["id"]) || !filter_var($_GET["id"], FILTER_VALIDATE_INT)) {
    die("Invalid student ID.");
}

$studentId = (int) $_GET["id"];

$successMessage = "";
$errorMessage = "";

// Create CSRF Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Fetch Student Details
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email FROM students WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    die("Student not found.");
}

// Update Student Details
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "" || $email === "") {

        $errorMessage = "All fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage = "Please enter a valid email address.";

    } else {

        // Check Duplicate Email
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT id FROM students WHERE email = ? AND id != ?"
        );

        mysqli_stmt_bind_param($checkStmt, "si", $email, $studentId);
        mysqli_stmt_execute($checkStmt);

        $checkResult = mysqli_stmt_get_result($checkStmt);
        $emailExists = mysqli_num_rows($checkResult) > 0;

        mysqli_stmt_close($checkStmt);

        if ($emailExists) {

            $errorMessage = "This email is already registered.";

        } else {

            // Update Database
            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE students SET name = ?, email = ? WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $updateStmt,
                "ssi",
                $name,
                $email,
                $studentId
            );

            if (mysqli_stmt_execute($updateStmt)) {

                $student["name"] = $name;
                $student["email"] = $email;

                $successMessage = "Student details updated successfully!";

            } else {

                $errorMessage = "Unable to update student details.";

            }

            mysqli_stmt_close($updateStmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Student | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .edit-form {
            max-width: 650px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #172554;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .save-btn {
            background: #172554;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .save-btn:hover {
            background: #263b75;
        }

        .success-message {
            color: #166534;
            background: #dcfce7;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error-message {
            color: #991b1b;
            background: #fee2e2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>

</head>

<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="logo">Readify.</div>

        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="add_book.php">➕ Add New Book</a>
            <a href="manage_books.php">📚 Manage Books</a>
            <a href="manage_students.php" class="active">👨‍🎓 Manage Students</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Edit Student ✏️</h1>
                <p class="welcome">Update registered student information.</p>
            </div>

            <a href="manage_students.php" class="action-btn">
                ← Back to Students
            </a>
        </header>

        <section class="section-card">

            <h2>Student Information</h2>

            <?php if ($successMessage !== ""): ?>
                <p class="success-message">
                    <?php echo htmlspecialchars($successMessage); ?>
                </p>
            <?php endif; ?>

            <?php if ($errorMessage !== ""): ?>
                <p class="error-message">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="edit-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
                >

                <div class="form-group">

                    <label for="name">Student Name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($student["name"]); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($student["email"]); ?>"
                        required
                    >

                </div>

                <button type="submit" class="save-btn">
                    Save Changes
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>
