<?php
require_once '../config.php';
checkAdminAuth();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Get photo path before deletion
    $stmt = $conn->prepare("SELECT photo FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($photo_path);
    $stmt->fetch();
    $stmt->close();

    // Delete announcement
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // If photo exists, delete the file
        if (!empty($photo_path) && file_exists("../" . $photo_path)) {
            unlink("../" . $photo_path);
        }
    }

    $stmt->close();
}

// Redirect back to the announcements list
header("Location: manage_announcements.php");
exit;
