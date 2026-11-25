
<?php
session_start();

include "../config/db.php";

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Handle Favourite Book
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_bookmark"])
) {
    if (!isset($_SESSION["student_id"])) {
        header("Location: login.php");
        exit;
    }

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please try again.");
    }

    $studentId = (int)$_SESSION["student_id"];
    $bookId = (int)$_POST["book_id"];

    if ($bookId > 0) {
        $bookmarkStmt = mysqli_prepare(
            $conn,
            "INSERT IGNORE INTO bookmarks (student_id, book_id)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $bookmarkStmt,
            "ii",
            $studentId,
            $bookId
        );

        mysqli_stmt_execute($bookmarkStmt);
        mysqli_stmt_close($bookmarkStmt);
    }

    header("Location: books.php?bookmark=added");
    exit;
}

// Get Books with Average Rating
$sql = "
    SELECT
        books.*,
        COALESCE(AVG(book_reviews.rating), 0) AS average_rating,
        COUNT(book_reviews.id) AS total_reviews
    FROM books
    LEFT JOIN book_reviews
        ON books.id = book_reviews.book_id
    GROUP BY books.id
    ORDER BY books.id DESC
";

$result = mysqli_query($conn, $sql);

// Get Categories
$categoryQuery = mysqli_query(
    $conn,
    "SELECT DISTINCT category FROM books
     WHERE category IS NOT NULL AND category != ''
     ORDER BY category ASC"
);

// Get all written reviews
$reviewsQuery = mysqli_query(
    $conn,
    "SELECT
        book_reviews.book_id,
        book_reviews.rating,
        book_reviews.review,
        book_reviews.created_at,
        students.name AS student_name
     FROM book_reviews
     INNER JOIN students
        ON book_reviews.student_id = students.id
     ORDER BY book_reviews.created_at DESC"
);

$reviewsByBook = [];

