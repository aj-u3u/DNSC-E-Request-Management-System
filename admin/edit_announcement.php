<?php
require_once '../config.php';
checkAdminAuth();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$title = $body = $start_date = $end_date = $photo_path = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]);
    $title = sanitize(trim($_POST["title"]));
    $body = sanitize(trim($_POST["body"]));
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $new_photo_uploaded = false;

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "../uploads/announcements/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $photo_filename = time() . "_" . basename($_FILES["photo"]["name"]);
        $photo_path = "uploads/announcements/" . $photo_filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], "../" . $photo_path)) {
            $new_photo_uploaded = true;

            // Delete old photo
            $stmt = $conn->prepare("SELECT photo FROM announcements WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 1) {
                $old = $res->fetch_assoc()['photo'];
                $old_path = "../" . $old;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            $stmt->close();
        }
    }

    if ($new_photo_uploaded) {
        $sql = "UPDATE announcements SET title=?, body=?, start_date=?, end_date=?, photo=? WHERE id=?";
    } else {
        $sql = "UPDATE announcements SET title=?, body=?, start_date=?, end_date=? WHERE id=?";
    }

    if ($stmt = $conn->prepare($sql)) {
        if ($new_photo_uploaded) {
            $stmt->bind_param("sssssi", $title, $body, $start_date, $end_date, $photo_path, $id);
        } else {
            $stmt->bind_param("ssssi", $title, $body, $start_date, $end_date, $id);
        }

        if ($stmt->execute()) {
            header("Location: manage_announcements.php?success=updated");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch existing data
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $body = $row['body'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $photo_path = $row['photo'];
    }
    $stmt->close();
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Edit Announcement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h3>Edit Announcement</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Body</label>
                <textarea name="body" class="form-control" rows="5" required><?= htmlspecialchars($body) ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Change Photo (optional)</label>
                <input type="file" name="photo" class="form-control" onchange="previewImage(this)">
                <div class="mt-2">
                    <p>Current Photo:</p>
                    <img id="preview" src="../<?= htmlspecialchars($photo_path) ?>" style="max-height:100px;" alt="Current Photo">
                </div>
            </div>
            
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="manage_announcements.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
