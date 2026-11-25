
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

$studentId = $_SESSION["student_id"];

$sql = "SELECT name, email FROM students WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student account not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);

    if ($name == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid name and email.";
    } else {

        $checkSql = "SELECT id FROM students WHERE email = ? AND id != ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "si", $email, $studentId);
        mysqli_stmt_execute($checkStmt);

        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "This email is already registered.";
        } else {

            $updateSql = "UPDATE students SET name = ?, email = ? WHERE id = ?";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "ssi", $name, $email, $studentId);

            if (mysqli_stmt_execute($updateStmt)) {
                $_SESSION["student_name"] = $name;
                $_SESSION["student_email"] = $email;

                header("Location: profile.php?updated=1");
                exit();
            } else {
                $message = "Profile update failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - Readify</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            padding: 40px;
        }

        .edit-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        h2 {
            color: #14213d;
            text-align: center;
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
            color: #d62828;
            text-align: center;
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

<div class="edit-container">

    <h2>Edit My Profile</h2>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST">

        <label>Full Name</label>
        <input
            type="text"
            name="name"
            value="<?php echo htmlspecialchars($student["name"]); ?>"
            required
        >

        <label>Email Address</label>
        <input
            type="email"
            name="email"
            value="<?php echo htmlspecialchars($student["email"]); ?>"
            required
        >

        <button type="submit">Save Changes</button>

    </form>

    <a href="profile.php" class="back-link">Back to Profile</a>

</div>

</body>
</html>
