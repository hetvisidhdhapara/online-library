
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

// Fetch All Students
$studentsQuery = mysqli_query(
    $conn,
    "SELECT id, name, email, created_at
     FROM students
     ORDER BY id DESC"
);

if (!$studentsQuery) {
    die("Database query failed.");
}

// Total Students
$totalStudents = mysqli_num_rows($studentsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Students | Readify</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .student-search {
            width: 100%;
            padding: 13px 15px;
            margin: 15px 0;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        .student-search:focus {
            border-color: #172554;
        }

        .student-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            color: #64748b;
        }

        .no-result {
            display: none;
            padding: 20px;
            text-align: center;
            color: #64748b;
        }

        .student-table-container {
            overflow-x: auto;
        }

        .book-table {
            min-width: 800px;
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

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="logo">Readify.</div>

        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="add_book.php">➕ Add New Book</a>
            <a href="manage_books.php">📚 Manage Books</a>
            <a href="manage_students.php" class="active">👨‍🎓 Manage Students</a>
            <a href="profile.php">👤 Admin Profile</a>
            <a href="../index.php">🌐 Visit Website</a>
            <a href="logout.php" class="logout">↪ Logout</a>
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">

            <div>
                <h1>Manage Students 🎓</h1>
                <p class="welcome">View all registered Readify students.</p>
            </div>

        </header>

        <section class="section-card">

            <div class="student-heading">

    <h2>All Registered Students</h2>

    <a href="export_students.php" class="action-btn">
        📥 Export CSV
    </a>

</div>

            <!-- Search Students -->
            <input
                type="text"
                id="studentSearch"
                class="student-search"
                placeholder="🔍 Search by ID, name or email..."
                autocomplete="off"
            >

            <div class="student-info">

                <span>
                    Total Students:
                    <strong><?php echo $totalStudents; ?></strong>
                </span>

                <span id="resultCount">
                    Showing <?php echo $totalStudents; ?> students
                </span>

            </div>

            <?php if ($totalStudents > 0): ?>

                <div class="student-table-container">

                    <table class="book-table">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php while ($student = mysqli_fetch_assoc($studentsQuery)): ?>

                                <tr>

                                    <td>
                                        <?php echo (int)$student["id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($student["name"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($student["email"]); ?>
                                    </td>

                                    <td>
                                        <?php echo date("d M Y", strtotime($student["created_at"])); ?>
                                    </td>

                                    <td>

                                        <a
                                            href="student_details.php?id=<?php echo (int)$student['id']; ?>"
                                            class="action-btn"
                                        >
                                            View Details
                                        </a>

                                        <a
                                            href="edit_student.php?id=<?php echo (int)$student['id']; ?>"
                                            class="action-btn"
                                        >
                                            Edit Student
                                        </a>

                                        <form
                                            method="POST"
                                            action="delete_student.php"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="student_id"
                                                value="<?php echo (int)$student['id']; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="action-btn"
                                                style="background:#dc2626; color:white; border:none; cursor:pointer;"
                                            >
                                                Delete Student
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                    <p class="no-result" id="noResult">
                        No matching students found.
                    </p>

                </div>

            <?php else: ?>

                <p class="empty-message">No students registered yet.</p>

            <?php endif; ?>

        </section>

    </main>

</div>

<!-- Student Search Script -->
<script>

const studentSearch = document.getElementById("studentSearch");
const studentRows = document.querySelectorAll(".book-table tbody tr");
const resultCount = document.getElementById("resultCount");
const noResult = document.getElementById("noResult");

function filterStudents() {

    const searchText = studentSearch.value.toLowerCase().trim();

    let visibleStudents = 0;

    studentRows.forEach(function(row) {

        const studentData = row.textContent.toLowerCase();

        if (studentData.includes(searchText)) {

            row.style.display = "";
            visibleStudents++;

        } else {

            row.style.display = "none";

        }

    });

    resultCount.textContent =
        "Showing " + visibleStudents + " students";

    if (noResult) {
        noResult.style.display =
            visibleStudents === 0 ? "block" : "none";
    }

}

studentSearch.addEventListener("input", filterStudents);

</script>

</body>
</html>
