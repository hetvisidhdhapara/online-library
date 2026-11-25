
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

// Create CSRF Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF Token
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {

        $errorMessage = "Invalid request. Please refresh the page.";

    } else {

        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        if (
            $currentPassword === "" ||
            $newPassword === "" ||
            $confirmPassword === ""
        ) {

            $errorMessage = "All fields are required.";

        } elseif (
            strlen($newPassword) < 8 ||
            strlen($newPassword) > 72
        ) {

            $errorMessage = "New password must contain 8 to 72 characters (bytes).";

        } elseif (strlen($confirmPassword) > 72) {

            $errorMessage = "Confirm password is too long.";

        } elseif ($newPassword !== $confirmPassword) {

            $errorMessage = "New password and confirm password do not match.";

        } elseif ($newPassword === $currentPassword) {

            $errorMessage = "New password must be different from current password.";

        } else {

            // Fetch Current Hashed Password
            $stmt = mysqli_prepare(
                $conn,
                "SELECT password FROM admins WHERE id = ?"
            );

            if ($stmt) {

                mysqli_stmt_bind_param($stmt, "i", $adminId);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $admin = mysqli_fetch_assoc($result);

                mysqli_stmt_close($stmt);

                if (!$admin) {

                    $errorMessage = "Admin account not found.";

                } elseif (!password_verify($currentPassword, $admin["password"])) {

                    $errorMessage = "Current password is incorrect.";

                } else {

                    // Hash New Password
                    $hashedPassword = password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );

                    if ($hashedPassword === false) {

                        $errorMessage = "Unable to secure new password.";

                    } else {

                        // Update Password
                        $updateStmt = mysqli_prepare(
                            $conn,
                            "UPDATE admins SET password = ? WHERE id = ?"
                        );

                        if ($updateStmt) {

                            mysqli_stmt_bind_param(
                                $updateStmt,
                                "si",
                                $hashedPassword,
                                $adminId
                            );

                            if (mysqli_stmt_execute($updateStmt)) {

                                session_regenerate_id(true);

                                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                                $successMessage = "Admin password changed successfully!";

                            } else {

                                $errorMessage = "Unable to update password.";

                            }

                            mysqli_stmt_close($updateStmt);

                        } else {

                            $errorMessage = "Something went wrong. Please try again.";

                        }
                    }
                }

            } else {

                $errorMessage = "Something went wrong. Please try again.";

            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Admin Password | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .password-form {
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
                <h1>Change Admin Password 🔐</h1>
                <p class="welcome">Keep your admin account secure.</p>
            </div>

            <a href="profile.php" class="action-btn">
                ← Back to Profile
            </a>
        </header>

        <section class="section-card">

            <h2>Update Password</h2>

            <?php if ($successMessage !== ""): ?>
                <p class="success-message">
                    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, "UTF-8"); ?>
                </p>
            <?php endif; ?>

            <?php if ($errorMessage !== ""): ?>
                <p class="error-message">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8"); ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="password-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8"); ?>"
                >

                <div class="form-group">
                    <label for="current_password">Current Password</label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="save-btn">
                    Change Password
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>
