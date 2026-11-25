
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Fetch All Students
$query = mysqli_query(
    $conn,
    "SELECT id, name, email, created_at
     FROM students
     ORDER BY id DESC"
);

if (!$query) {
    die("Unable to export students.");
}

// CSV Download Headers
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=readify_students.csv");

// Open Output
$output = fopen("php://output", "w");

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Column Headings
fputcsv($output, [
    "Student ID",
    "Student Name",
    "Email",
    "Registration Date"
]);

// Export Student Data
while ($student = mysqli_fetch_assoc($query)) {

    fputcsv($output, [
        $student["id"],
        $student["name"],
        $student["email"],
        $student["created_at"]
    ]);

}

fclose($output);
mysqli_close($conn);
exit;
?>
