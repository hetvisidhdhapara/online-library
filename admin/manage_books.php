
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Create CSRF Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Search and Category Filter
$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

// Fetch All Categories
$categoryQuery = mysqli_query(
    $conn,
    "SELECT DISTINCT category FROM books
     WHERE category IS NOT NULL AND category != ''
     ORDER BY category ASC"
);

// Fetch Books with Filters
$sql = "SELECT * FROM books WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($category !== "") {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Books | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .section-card {
            overflow-x: auto;
        }

        .book-table {
            min-width: 650px;
        }

        .view-btn, .delete-btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .view-btn {
            background: #e8f0ff;
            color: #14213d;
        }

        .delete-btn {
            background: #ffe9e7;
            color: #b42318;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }

        .filter-container input,
        .filter-container select {
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }

        .search-btn {
            padding: 12px 20px;
            background: #172554;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .reset-btn {
            padding: 12px 20px;
            background: #e2e8f0;
            color: #172554;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .result-count {
            color: #64748b;
            margin-bottom: 15px;
        }
        .student-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
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
            <a href="manage_books.php" class="active">📚 Manage Books</a>
            <a href="manage_students.php">👥 Manage Students</a>
            <a href="profile.php">👤 Admin Profile</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>

    </aside>

    <main class="main-content">

        <header class="topbar">

            <div>
                <h1>Manage Books</h1>
                <p class="welcome">
                    View and manage all books in your Readify library.
                </p>
            </div>

            <a href="add_book.php" class="action-btn">
                + Add New Book
            </a>

        </header>

        <section class="section-card">

            <div class="student-heading">

    <h2>All Books</h2>

    <a href="export_books.php" class="action-btn">
        📥 Export CSV
    </a>

</div>

            <!-- Search and Category Filter -->
            <form method="GET" class="filter-container">

                <input
                    type="text"
                    name="search"
                    placeholder="Search title or author..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

                <select name="category">

                    <option value="">All Categories</option>

                    <?php while ($cat = mysqli_fetch_assoc($categoryQuery)): ?>

                        <option
                            value="<?php echo htmlspecialchars($cat["category"]); ?>"
                            <?php echo ($category === $cat["category"]) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($cat["category"]); ?>
                        </option>

                    <?php endwhile; ?>

                </select>

                <button type="submit" class="search-btn">
                    🔍 Search
                </button>

                <a href="manage_books.php" class="reset-btn">
                    Reset
                </a>

            </form>

            <p class="result-count">
                Total Results: <?php echo mysqli_num_rows($query); ?>
            </p>

            <?php if (mysqli_num_rows($query) > 0): ?>

                <table class="book-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>PDF</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($book = mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$book["id"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($book["title"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($book["author"] ?? "Unknown"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($book["category"] ?? "General"); ?>
                                </td>

                                <td>

                                    <?php if (!empty($book["pdf_file"])): ?>

                                        <a
                                            class="view-btn"
                                            href="../<?php echo htmlspecialchars($book["pdf_file"]); ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            View PDF
                                        </a>

                                    <?php else: ?>

                                        No PDF

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <form
                                        action="delete_book.php"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this book?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo (int)$book["id"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
                                        >

                                        <button type="submit" class="delete-btn">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p class="empty-message">
                    No books found. Try another search or category.
                </p>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
