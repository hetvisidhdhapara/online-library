
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

$adminId = $_SESSION["admin_id"];

$successMessage = "";
$errorMessage = "";

// CSRF Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Fetch Current Username
$stmt = mysqli_prepare(
    $conn,
    "SELECT username FROM admins WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$admin) {
    die("Admin account not found.");
}

// Update Username
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $username = trim($_POST["username"] ?? "");

    if ($username === "") {

        $errorMessage = "Username is required.";

    } elseif (strlen($username) > 100) {

        $errorMessage = "Username must not exceed 100 characters.";

    } else {

        // Check Duplicate Username
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT id FROM admins WHERE username = ? AND id != ?"
        );

        mysqli_stmt_bind_param($checkStmt, "si", $username, $adminId);
        mysqli_stmt_execute($checkStmt);

        $checkResult = mysqli_stmt_get_result($checkStmt);
        $usernameExists = mysqli_num_rows($checkResult) > 0;

        mysqli_stmt_close($checkStmt);

        if ($usernameExists) {

            $errorMessage = "This username is already taken.";

        } else {

            // Update Database
            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE admins SET username = ? WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $updateStmt,
                "si",
                $username,
                $adminId
            );

            if (mysqli_stmt_execute($updateStmt)) {

                $admin["username"] = $username;

                // Update Session
                $_SESSION["admin_username"] = $username;

                $successMessage = "Username updated successfully!";

            } else {

                $errorMessage = "Unable to update username.";

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

    <title>Edit Admin Profile | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .edit-form {
            max-width: 600px;
            margin-top: 25px;
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

    <aside class="sidebar">

        <div class="logo">Readify.</div>

        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="add_book.php">➕ Add New Book</a>
            <a href="manage_books.php">📚 Manage Books</a>
            <a href="manage_students.php">👨‍🎓 Manage Students</a>
            <a href="profile.php" class="active">👤 Admin Profile</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>

    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Edit Admin Profile ✏️</h1>
                <p class="welcome">Update your admin username.</p>
            </div>

            <a href="profile.php" class="action-btn">
                ← Back to Profile
            </a>
        </header>

        <section class="section-card">

            <h2>Update Username</h2>

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

                    <label for="username">Admin Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($admin["username"]); ?>"
                        maxlength="100"
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
