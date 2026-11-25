<?php

include "config/db.php";

// Fetch all books from database
$sql = "SELECT * FROM books ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Readify - Digital Library</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

<!-- Navbar -->
<header class="navbar">
    <a href="index.php" class="logo">Read<span>ify.</span></a>

    <nav>
        <a href="#home">Home</a>
        <a href="#categories">Categories</a>
        <a href="#books">Explore Books</a>
        <a href="#about">About</a>
    </nav>

    <div class="nav-buttons">
        <a href="student/login.php" class="login">Login</a>
        <a href="student/register.php" class="signup">Join Now</a>
    </div>
</header>

<!-- Hero Section -->
<section class="hero" id="home">

    <div class="hero-content">
        <span class="tag">YOUR DIGITAL KNOWLEDGE HUB</span>

        <h1>
            Discover a World<br>
            of <span>Knowledge.</span>
        </h1>

        <p>
            Explore thousands of books, expand your knowledge
            and make learning a part of your everyday life.
        </p>

        <div class="search-box">
            <input type="text" id="bookSearch" placeholder="Search your favourite books...">
            <select id="categoryFilter">
                <option value="all">All Categories</option>
                <option value="programming">Programming</option>
                <option value="science">Science</option>
                <option value="story">Stories</option>
            </select>
            <button type="button" id="searchBtn">Search</button>
        </div>

        <div class="hero-info">
            <span>📚 Digital Books</span>
            <span>🎓 Learn Anytime</span>
            <span>🌍 Easy Access</span>
        </div>
    </div>

    <div class="hero-visual">
        <div class="book book-one">
            <span>THE ART OF</span>
            <h3>LEARNING</h3>
            <small>Knowledge for everyone</small>
        </div>

        <div class="book book-two">
            <span>EXPLORE</span>
            <h3>THE WORLD</h3>
            <small>Discover something new</small>
        </div>

        <div class="floating-note">
            <strong>Keep Reading!</strong>
            <p>Your next chapter starts here.</p>
        </div>
    </div>

</section>

<!-- Categories -->
<section class="section" id="categories">

    <div class="section-heading">
        <div>
            <span class="tag">EXPLORE & LEARN</span>
            <h2>Browse Categories</h2>
        </div>
        <a href="#books" class="view-all">Explore All →</a>
    </div>

    <div class="category-grid">
        <div class="category-card">
            <span>💻</span>
            <h3>Technology</h3>
            <p>Programming & IT</p>
        </div>

        <div class="category-card">
            <span>🔬</span>
            <h3>Science</h3>
            <p>Discover & Explore</p>
        </div>

        <div class="category-card">
            <span>📊</span>
            <h3>Business</h3>
            <p>Business & Finance</p>
        </div>

        <div class="category-card">
            <span>📖</span>
            <h3>Education</h3>
            <p>Study Materials</p>
        </div>

        <div class="category-card">
            <span>🎨</span>
            <h3>Arts</h3>
            <p>Creativity & Design</p>
        </div>

        <div class="category-card">
            <span>🌱</span>
            <h3>Self Growth</h3>
            <p>Learn & Improve</p>
        </div>
    </div>

</section>

<!-- Featured Books -->
<section class="books-section" id="books">

    <div class="section-heading">
        <div>
            <span class="tag">HANDPICKED FOR YOU</span>
            <h2>Popular Books</h2>
        </div>

        <a href="student/books.php" class="view-all">
            View All →
        </a>
    </div>

    <div class="book-grid">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($book = mysqli_fetch_assoc($result)): ?>

                <div class="book-card"
                     data-category="<?php echo htmlspecialchars(strtolower($book['category'] ?? '')); ?>">

                    <div class="cover cover-blue">

                        <span>READIFY</span>

                        <h3 class="book-title">
                            <?php echo htmlspecialchars($book['title']); ?>
                        </h3>

                        <small>EXPLORE MORE</small>

                    </div>

                    <div class="book-details">

                        <h3>
                            <?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($book['category'] ?? 'General'); ?>
                        </p>

                        <p>
                            <?php echo htmlspecialchars($book['description'] ?? ''); ?>
                        </p>

                        <a href="student/books.php">Read Book →</a>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No books available yet.</p>

        <?php endif; ?>

    </div>

    <p id="noBooksMessage" style="display: none;">
        No books found. Try another search!
    </p>

</section>

<p id="noBooksMessage" style="display: none;">
    No books found. Try another search!
</p>

<!-- About -->
<section class="about" id="about">
    <span class="tag">ABOUT READIFY</span>
    <h2>Knowledge Should Be Accessible to Everyone.</h2>
    <p>
        Readify is a digital library platform designed to make
        learning easier. Discover, explore and read educational
        resources in one place.
    </p>
    <a href="student/register.php" class="signup">Start Reading →</a>
</section>

<!-- Footer -->
<footer>
    <a href="index.php" class="logo">Read<span>ify.</span></a>
    <p>Learn something new. Every single day.</p>
    <p>© 2026 Readify Digital Library. All rights reserved.</p>
</footer>

<script src="assets/js/script.js"></script>

</body>
</html>
