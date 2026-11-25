
<?php
session_start();
include "../config/db.php";

$message = "";

// Create CSRF Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    // CSRF Validation
    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $message = "Invalid request. Please try again.";
    } elseif (
        $name === "" ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150
    ) {
        $message = "Please enter valid name and email details.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif (strlen($password) > 72) {
        $message = "Password is too long.";
    } else {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO students (name, email, password)
                VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $name,
            $email,
            $hashedPassword
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Registration successful! You can now login.";

            // Generate a new token after successful submission
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

        } else {

            if (mysqli_stmt_errno($stmt) === 1062) {
                $message = "This email is already registered.";
            } else {
                $message = "Registration failed. Please try again.";
            }
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

<title>Student Registration | Readify</title>

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

.register-box {
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
    color: #172554;
    margin-bottom: 15px;
    line-height: 1.5;
}

.login-link {
    text-align: center;
    margin-top: 20px;
}

.login-link a {
    color: #f59e0b;
    text-decoration: none;
}

</style>
</head>

<body>

<div class="register-box">

    <h1>Readify 📚</h1>

    <p class="subtitle">Create Your Student Account</p>

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

        <label>Full Name</label>

        <input
            type="text"
            name="name"
            required
            maxlength="100"
            autocomplete="name"
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
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
        >

        <button type="submit">Create Account</button>

    </form>

    <p class="login-link">
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>
