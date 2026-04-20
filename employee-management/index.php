<?php
require_once 'config/database.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department_filter = isset($_GET['department']) ? trim($_GET['department']) : '';

$query = "SELECT * FROM employees WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (name LIKE :search OR email LIKE :search2 OR designation LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

if ($department_filter !== '') {
    $query .= " AND department = :dept";
    $params[':dept'] = $department_filter;
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Stats
$total = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$departments = $pdo->query("SELECT COUNT(DISTINCT department) FROM employees")->fetchColumn();
$avg_salary = $pdo->query("SELECT COALESCE(AVG(salary), 0) FROM employees")->fetchColumn();
$recent = $pdo->query("SELECT COUNT(*) FROM employees WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

// Get distinct departments for filter
$dept_list = $pdo->query("SELECT DISTINCT department FROM employees ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

// Flash messages
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Helper: get department CSS class
function getDeptClass($dept) {
    $map = [
        'Engineering' => 'dept-engineering',
        'Marketing' => 'dept-marketing',
        'Human Resources' => 'dept-hr',
        'Finance' => 'dept-finance',
    ];
    return $map[$dept] ?? 'dept-default';
}

// Helper: get initials
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper($part[0]);
    }
    return $initials;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <meta name="description" content="Manage employee records - add, update, delete and view employees">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <div class="brand-icon">👥</div>
                <h1>Employee<span>Hub</span></h1>
            </a>
            <a href="add.php" class="btn btn-primary">
                <span>+</span> Add Employee
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?= $error ?></div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h2>Employee Dashboard</h2>
                <div class="subtitle">Manage and track all employee records</div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card fade-in-up">
                <div class="stat-icon purple">👥</div>
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-card fade-in-up" style="animation-delay: 0.1s">
                <div class="stat-icon teal">🏢</div>
                <div class="stat-value"><?= $departments ?></div>
                <div class="stat-label">Departments</div>
            </div>
            <div class="stat-card fade-in-up" style="animation-delay: 0.2s">
                <div class="stat-icon blue">💰</div>
                <div class="stat-value">₹<?= number_format($avg_salary, 0) ?></div>
                <div class="stat-label">Avg. Salary</div>
            </div>
            <div class="stat-card fade-in-up" style="animation-delay: 0.3s">
                <div class="stat-icon orange">🆕</div>
                <div class="stat-value"><?= $recent ?></div>
                <div class="stat-label">New This Month</div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Search by name, email or designation..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <select id="deptFilter" class="btn btn-outline" style="padding: 10px 16px; font-size: 0.88rem;">
                <option value="">All Departments</option>
                <?php foreach ($dept_list as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>" <?= $department_filter === $dept ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Employees Table -->
        <?php if (count($employees) > 0): ?>
        <div class="table-wrapper fade-in-up">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Salary</th>
                        <th>Hire Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="avatar"><?= getInitials($emp['name']) ?></div>
                                <strong><?= htmlspecialchars($emp['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td><?= htmlspecialchars($emp['phone']) ?></td>
                        <td>
                            <span class="dept-badge <?= getDeptClass($emp['department']) ?>">
                                <?= htmlspecialchars($emp['department']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($emp['designation']) ?></td>
                        <td><span class="salary">₹<?= number_format($emp['salary'], 2) ?></span></td>
                        <td><?= date('d M Y', strtotime($emp['hire_date'])) ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit.php?id=<?= $emp['id'] ?>" class="btn btn-warning btn-sm" title="Edit">✏️ Edit</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $emp['id'] ?>, '<?= htmlspecialchars($emp['name'], ENT_QUOTES) ?>')" title="Delete">🗑️ Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No employees found</h3>
                <p>
                    <?= $search || $department_filter ? 'Try adjusting your search or filter criteria.' : 'Get started by adding your first employee record.' ?>
                </p>
                <?php if (!$search && !$department_filter): ?>
                    <a href="add.php" class="btn btn-primary">+ Add Employee</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-icon">🗑️</div>
            <h3>Delete Employee</h3>
            <p>Are you sure you want to delete <strong id="deleteName"></strong>? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <a href="#" id="deleteLink" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>

    <script>
        // Search with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateFilters();
            }, 400);
        });

        // Department filter
        document.getElementById('deptFilter').addEventListener('change', function() {
            updateFilters();
        });

        function updateFilters() {
            const search = document.getElementById('searchInput').value;
            const dept = document.getElementById('deptFilter').value;
            let url = 'index.php?';
            if (search) url += 'search=' + encodeURIComponent(search) + '&';
            if (dept) url += 'department=' + encodeURIComponent(dept) + '&';
            window.location.href = url.slice(0, -1);
        }

        // Delete modal
        function confirmDelete(id, name) {
            document.getElementById('deleteName').textContent = name;
            document.getElementById('deleteLink').href = 'delete.php?id=' + id;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal on overlay click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
