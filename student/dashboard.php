
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

$studentId = (int)$_SESSION["student_id"];

// Total Available Books
$sql = "SELECT COUNT(*) AS total_books FROM books";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
$totalBooks = $data["total_books"];

// Total Categories
$categorySql = "SELECT COUNT(DISTINCT category) AS total_categories
                FROM books
                WHERE category IS NOT NULL AND category != ''";

$categoryResult = mysqli_query($conn, $categorySql);
$categoryData = mysqli_fetch_assoc($categoryResult);
$totalCategories = $categoryData["total_categories"];

// Total Books Read
$readSql = "SELECT COUNT(*) AS total_read
            FROM reading_history
            WHERE student_id = ?";

$readStmt = mysqli_prepare($conn, $readSql);
mysqli_stmt_bind_param($readStmt, "i", $studentId);
mysqli_stmt_execute($readStmt);

$readResult = mysqli_stmt_get_result($readStmt);
$readData = mysqli_fetch_assoc($readResult);
$totalRead = $readData["total_read"];

mysqli_stmt_close($readStmt);

// Total Favourite Books
$favoriteSql = "SELECT COUNT(*) AS total_favorites
                FROM bookmarks
                WHERE student_id = ?";

$favoriteStmt = mysqli_prepare($conn, $favoriteSql);
mysqli_stmt_bind_param($favoriteStmt, "i", $studentId);
mysqli_stmt_execute($favoriteStmt);

$favoriteResult = mysqli_stmt_get_result($favoriteStmt);
$favoriteData = mysqli_fetch_assoc($favoriteResult);
$totalFavorites = $favoriteData["total_favorites"];

mysqli_stmt_close($favoriteStmt);

// Recently Read Books
$recentReadSql = "SELECT books.*, reading_history.read_at
                  FROM reading_history
                  INNER JOIN books
                  ON reading_history.book_id = books.id
                  WHERE reading_history.student_id = ?
                  ORDER BY reading_history.read_at DESC
                  LIMIT 3";

$recentReadStmt = mysqli_prepare($conn, $recentReadSql);

mysqli_stmt_bind_param(
    $recentReadStmt,
    "i",
    $studentId
);

mysqli_stmt_execute($recentReadStmt);
$recentReadResult = mysqli_stmt_get_result($recentReadStmt);

// Recently Added Books
$recentSql = "SELECT * FROM books ORDER BY id DESC LIMIT 3";
$recentResult = mysqli_query($conn, $recentSql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard | Readify</title>

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

        /* Sidebar */

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #172554;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-size: 28px;
            margin-bottom: 45px;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .sidebar nav a {
            color: white;
            text-decoration: none;
            padding: 14px;
            border-radius: 8px;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: #f59e0b;
        }

        .sidebar-footer {
            margin-top: auto;
            color: #cbd5e1;
            line-height: 1.8;
        }

        /* Main Container */

        .container {
            margin-left: 250px;
            padding: 45px;
        }

        /* Welcome Section */

        .welcome {
            margin-bottom: 35px;
        }

        .welcome h1 {
            font-size: 34px;
            margin-bottom: 12px;
        }

        .welcome p {
            color: #64748b;
            font-size: 16px;
        }

        /* Statistics Cards */

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 22px;
            margin-top: 25px;
        }

        .dashboard-card {
            background: white;
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .dashboard-card h3 {
            color: #64748b;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .dashboard-card h1 {
            font-size: 42px;
            color: #f59e0b;
        }

        /* Recent Books Sections */

        .recent-books {
            margin-top: 40px;
        }

        .recent-books h2 {
            margin-bottom: 10px;
        }

        .recent-books > p {
            color: #64748b;
            margin-bottom: 20px;
        }

        .recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .recent-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .recent-card h3 {
            margin-bottom: 15px;
        }

        .recent-card p {
            color: #64748b;
            margin-bottom: 12px;
        }

        .recent-card a {
            display: inline-block;
            margin-top: 10px;
            color: #172554;
            font-weight: bold;
            text-decoration: none;
        }

        .recent-card a:hover {
            color: #f59e0b;
        }

        /* Browse Section */

        
/* Quick Access Cards */

.quick-access {
    margin-top: 40px;
}

.quick-access h2 {
    margin-bottom: 20px;
}

.quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.quick-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    text-decoration: none;
    color: #172554;
    box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    transition: 0.3s;
}

.quick-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.10);
}

.quick-card h3 {
    margin-bottom: 10px;
}

