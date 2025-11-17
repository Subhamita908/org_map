<?php
session_start();
if (!isset($_SESSION['hr_logged_in']) || $_SESSION['hr_logged_in'] !== true) {
    header("Location: index.html");
    exit();
}

require 'config.php';

$hr_id = $_GET['id'] ?? null;

if (!$hr_id || !is_numeric($hr_id)) {
    echo "Invalid request.";
    exit();
}

try {
    // Fetch the HR data to get the image file name
    $stmt = $pdo->prepare("SELECT profile_image FROM hr_login WHERE id = ?");
    $stmt->execute([$hr_id]);
    $hr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hr) {
        echo "HR profile not found.";
        exit();
    }

    // Delete profile image if it exists
    if (!empty($hr['profile_image'])) {
        $image_path = "uploads/hr_profiles/" . $hr['profile_image'];
        if (file_exists($image_path)) {
            unlink($image_path); // delete the image file from server
        }
    }

    // Delete HR record from database
    $delete_stmt = $pdo->prepare("DELETE FROM hr_login WHERE id = ?");
    $delete_stmt->execute([$hr_id]);

    // Clear session and redirect to login
    session_unset();
    session_destroy();
    header("Location: index.html?message=Profile+deleted+successfully");
    exit();

} catch (PDOException $e) {
    echo "Error deleting profile: " . $e->getMessage();
    exit();
}
?>
