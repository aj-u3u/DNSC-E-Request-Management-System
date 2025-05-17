<?php
require_once '../config.php';
checkAdminAuth();

$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.9em;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .status-active {
            background-color: #198754;
            color: white;
        }
        .status-expired {
            background-color: #dc3545;
            color: white;
        }
        .status-upcoming {
            background-color: #ffc107;
            color: black;
        }
        img.thumb {
            height: 60px;
            width: auto;
            object-fit: cover;
            border-radius: 5px;
        }
         .sidebar {
            min-height: 100vh;
            background-color: #198754;
            color: white;
        }
    .nav-link {
      color: rgba(255,255,255,.8);
    }
    .nav-link:hover {
      color: white;
    }
    .nav-link.active {
      color: white;
      background-color: rgba(255,255,255,.2);
    }
    .dashboard-card {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background-color: #498428;
      border-color: #498428;
    }
    .btn-primary:hover {
      background-color: #2d5516;
      border-color: #2d5516;
    }
    .sidebar .nav-link {
      position: relative;
    }
    .badge-notification {
      position: absolute;
      top: 5px;
      right: 15px;
      background-color: red;
      color: white;
      font-size: 0.6rem;
      font-weight: 600;
      padding: 2px 6px;
      border-radius: 50%;
    }
    </style>
</head>
<body>
    <div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <div class="position-sticky pt-3">
        <div class="text-center mb-4">
          <h5>DNSC E-Request System</h5>
          <p class="text-white">Admin Panel</p>
        </div>
        <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="requests.php">
                            <i class="fas fa-clipboard-list me-2"></i>
                            All Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending.php">
                            <i class="fas fa-clock me-2"></i>
                            Pending Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="approved.php">
                            <i class="fas fa-check-circle me-2"></i>
                            Approved Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="completed.php">
                            <i class="fas fa-check-double me-2"></i>
                            Completed Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registration_list.php">
                            <i class="fas fa-user-check me-2"></i> Registration List
                        </a>
                    </li>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link" href="create_announcement.php">
                            <i class="fas fa-bullhorn me-2"></i>
                            Create Announcement
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link active" href="manage_announcements.php">
                            <i class="fas fa-tools me-2"></i>
                            Announcement List
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_notifications.php">
                            <i class="fas fa-bell me-2"></i> Notifications
                            <?php
                            // Get notification count
                            $notifCountQuery = $conn->query("SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0");
                            $notifCount = $notifCountQuery->fetch_assoc()['count'];
                            if($notifCount > 0): ?>
                                <span class="badge bg-danger rounded-pill position-absolute top-50 end-0 translate-middle-y me-3"><?php echo $notifCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </li>          
                </ul>
      </div>
    </div>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Announcements</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="btn btn-sm btn-outline-secondary">Welcome, <?php echo $_SESSION['full_name']; ?></span>
                    </div>
                </div>
            </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-success">
                <tr>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Dates</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $today = date('Y-m-d');
            while ($row = $result->fetch_assoc()):
                $status = '';
                if ($today < $row['start_date']) {
                    $status = '<span class="status-badge status-upcoming">Upcoming</span>';
                } elseif ($today > $row['end_date']) {
                    $status = '<span class="status-badge status-expired">Expired</span>';
                } else {
                    $status = '<span class="status-badge status-active">Active</span>';
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['body'])); ?></td>
                    <td>
                        <strong>From:</strong> <?php echo $row['start_date']; ?><br>
                        <strong>To:</strong> <?php echo $row['end_date']; ?>
                    </td>
                   <td>
                        <?php
                        $imagePath = '../' . $row['photo'];
                        ?>
                        <?php if (!empty($row['photo']) && file_exists($imagePath)): ?>
                            <img src="<?= $imagePath ?>" class="thumb" alt="Announcement Photo">
                        <?php else: ?>
                            <span class="text-muted">None</span>
                        <?php endif; ?>
                    </td>


                    <td><?= $status ?></td>
                    <td>
                        <a href="edit_announcement.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteModal(<?= $row['id']; ?>)">Delete</button>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this announcement?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <a href="#" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Delete</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function showDeleteModal(id) {
    const deleteUrl = `delete_announcement.php?id=${id}`;
    document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
  }
</script>

</body>
</html>
