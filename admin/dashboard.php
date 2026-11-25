

<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Total Books
$countQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books");
$countData = mysqli_fetch_assoc($countQuery);
$totalBooks = $countData["total"];

// Total Students
$studentQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$studentData = mysqli_fetch_assoc($studentQuery);
$totalStudents = $studentData["total"];

// Total Categories
$categoryQuery = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT category) AS total
     FROM books
     WHERE category IS NOT NULL AND category != ''"
);
$categoryData = mysqli_fetch_assoc($categoryQuery);
$totalCategories = $categoryData["total"];

// Book Search
$bookSearch = trim($_GET["book_search"] ?? "");

if ($bookSearch !== "") {

    $searchTerm = "%" . $bookSearch . "%";

    $recentStmt = mysqli_prepare(
        $conn,
        "SELECT title, author, category
         FROM books
         WHERE title LIKE ? OR author LIKE ? OR category LIKE ?
         ORDER BY id DESC
         LIMIT 5"
    );

    mysqli_stmt_bind_param(
        $recentStmt,
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    mysqli_stmt_execute($recentStmt);
    $recentQuery = mysqli_stmt_get_result($recentStmt);

} else {

    $recentQuery = mysqli_query(
        $conn,
        "SELECT title, author, category
         FROM books
         ORDER BY id DESC
         LIMIT 5"
    );
}

// Recently Registered Students
$recentStudentsQuery = mysqli_query(
    $conn,
    "SELECT name, email, created_at
     FROM students
     ORDER BY id DESC
     LIMIT 5"
);

// Recent Activity - Books
$activityBooksQuery = mysqli_query(
    $conn,
    "SELECT title, created_at
     FROM books
     ORDER BY id DESC
     LIMIT 5"
);

// Recent Activity - Students
$activityStudentsQuery = mysqli_query(
    $conn,
    "SELECT name, created_at
     FROM students
     ORDER BY id DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .dashboard-search {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .dashboard-search input {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
        }

        .dashboard-search button {
            padding: 12px 20px;
            background: #172554;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .dashboard-search .reset-search {
            padding: 12px 20px;
            background: #e2e8f0;
            color: #172554;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .search-info {
            color: #64748b;
            margin-bottom: 15px;
        }

/* Dashboard Statistics Enhancement */
.stats {
    gap: 20px;
}

.stat-card {
    position: relative;
    overflow: hidden;
    border: 1px solid #e8edf5;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 25px rgba(20, 33, 61, 0.10);
}

.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff3e8;
    border-radius: 12px;
    font-size: 24px;
    margin-bottom: 12px;
}

.stat-card h3 {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 8px;
}

.stat-card h2 {
    color: #14213d;
    font-size: 28px;
    font-weight: 700;
}

/* Quick Action Enhancement */
.quick-grid {
    gap: 18px;
}

.quick-card {
    border: 1px solid #e8edf5;
    border-radius: 14px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.quick-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 22px rgba(20, 33, 61, 0.10);
}

.quick-card h3 {
    color: #14213d;
    margin-bottom: 10px;
}

.quick-card p {
    color: #64748b;
    line-height: 1.6;
}

/* Recent Activity Design */
.recent-activity {
    margin-bottom: 25px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    transition: 0.3s ease;
}

.activity-item:hover {
    background: #fff3e8;
    transform: translateX(5px);
}

.activity-icon {
    width: 45px;
    height: 45px;
    min-width: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff3e8;
    border-radius: 12px;
    font-size: 22px;
}

.activity-details h3 {
    color: #14213d;
    font-size: 15px;
    margin-bottom: 5px;
}

.activity-details p {
    color: #475569;
    font-size: 14px;
    margin-bottom: 5px;
}

.activity-details small {
    color: #94a3b8;
    font-size: 12px;
}


    </style>
</head>

<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="logo">Readify.</div>

        <nav>
            <a href="dashboard.php" class="active">🏠 Dashboard</a>
            <a href="add_book.php">➕ Add New Book</a>
            <a href="manage_books.php">📚 Manage Books</a>
            <a href="manage_students.php">👨‍🎓 Manage Students</a>
            <a href="profile.php">👤 Admin Profile</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">

            <div>
                <h1>Dashboard</h1>

                <p class="welcome">
                    Welcome back,
                    <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>!
                </p>
            </div>

            <a href="add_book.php" class="action-btn">
                + Add New Book
            </a>

        </header>

        <!-- Statistics -->
        <section class="stats">

            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <h3>Total Books</h3>
                <h2><?php echo $totalBooks; ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👨‍🎓</div>
                <h3>Total Students</h3>
                <h2><?php echo $totalStudents; ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📂</div>
                <h3>Total Categories</h3>
                <h2><?php echo $totalCategories; ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🛠️</div>
                <h3>Admin Panel</h3>
                <h2>Active</h2>
            </div>

        </section>

        <!-- Recently Added Books -->
        <section class="section-card">

            <h2>Recently Added Books</h2>

            <!-- Search Form -->
            <form method="GET" action="dashboard.php" class="dashboard-search">

                <input
                    type="text"
                    name="book_search"
                    placeholder="Search book, author or category..."
                    value="<?php echo htmlspecialchars($bookSearch); ?>"
                >

                <button type="submit">
                    🔍 Search
                </button>

                <a href="dashboard.php" class="reset-search">
                    Reset
                </a>

            </form>

            <p class="search-info">
                <?php if ($bookSearch !== ""): ?>
                    Search results for:
                    <strong><?php echo htmlspecialchars($bookSearch); ?></strong>
                <?php else: ?>
                    Showing the 5 most recently added books.
                <?php endif; ?>
            </p>

            <?php if (mysqli_num_rows($recentQuery) > 0): ?>

                <table class="book-table">

                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($book = mysqli_fetch_assoc($recentQuery)): ?>

                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($book["title"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($book["author"] ?? "Unknown"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($book["category"] ?? "General"); ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p class="empty-message">
                    No matching books found.
                </p>

            <?php endif; ?>

        </section>

        <!-- Recently Registered Students -->
        <section class="section-card">

            <h2>Recently Registered Students 🎓</h2>

            <?php if (mysqli_num_rows($recentStudentsQuery) > 0): ?>

                <table class="book-table">

                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($student = mysqli_fetch_assoc($recentStudentsQuery)): ?>

                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($student["name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($student["email"]); ?>
                                </td>

                                <td>
                                    <?php echo date("d M Y", strtotime($student["created_at"])); ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p class="empty-message">No students registered yet.</p>

            <?php endif; ?>

        </section>

<!-- Recent Activity -->
<section class="section-card recent-activity">

    <h2>Recent Activity 🕒</h2>

    <div class="activity-list">

        <?php if (
            mysqli_num_rows($activityBooksQuery) > 0 ||
            mysqli_num_rows($activityStudentsQuery) > 0
        ): ?>

            <?php while ($activity = mysqli_fetch_assoc($activityBooksQuery)): ?>

                <div class="activity-item">
                    <div class="activity-icon">📚</div>

                    <div class="activity-details">
                        <h3>New Book Added</h3>
                        <p>
                            <?php echo htmlspecialchars($activity["title"]); ?>
                        </p>
                        <small>
                            <?php echo date("d M Y", strtotime($activity["created_at"])); ?>
                        </small>
                    </div>
                </div>

            <?php endwhile; ?>

            <?php while ($activity = mysqli_fetch_assoc($activityStudentsQuery)): ?>

                <div class="activity-item">
                    <div class="activity-icon">🎓</div>

                    <div class="activity-details">
                        <h3>New Student Registered</h3>
                        <p>
                            <?php echo htmlspecialchars($activity["name"]); ?>
                        </p>
                        <small>
                            <?php echo date("d M Y", strtotime($activity["created_at"])); ?>
                        </small>
                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty-message">No recent activity available.</p>

        <?php endif; ?>

    </div>

</section>


        <!-- Admin Quick Actions -->
        <section class="quick-actions">

            <h2>Quick Actions ⚡</h2>

            <div class="quick-grid">

                <a href="add_book.php" class="quick-card">
                    <h3>➕ Add New Book</h3>
                    <p>Upload a new book to Readify library.</p>
                </a>

                <a href="manage_books.php" class="quick-card">
                    <h3>📚 Manage Books</h3>
                    <p>View and manage all library books.</p>
                </a>

                <a href="../index.php" class="quick-card">
                    <h3>🌐 Visit Website</h3>
                    <p>Preview the student-facing website.</p>
                </a>

            </div>

        </section>

    </main>

</div>

</body>
</html>

<?php
if (isset($recentStmt)) {
    mysqli_stmt_close($recentStmt);
}
?>
