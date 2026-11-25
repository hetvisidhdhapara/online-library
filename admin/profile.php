
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

$adminId = $_SESSION["admin_id"];

// Fetch Admin Information
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, username, created_at
     FROM admins
     WHERE id = ?"
);

if (!$stmt) {
    die("Unable to fetch admin information.");
}

mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($conn);

if (!$admin) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Profile | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .profile-card {
            max-width: 650px;
            margin-top: 25px;
        }

        .profile-item {
            padding: 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .profile-item strong {
            display: block;
            color: #64748b;
            margin-bottom: 8px;
        }

        .profile-item span {
            color: #172554;
            font-size: 17px;
        }

        .profile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 25px;
        }

        .profile-btn {
            display: inline-block;
            padding: 13px 20px;
            border-radius: 8px;
            background: #172554;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .profile-btn:hover {
            background: #263b75;
            transform: translateY(-2px);
        }

        .password-btn {
            background: #f59e0b;
            color: #172554;
        }

        .password-btn:hover {
            background: #d97706;
        }

/* Center Admin Profile Card */

.main-content {
    display: flex;
    flex-direction: column;
}

.profile-card {
    width: 100%;
    max-width: 650px;
    margin: 40px auto !important;
    padding: 35px;
    border-radius: 16px;
    background: white;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
}

.profile-card h2 {
    text-align: center;
    color: #172554;
    font-size: 24px;
    margin-bottom: 25px;
}

.profile-item {
    padding: 20px;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
}

.profile-item strong {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.profile-item span {
    font-size: 18px;
    font-weight: 600;
}

.profile-actions {
    justify-content: center;
    margin-top: 30px;
}

@media (max-width: 600px) {
    .profile-card {
        padding: 22px 16px;
        margin: 25px auto !important;
    }

    .profile-actions {
        flex-direction: column;
    }

    .profile-btn {
        text-align: center;
    }
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
            <a href="manage_students.php">👨‍🎓 Manage Students</a>
            <a href="profile.php" class="active">👤 Admin Profile</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Admin Profile 👤</h1>
                <p class="welcome">Manage your Readify admin account.</p>
            </div>
        </header>

        <section class="section-card profile-card">

            <h2>Account Information</h2>

            <div class="profile-item">
                <strong>Admin ID</strong>
                <span>
                    <?php echo htmlspecialchars((string)$admin["id"], ENT_QUOTES, "UTF-8"); ?>
                </span>
            </div>

            <div class="profile-item">
                <strong>Admin Username</strong>
                <span>
                    <?php echo htmlspecialchars($admin["username"], ENT_QUOTES, "UTF-8"); ?>
                </span>
            </div>

            <div class="profile-item">
                <strong>Account Created</strong>
                <span>
                    <?php echo date("d M Y", strtotime($admin["created_at"])); ?>
                </span>
            </div>

            <div class="profile-actions">

                <a href="edit_profile.php" class="profile-btn">
                    ✏️ Edit Username
                </a>

                <a href="change_password.php" class="profile-btn password-btn">
                    🔒 Change Password
                </a>

            </div>

        </section>

    </main>

</div>

</body>
</html>
