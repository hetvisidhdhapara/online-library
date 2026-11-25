
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// CSRF security token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $message = "Invalid request. Please try again.";
        $messageType = "error";

    } else {

        $title = trim($_POST["title"] ?? "");
        $author = trim($_POST["author"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");

        if ($title === "") {
            $message = "Book title is required.";
            $messageType = "error";

        } elseif (
            !isset($_FILES["pdf_file"]) ||
            $_FILES["pdf_file"]["error"] !== UPLOAD_ERR_OK
        ) {
            $message = "Please select a valid PDF file.";
            $messageType = "error";

        } elseif ($_FILES["pdf_file"]["size"] > 10 * 1024 * 1024) {
            $message = "PDF file must be smaller than 10 MB.";
            $messageType = "error";

        } else {

            $originalName = $_FILES["pdf_file"]["name"];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES["pdf_file"]["tmp_name"]);
            finfo_close($fileInfo);

            if ($extension !== "pdf" || $mimeType !== "application/pdf") {
                $message = "Only valid PDF files are allowed.";
                $messageType = "error";

            } else {

                $uploadDir = __DIR__ . "/../uploads/books/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = bin2hex(random_bytes(16)) . ".pdf";
                $filePath = $uploadDir . $fileName;
                $databasePath = "uploads/books/" . $fileName;

                if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $filePath)) {

                    $stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO books (title, author, category, description, pdf_file)
                         VALUES (?, ?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssss",
                        $title,
                        $author,
                        $category,
                        $description,
                        $databasePath
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Book added successfully!";
                        $messageType = "success";
                        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
                    } else {
                        unlink($filePath);
                        $message = "Database error. Please try again.";
                        $messageType = "error";
                    }

                    mysqli_stmt_close($stmt);

                } else {
                    $message = "File upload failed. Check folder permissions.";
                    $messageType = "error";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book | Readify</title>
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 14px;
            max-width: 750px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce1ea;
            border-radius: 8px;
            font-size: 15px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 110px;
        }

        .submit-btn {
            background: #14213d;
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .message {
            padding: 13px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .success {
            background: #e4f8eb;
            color: #176b36;
        }

        .error {
            background: #ffe9e7;
            color: #a52b22;
        }
    </style>
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="logo">Readify.</div>

        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="add_book.php" class="active">➕ Add New Book</a>
            <a href="manage_books.php">📚 Manage Books</a>
            <a href="manage_students.php">👥 Manage Students</a>
            <a href="profile.php">👤 Admin Profile</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Add New Book</h1>
                <p class="welcome">Add a new book to your Readify library.</p>
            </div>
        </header>

        <div class="form-card">

            <?php if ($message !== ""): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <input type="hidden" name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" required
                           placeholder="Enter book title">
                </div>

                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" name="author"
                           placeholder="Enter author name">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">Select Category</option>
                        <option value="Programming">Programming</option>
                        <option value="Science">Science</option>
                        <option value="Story">Story</option>
                        <option value="Technology">Technology</option>
                        <option value="Education">Education</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Book Description</label>
                    <textarea name="description"
                              placeholder="Write a short description"></textarea>
                </div>

                <div class="form-group">
                    <label>Upload PDF *</label>
                    <input type="file" name="pdf_file"
                           accept=".pdf,application/pdf" required>
                    <small>Only PDF files. Maximum size: 10 MB.</small>
                </div>

                <button type="submit" class="submit-btn">
                    Upload Book
                </button>

            </form>
        </div>

    </main>
</div>

</body>
</html>