if ($reviewsQuery) {
    while ($reviewRow = mysqli_fetch_assoc($reviewsQuery)) {
        $reviewsByBook[$reviewRow["book_id"]][] = $reviewRow;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Explore Books | Readify</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fc;
            color: #14213d;
        }

        .navbar {
            background: #14213d;
            padding: 22px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .logo {
            font-size: 27px;
            font-weight: bold;
            color: #ffad60;
        }

        .navbar a {
            color: white;
            text-decoration: none;
        }

        .hero {
            text-align: center;
            padding: 65px 20px 35px;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .hero p {
            color: #68758b;
        }

        .book-container {
            width: 86%;
            margin: 30px auto 70px;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-cover {
            height: 190px;
            background: linear-gradient(135deg, #14213d, #34527d);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffad60;
            font-size: 25px;
            font-weight: bold;
            padding: 20px;
            text-align: center;
        }

        .book-info {
            padding: 22px;
        }

        .book-info h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .category {
            display: inline-block;
            background: #fff0df;
            color: #a85b17;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .book-info p {
            color: #68758b;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .read-btn,
        .review-btn {
            display: inline-block;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
        }

        .read-btn {
            background: #14213d;
            color: white;
        }

        .review-btn {
            background: #fff0df;
            color: #a85b17;
        }

        .favorite-btn {
            display: inline-block;
            background: #fff0df;
            color: #a85b17;
            border: none;
            padding: 11px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .favorite-btn:hover {
            background: #ffad60;
            color: #14213d;
        }

        .rating {
            color: #d97706;
            font-weight: bold;
            margin: 10px 0;
        }

        .reviews-section {
            border-top: 1px solid #e8edf5;
            margin-top: 20px;
            padding-top: 18px;
        }

        .reviews-section h4 {
            color: #14213d;
            margin-bottom: 14px;
            font-size: 16px;
        }

        .review-item {
            background: #f8fafc;
            padding: 13px;
            border-radius: 9px;
            margin-bottom: 12px;
            border: 1px solid #e8edf5;
        }

        .review-student {
            font-weight: bold;
            color: #14213d;
            margin-bottom: 6px;
        }

        .review-stars {
            color: #d97706;
            font-size: 14px;
            margin-bottom: 7px;
        }

        .review-text {
            color: #475569;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .review-date {
            display: block;
            color: #94a3b8;
            font-size: 11px;
            margin-top: 8px;
        }

        .no-review {
            color: #94a3b8;
            font-size: 13px;
        }

        .empty {
            text-align: center;
            color: #68758b;
            padding: 50px;
        }

        footer {
            background: #14213d;
            color: white;
            text-align: center;
            padding: 22px;
        }

        .search-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            max-width: 750px;
            margin: 30px auto 0;
            padding: 0 15px;
        }

        .search-container input,
        .search-container select {
            padding: 15px;
            border: 1px solid #dce1ea;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
            background: white;
        }

        .search-container input {
            flex: 1;
            min-width: 0;
        }

        .search-container select {
            width: 190px;
        }

        .search-container input:focus,
        .search-container select:focus {
            border-color: #ffad60;
        }

        .no-results {
            display: none;
            text-align: center;
            color: #68758b;
            padding: 35px;
            grid-column: 1 / -1;
        }

        .success-message {
            width: 86%;
            margin: 20px auto;
            padding: 14px;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            text-align: center;
        }

        @media(max-width: 600px) {
            .hero h1 {
                font-size: 28px;
            }

            .book-container {
                width: 92%;
            }

            .search-container {
                flex-direction: column;
            }

            .search-container select {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="logo">Readify.</div>
        <a href="../index.php">Home</a>
    </nav>

    <section class="hero">
        <h1>Explore Our Library</h1>
        <p>Discover knowledge, explore books and start reading.</p>

        <div class="search-container">

            <input
                type="text"
                id="bookSearch"
                placeholder="Search books or authors..."
            >

            <select id="categoryFilter">
                <option value="">All Categories</option>

                <?php while ($category = mysqli_fetch_assoc($categoryQuery)): ?>

                    <option value="<?php echo htmlspecialchars(strtolower($category["category"])); ?>">
                        <?php echo htmlspecialchars($category["category"]); ?>
                    </option>

                <?php endwhile; ?>
            </select>

        </div>
    </section>

    <?php if (isset($_GET["review"]) && $_GET["review"] === "success"): ?>
        <div class="success-message">
            Your review has been submitted successfully!
        </div>
    <?php endif; ?>

    <main class="book-container">

        <div class="book-grid">

            <p class="no-results" id="noResults">
                No books found. Try another search!
            </p>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($book = mysqli_fetch_assoc($result)): ?>

                    <div class="book-card"
                        data-title="<?php echo htmlspecialchars(strtolower($book['title'])); ?>"
                        data-author="<?php echo htmlspecialchars(strtolower($book['author'] ?? '')); ?>"
                        data-category="<?php echo htmlspecialchars(strtolower($book['category'] ?? '')); ?>">

                        <div class="book-cover">
                            READIFY
                        </div>

                        <div class="book-info">

                            <h3>
                                <?php echo htmlspecialchars($book["title"]); ?>
                            </h3>

                            <span class="category">
                                <?php echo htmlspecialchars($book["category"] ?? "General"); ?>
                            </span>

                            <p>
                                Author:
                                <?php echo htmlspecialchars($book["author"] ?? "Unknown"); ?>
                            </p>

                            <p>
                                <?php echo htmlspecialchars($book["description"] ?? "Explore this book in Readify."); ?>
                            </p>

                            <div class="rating">
                                ⭐ <?php echo number_format((float)$book["average_rating"], 1); ?>/5
                                (<?php echo (int)$book["total_reviews"]; ?> reviews)
                            </div>

                            <?php if (!empty($book["pdf_file"])): ?>

                                <a class="read-btn"
                                   href="read.php?id=<?php echo (int)$book["id"]; ?>">
                                    Read Online →
                                </a>

                            <?php else: ?>

                                <span>PDF not available</span>

                            <?php endif; ?>

                            <br>

                            <a class="review-btn"
                               href="add_review.php?book_id=<?php echo (int)$book["id"]; ?>">
                                ⭐ Rate & Review
                            </a>

                            <form method="POST" style="margin-top: 12px;">

                                <input
                                    type="hidden"
                                    name="book_id"
                                    value="<?php echo (int)$book["id"]; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
                                >

                                <button type="submit"
                                        name="add_bookmark"
                                        class="favorite-btn">
                                    ⭐ Add to Favorites
                                </button>

                            </form>

                            <!-- Student Reviews -->
                            <div class="reviews-section">

                                <h4>Student Reviews</h4>

                                <?php
                                $bookReviews = $reviewsByBook[$book["id"]] ?? [];
                                ?>

                                <?php if (count($bookReviews) > 0): ?>

                                    <?php foreach ($bookReviews as $review): ?>

                                        <div class="review-item">

                                            <div class="review-student">
                                                <?php echo htmlspecialchars($review["student_name"]); ?>
                                            </div>

                                            <div class="review-stars">
                                                <?php
                                                echo str_repeat("⭐", (int)$review["rating"]);
                                                ?>
                                            </div>

                                            <div class="review-text">
                                                <?php echo nl2br(htmlspecialchars($review["review"])); ?>
                                            </div>

                                            <small class="review-date">
                                                <?php echo date("d M Y", strtotime($review["created_at"])); ?>
                                            </small>

                                        </div>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <p class="no-review">
                                        No reviews yet. Be the first to review!
                                    </p>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="empty">No books available right now.</p>

            <?php endif; ?>

        </div>

    </main>

    <footer>
        © 2026 Readify. All Rights Reserved.
    </footer>

    <script src="../assets/js/script.js"></script>

</body>
</html>
