<?php
include("../assets/database.php");
date_default_timezone_set('Asia/Colombo');
session_start();

if (!isset($_SESSION["admin_logged_user"])) {
    header("Location: ./login");
    exit;
}

// Ensure dropped_students table exists
$conn->query("CREATE TABLE IF NOT EXISTS `dropped_students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reg_no` VARCHAR(50) NOT NULL UNIQUE,
  `dropped_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Fetch all students with drop status
$students = [];
$res = $conn->query("
    SELECT u.reg_no, u.Name, u.card,
           IF(d.reg_no IS NOT NULL, 1, 0) AS is_dropped
    FROM users u
    LEFT JOIN dropped_students d ON u.reg_no = d.reg_no
    ORDER BY u.reg_no ASC
");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}

$total_count   = count($students);
$dropped_count = count(array_filter($students, fn($s) => $s['is_dropped']));
$active_count  = $total_count - $dropped_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - SCMP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 250px; }
        body { background-color: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        #content { width: calc(100% - var(--sidebar-width)); margin-left: var(--sidebar-width); min-height: 100vh; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,.1); }
        .badge-dropped { background: #dc3545; }
        .badge-active  { background: #28a745; }
        tr.dropped-row td { opacity: 0.6; }
        tr.dropped-row { background-color: #fff5f5 !important; }
    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>
    <div id="content">
        <?php include('topbar.php'); ?>
        <div class="container-fluid p-4">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users text-primary me-2"></i> Manage Students
                </h1>
                <div class="text-muted small">Total: <?php echo $total_count; ?> &nbsp;|&nbsp;
                    <span class="text-success fw-bold"><?php echo $active_count; ?> Active</span> &nbsp;|&nbsp;
                    <span class="text-danger fw-bold"><?php echo $dropped_count; ?> Dropped</span>
                </div>
            </div>

            <!-- Alert area -->
            <div id="alertArea"></div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card p-3 text-center">
                        <div class="text-muted small fw-bold text-uppercase">Total Students</div>
                        <div class="h3 fw-bold text-primary"><?php echo $total_count; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center">
                        <div class="text-muted small fw-bold text-uppercase">Active Students</div>
                        <div class="h3 fw-bold text-success"><?php echo $active_count; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center">
                        <div class="text-muted small fw-bold text-uppercase">Dropped Students</div>
                        <div class="h3 fw-bold text-danger"><?php echo $dropped_count; ?></div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span class="fw-bold me-2">Filter:</span>
                        <button class="btn btn-sm btn-outline-primary active" id="filterAll" onclick="filterStudents('all')">All</button>
                        <button class="btn btn-sm btn-outline-success" id="filterActive" onclick="filterStudents('active')">Active Only</button>
                        <button class="btn btn-sm btn-outline-danger" id="filterDropped" onclick="filterStudents('dropped')">Dropped Only</button>
                        <input type="text" class="form-control form-control-sm ms-auto" style="max-width:220px"
                               placeholder="Search name / reg no..." id="searchInput" oninput="filterStudents()">
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="studentsTable">
                            <thead style="background:#4e73df;color:white;">
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:20%">Reg No</th>
                                    <th style="width:35%">Student Name</th>
                                    <th style="width:15%">Card No</th>
                                    <th style="width:12%">Status</th>
                                    <th style="width:13%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idx = 1; foreach ($students as $st): ?>
                                <tr class="student-row <?php echo $st['is_dropped'] ? 'dropped-row' : ''; ?>"
                                    data-dropped="<?php echo $st['is_dropped'] ? '1' : '0'; ?>"
                                    data-search="<?php echo strtolower(htmlspecialchars($st['reg_no'] . ' ' . $st['Name'])); ?>"
                                    id="row-<?php echo htmlspecialchars($st['reg_no']); ?>">
                                    <td><?php echo $idx++; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($st['reg_no']); ?></td>
                                    <td><?php echo htmlspecialchars($st['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($st['card'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($st['is_dropped']): ?>
                                            <span class="badge badge-dropped">
                                                <i class="fas fa-user-minus me-1"></i>Dropped
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-active">
                                                <i class="fas fa-user-check me-1"></i>Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($st['is_dropped']): ?>
                                            <button class="btn btn-sm btn-outline-success"
                                                    onclick="restoreStudent('<?php echo htmlspecialchars($st['reg_no']); ?>', '<?php echo htmlspecialchars($st['Name']); ?>')">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="dropStudent('<?php echo htmlspecialchars($st['reg_no']); ?>', '<?php echo htmlspecialchars($st['Name']); ?>')">
                                                <i class="fas fa-user-minus me-1"></i>Drop
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Confirm Drop Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmBody">Are you sure?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" id="confirmBtn" onclick="executeAction()">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let pendingAction = null;
        let pendingRegNo  = null;
        let confirmModal  = new bootstrap.Modal(document.getElementById('confirmModal'));

        function dropStudent(regNo, name) {
            pendingAction = 'drop';
            pendingRegNo  = regNo;
            document.getElementById('confirmTitle').textContent = 'Drop Student';
            document.getElementById('confirmBody').innerHTML =
                `Are you sure you want to <strong>drop</strong> <strong>${name}</strong> (${regNo})?<br>
                 <small class="text-muted">Their dues will be excluded from Grand Total in Due Reports.</small>`;
            document.getElementById('confirmBtn').className = 'btn btn-danger';
            document.getElementById('confirmBtn').textContent = 'Yes, Drop';
            confirmModal.show();
        }

        function restoreStudent(regNo, name) {
            pendingAction = 'restore';
            pendingRegNo  = regNo;
            document.getElementById('confirmTitle').textContent = 'Restore Student';
            document.getElementById('confirmBody').innerHTML =
                `Are you sure you want to <strong>restore</strong> <strong>${name}</strong> (${regNo})?<br>
                 <small class="text-muted">Their dues will be included again in Due Reports.</small>`;
            document.getElementById('confirmBtn').className = 'btn btn-success';
            document.getElementById('confirmBtn').textContent = 'Yes, Restore';
            confirmModal.show();
        }

        function executeAction() {
            confirmModal.hide();
            if (!pendingAction || !pendingRegNo) return;

            fetch('./api_drop_student', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                           'X-Requested-With': 'XMLHttpRequest' },
                body: `action=${pendingAction}&reg_no=${encodeURIComponent(pendingRegNo)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message || 'Done.');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showAlert('danger', data.error || 'Action failed.');
                }
            })
            .catch(() => showAlert('danger', 'Request failed. Please try again.'));
        }

        function showAlert(type, msg) {
            document.getElementById('alertArea').innerHTML =
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${msg}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
        }

        let currentFilter = 'all';

        function filterStudents(filter) {
            if (filter) {
                currentFilter = filter;
                document.querySelectorAll('[id^=filter]').forEach(b => b.classList.remove('active'));
                const btn = { all: 'filterAll', active: 'filterActive', dropped: 'filterDropped' }[filter];
                if (btn) document.getElementById(btn).classList.add('active');
            }
            const search = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.student-row').forEach(row => {
                const dropped  = row.dataset.dropped === '1';
                const haystack = row.dataset.search;
                let show = true;
                if (currentFilter === 'active'  &&  dropped) show = false;
                if (currentFilter === 'dropped' && !dropped) show = false;
                if (search && !haystack.includes(search)) show = false;
                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</body>
</html>