.quick-card p {
    color: #64748b;
}

        .browse {
            margin-top: 40px;
        }

        .browse p {
            margin: 12px 0 20px;
        }

        .browse a {
            display: inline-block;
            background: #172554;
            color: white;
            text-decoration: none;
            padding: 14px 24px;
            border-radius: 8px;
        }

        .browse a:hover {
            background: #f59e0b;
        }

        /* Responsive Design */

        @media (max-width: 1100px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
            }

            .logo {
                margin-bottom: 20px;
            }

            .sidebar nav {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .container {
                margin-left: 0;
                padding: 25px 20px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .welcome h1 {
                font-size: 27px;
            }
        }

    </style>

</head>

<body>

<!-- Sidebar -->

<div class="sidebar">

    <h2 class="logo">Readify</h2>

    <nav>
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="books.php">📚 Browse Books</a>
        <a href="../index.php">🌐 Home</a>
        <a href="favorites.php">⭐ My Favorites</a>
        <a href="reading_history.php">📖 Reading History</a>
        <a href="profile.php">👤 My Profile</a>
        <a href="change_password.php">🔐 Change Password</a>
        <a href="logout.php">🚪 Logout</a>
    </nav>

    <div class="sidebar-footer">
        <p>Readify Library</p>
        <small>Learn • Explore • Grow</small>
    </div>

</div>

<!-- Main Dashboard -->

<div class="container">

    <section class="welcome">

        <h1>Welcome to Readify! 📚</h1>

        <p>
            Your learning journey starts here.
            Explore books and discover knowledge.
        </p>

    </section>

    <!-- Dashboard Statistics -->

    <div class="stats-container">

        <div class="dashboard-card">
            <h3>Total Available Books</h3>
            <h1><?php echo $totalBooks; ?></h1>
        </div>

        <div class="dashboard-card">
            <h3>Total Categories</h3>
            <h1><?php echo $totalCategories; ?></h1>
        </div>

        <div class="dashboard-card">
            <h3>Books Read 📖</h3>
            <h1><?php echo $totalRead; ?></h1>
        </div>

        <div class="dashboard-card">
            <h3>My Favorites ⭐</h3>
            <h1><?php echo $totalFavorites; ?></h1>
        </div>

    </div>

    <!-- Recently Read Books -->

    <section class="recent-books">

        <h2>Recently Read Books 📖</h2>

        <p>Continue your learning journey.</p>

        <div class="recent-grid">

            <?php if (mysqli_num_rows($recentReadResult) > 0): ?>

                <?php while ($book = mysqli_fetch_assoc($recentReadResult)): ?>

                    <div class="recent-card">

                        <h3>
                            <?php echo htmlspecialchars($book["title"]); ?>
                        </h3>

                        <p>
                            Author:
                            <?php echo htmlspecialchars($book["author"] ?? "Unknown"); ?>
                        </p>

                        <p>
                            Last Read:
                            <?php echo date(
                                "d M Y, h:i A",
                                strtotime($book["read_at"])
                            ); ?>
                        </p>

                        <a href="read.php?id=<?php echo (int)$book["id"]; ?>">
                            Read Again →
                        </a>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p>You haven't read any books yet.</p>

            <?php endif; ?>

        </div>

    </section>

    <!-- Recently Added Books -->

    <section class="recent-books">

        <h2>Recently Added Books 📚</h2>

        <p>Explore the latest books added to our library.</p>

        <div class="recent-grid">

            <?php if (mysqli_num_rows($recentResult) > 0): ?>

                <?php while ($book = mysqli_fetch_assoc($recentResult)): ?>

                    <div class="recent-card">

                        <h3>
                            <?php echo htmlspecialchars($book["title"]); ?>
                        </h3>

                        <p>
                            Author:
                            <?php echo htmlspecialchars($book["author"] ?? "Unknown"); ?>
                        </p>

                        <p>
                            Category:
                            <?php echo htmlspecialchars($book["category"] ?? "General"); ?>
                        </p>

                        <?php if (!empty($book["pdf_file"])): ?>

                            <a href="read.php?id=<?php echo (int)$book["id"]; ?>">
                                Read Online →
                            </a>

                        <?php else: ?>

                            <p>PDF not available</p>

                        <?php endif; ?>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p>No books available yet.</p>

            <?php endif; ?>

        </div>

    </section>

<!-- Quick Access Section -->

<section class="quick-access">

    <h2>Quick Access ⚡</h2>

    <div class="quick-grid">

        <a href="books.php" class="quick-card">
            <h3>📚 Browse Books</h3>
            <p>Explore our complete book collection.</p>
        </a>

        <a href="favorites.php" class="quick-card">
            <h3>⭐ My Favorites</h3>
            <p>Access your saved favourite books.</p>
        </a>

        <a href="reading_history.php" class="quick-card">
            <h3>📖 Reading History</h3>
            <p>Continue reading your previously opened books.</p>
        </a>

    </div>

</section>


    <!-- Browse Books -->

    <section class="browse">

        <h2>Ready to Explore?</h2>

        <p>
            Discover books from different categories
            and start reading.
        </p>

        <a href="books.php">Browse All Books →</a>

    </section>

</div>

</body>
</html>
