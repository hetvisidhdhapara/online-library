
<?php
session_start();
include "../config/db.php";

$error = "";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $error = "Invalid request. Please try again.";
    } elseif (
        $username === "" ||
        strlen($username) > 100 ||
        $password === "" ||
        strlen($password) > 72
    ) {
        $error = "Invalid username or password!";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password
             FROM admins
             WHERE username = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $admin = mysqli_fetch_assoc($result);

            if ($admin && password_verify($password, $admin["password"])) {

                session_regenerate_id(true);

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Invalid username or password!";
            }

            mysqli_stmt_close($stmt);

        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Readify</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .login-container {
            width: 100%;
            max-width: 1000px;
            min-height: 570px;
            background: white;
            display: flex;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(15, 23, 42, 0.12);
        }

        /* Left Panel */

        .welcome-panel {
            width: 45%;
            background: linear-gradient(145deg, #14213d, #243b68);
            color: white;
            padding: 55px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-panel::before {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border: 35px solid rgba(255,255,255,0.06);
            border-radius: 50%;
            top: -90px;
            right: -90px;
        }

        .welcome-panel::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border: 30px solid rgba(245,158,11,0.12);
            border-radius: 50%;
            bottom: -80px;
            left: -70px;
        }

        .brand {
            font-size: 29px;
            font-weight: 800;
            margin-bottom: 65px;
            position: relative;
            z-index: 1;
        }

        .brand span {
            color: #f59e0b;
        }

        .admin-icon {
            width: 75px;
            height: 75px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .welcome-panel h1 {
            font-size: 32px;
            line-height: 1.3;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }

        .welcome-panel h1 span {
            color: #f59e0b;
        }

        .welcome-panel p {
            color: #cbd5e1;
            font-size: 15px;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }

        .panel-footer {
            margin-top: 55px;
            color: #94a3b8;
            font-size: 12px;
            position: relative;
            z-index: 1;
        }

        /* Right Login Panel */

        .form-panel {
            width: 55%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px;
        }

        .login-form {
            width: 100%;
            max-width: 380px;
        }

        .login-form h2 {
            color: #14213d;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .input-box {
            position: relative;
        }

        .input-box input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        .input-box input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
        }

        .password-input {
            padding-right: 65px !important;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #d97706;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: #14213d;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }

        .login-btn:hover {
            background: #f59e0b;
            color: #14213d;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(245,158,11,0.2);
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .secure-note {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 25px;
        }

        /* Responsive */

        @media (max-width: 700px) {

            body {
                padding: 15px;
            }

            .login-container {
                flex-direction: column;
                max-width: 450px;
            }

            .welcome-panel {
                width: 100%;
                padding: 30px;
                min-height: 240px;
            }

            .brand {
                margin-bottom: 25px;
                font-size: 25px;
            }

            .admin-icon {
                width: 55px;
                height: 55px;
                font-size: 27px;
                margin-bottom: 15px;
            }

            .welcome-panel h1 {
                font-size: 24px;
            }

            .welcome-panel p {
                font-size: 13px;
            }

            .panel-footer {
                margin-top: 20px;
            }

            .form-panel {
                width: 100%;
                padding: 35px 25px;
            }

            .login-form h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">

        <div class="welcome-panel">

            <div class="brand">
                Read<span>ify.</span>
            </div>

            <div class="admin-icon">
                🛡️
            </div>

            <h1>Admin <span>Portal</span></h1>

            <p>
                Manage your digital library with ease.
                Organize books, manage students and keep
                your library running smoothly.
            </p>

            <div class="panel-footer">
                © 2026 Readify Library. All rights reserved.
            </div>

        </div>

        <div class="form-panel">

            <div class="login-form">

                <h2>Welcome Back!</h2>

                <p class="subtitle">
                    Sign in to access your admin dashboard.
                </p>

                <?php if ($error !== ""): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8"); ?>"
                    >

                    <div class="form-group">

                        <label>Admin Username</label>

                        <div class="input-box">
                            <input
                                type="text"
                                name="username"
                                placeholder="Enter your username"
                                required
                                maxlength="100"
                                autocomplete="username"
                            >
                        </div>

                    </div>

                    <div class="form-group">

                        <label>Password</label>

                        <div class="input-box">

                            <input
                                type="password"
                                name="password"
                                id="adminPassword"
                                class="password-input"
                                placeholder="Enter your password"
                                required
                                maxlength="72"
                                autocomplete="current-password"
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword()"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <button type="submit" class="login-btn">
                        Login to Dashboard →
                    </button>

                </form>

                <p class="secure-note">
                    🔒 Secure Admin Access
                </p>

            </div>

        </div>

    </div>

    <script>
        function togglePassword() {

            const password = document.getElementById("adminPassword");
            const button = document.querySelector(".toggle-password");

            if (password.type === "password") {
                password.type = "text";
                button.textContent = "Hide";
            } else {
                password.type = "password";
                button.textContent = "Show";
            }
        }
    </script>

</body>
</html>
