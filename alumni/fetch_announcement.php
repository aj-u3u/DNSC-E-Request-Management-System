<?php
require_once '../config.php';
checkAlumniAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();
$stmt->close();

if (!$announcement) {
    echo json_encode(['success' => false, 'message' => 'Announcement not found']);
    exit;
}

echo json_encode([
    'success' => true,
    'announcement' => [
        'title' => $announcement['title'],
        'start_date' => $announcement['start_date'],
        'end_date' => $announcement['end_date'],
        'photo' => $announcement['photo'],
        // We want raw text here, the JS replaces \n with <br> already
        'body' => $announcement['body']
    ]
]);
