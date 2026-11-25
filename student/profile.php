
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

$studentId = $_SESSION["student_id"];

$sql = "SELECT name, email, created_at FROM students WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile | Readify</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f6fb;
    color: #172554;
}

.profile-container {
    max-width: 750px;
    margin: 60px auto;
    padding: 20px;
}

.profile-card {
    background: white;
    padding: 40px;
    border-radius: 18px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.07);
}

.profile-heading {
    text-align: center;
    margin-bottom: 35px;
}

.profile-icon {
    width: 90px;
    height: 90px;
    background: #172554;
    color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 38px;
    margin: 0 auto 18px;
}

.profile-heading h1 {
    margin-bottom: 8px;
}

.profile-heading p {
    color: #64748b;
}

.profile-info {
    margin-top: 20px;
}

.info-item {
    padding: 18px;
    border-bottom: 1px solid #e2e8f0;
}

.info-item h4 {
    color: #64748b;
    margin-bottom: 8px;
}

.info-item p {
    font-size: 17px;
    font-weight: bold;
}

.back-btn {
    display: inline-block;
    margin-top: 25px;
    padding: 13px 22px;
    background: #172554;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.back-btn:hover {
    background: #f59e0b;
}

@media(max-width: 600px) {
    .profile-card {
        padding: 25px;
    }
}
.edit-btn {
    display: inline-block;
    padding: 10px 20px;
    background: #14213d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    margin-top: 15px;
}

.edit-btn:hover {
    background: #fca311;
}
</style>
</head>

<body>

<div class="profile-container">

    <div class="profile-card">

        <div class="profile-heading">

            <div class="profile-icon">👤</div>

            <h1>
                <?php echo htmlspecialchars($student["name"]); ?>
            </h1>

            <p>Welcome to your Readify Profile</p>

        </div>

        <div class="profile-info">

            <div class="info-item">
                <h4>Full Name</h4>
                <p><?php echo htmlspecialchars($student["name"]); ?></p>
            </div>

            <div class="info-item">
                <h4>Email Address</h4>
                <p><?php echo htmlspecialchars($student["email"]); ?></p>
            </div>
            <a href="edit_profile.php" class="edit-btn">
    ✏️ Edit Profile
</a>

            <div class="info-item">
                <h4>Account Created</h4>
                <p>
                    <?php
                    echo date("d M Y", strtotime($student["created_at"]));
                    ?>
                </p>
            </div>

        </div>

        <a href="dashboard.php" class="back-btn">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>
</html>
