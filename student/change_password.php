
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

$studentId = $_SESSION["student_id"];
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $currentPassword = $_POST["current_password"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    if ($currentPassword == "" || $newPassword == "" || $confirmPassword == "") {
        $message = "All fields are required.";
    } elseif (strlen($newPassword) < 8) {
        $message = "New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New password and confirm password do not match.";
    } else {

        $sql = "SELECT password FROM students WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $studentId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $student = mysqli_fetch_assoc($result);

        if (!$student || !password_verify($currentPassword, $student["password"])) {
            $message = "Current password is incorrect.";
        } else {

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateSql = "UPDATE students SET password = ? WHERE id = ?";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $studentId);

            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Password changed successfully!";
                $messageType = "success";
            } else {
                $message = "Unable to change password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - Readify</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            padding: 40px;
        }

        .password-container {
            max-width: 450px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            color: #14213d;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            background: #14213d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #fca311;
        }

        .message {
            text-align: center;
            color: #d62828;
        }

        .success {
            color: green;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #14213d;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="password-container">

    <h2>Change Password 🔐</h2>

    <?php if ($message != "") { ?>
        <p class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST">

        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" minlength="8" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" minlength="8" required>

        <button type="submit">Update Password</button>

    </form>

    <a href="profile.php" class="back-link">Back to Profile</a>

</div>

</body>
</html>
