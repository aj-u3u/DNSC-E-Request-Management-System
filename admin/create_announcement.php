<?php
require_once '../config.php';
checkAdminAuth();

$title = $body = $start_date = $end_date = "";
$photo_path = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_submit"])) {
    $title = sanitize(trim($_POST["title"]));
    $body = sanitize(trim($_POST["body"]));
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

    // Handle image upload
    if (!empty($_FILES["photo"]["name"])) {
        $upload_dir = "../uploads/announcements/";
        $filename = time() . '_' . basename($_FILES["photo"]["name"]);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_path)) {
           $photo_path = 'uploads/announcements/' . $filename;

        }
    }

    $stmt = $conn->prepare("INSERT INTO announcements (title, body, start_date, end_date, photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $body, $start_date, $end_date, $photo_path);

    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                let successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            });
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .btn-primary {
            background-color: #198754;
            border-color: #198754;
        }
        .btn-primary:hover {
            background-color: #146c43;
            border-color: #146c43;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
        .modal-header {
            background-color: #198754;
            color: white;
        }
        .modal-body p {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f1fdf6;
            border-left: 5px solid #198754;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Create Announcement</h4>
                </div>
                <div class="card-body">
                    <form id="announcementForm" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Body</label>
                            <textarea class="form-control" name="body" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photo (optional)</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>

                        <input type="hidden" name="confirm_submit" value="1">

                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-danger">Cancel</a>
                            <button type="button" class="btn btn-primary" id="previewBtn">Publish Announcement</button>
                        </div>

                        <!-- Preview Modal -->
                        <div class="modal fade" id="previewModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Announcement</h5>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Title:</strong> <span id="preview_title"></span></p>
                                        <p><strong>Body:</strong> <span id="preview_body"></span></p>
                                        <p><strong>Start Date:</strong> <span id="preview_start"></span></p>
                                        <p><strong>End Date:</strong> <span id="preview_end"></span></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Edit</button>
                                        <button type="submit" class="btn btn-success">Confirm & Publish</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Announcement Published</h5>
            </div>
            <div class="modal-body">
                <p>Your announcement has been successfully created!</p>
            </div>
            <div class="modal-footer">
                <a href="dashboard.php" class="btn btn-success">OK</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const form = document.getElementById("announcementForm");
    const previewModal = new bootstrap.Modal(document.getElementById("previewModal"));

    document.getElementById("previewBtn").addEventListener("click", function () {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Fill preview values
        document.getElementById("preview_title").innerText = form["title"].value;
        document.getElementById("preview_body").innerText = form["body"].value;
        document.getElementById("preview_start").innerText = form["start_date"].value;
        document.getElementById("preview_end").innerText = form["end_date"].value;

        previewModal.show();
    });
</script>
</body>
</html>
