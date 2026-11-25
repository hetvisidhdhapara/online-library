
<?php
session_start();
include "../config/db.php";

$message = "";

// Create CSRF Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    // CSRF Validation
    if (!hash_equals($_SESSION["csrf_token"], $token)) {

        $message = "Invalid request. Please try again.";

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 ||
        $password === "" ||
        strlen($password) > 72
    ) {

        $message = "Invalid email or password.";

    } else {

        $sql = "SELECT id, name, email, password
                FROM students
                WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($student = mysqli_fetch_assoc($result)) {

            if (password_verify($password, $student["password"])) {

                // Regenerate Session ID
                session_regenerate_id(true);

                // Store Student Details
                $_SESSION["student_id"] = $student["id"];
                $_SESSION["student_name"] = $student["name"];
                $_SESSION["student_email"] = $student["email"];

                // Rotate CSRF Token
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                header("Location: dashboard.php");
                exit;

            } else {

                $message = "Invalid email or password.";

            }

        } else {

            $message = "Invalid email or password.";

        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Login | Readify</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f6fb;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.login-box {
    background: white;
    width: 90%;
    max-width: 430px;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}

h1 {
    color: #172554;
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    color: #64748b;
    margin-bottom: 25px;
}

label {
    display: block;
    margin: 15px 0 7px;
    color: #172554;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}

button {
    width: 100%;
    margin-top: 22px;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: #172554;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #f59e0b;
}

.message {
    text-align: center;
    color: #dc2626;
    margin-bottom: 15px;
}

.register-link {
    text-align: center;
    margin-top: 20px;
}

.register-link a {
    color: #f59e0b;
    text-decoration: none;
}
</style>

</head>

<body>

<div class="login-box">

    <h1>Readify 📚</h1>

    <p class="subtitle">Welcome Back, Student!</p>

    <?php if ($message !== ""): ?>

        <p class="message">
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Email Address</label>

        <input
            type="email"
            name="email"
            required
            maxlength="150"
            autocomplete="email"
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            required
            maxlength="72"
            autocomplete="current-password"
        >

        <button type="submit">Login</button>

    </form>

    <p class="register-link">
        Don't have an account?
        <a href="register.php">Register Now</a>
    </p>

</div>

</body>
</html>
