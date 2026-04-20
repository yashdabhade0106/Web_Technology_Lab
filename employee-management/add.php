<?php
require_once 'config/database.php';

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');

    // Validation
    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($department)) $errors[] = 'Department is required.';
    if (empty($designation)) $errors[] = 'Designation is required.';
    if (empty($salary) || !is_numeric($salary) || $salary <= 0) $errors[] = 'Valid salary is required.';
    if (empty($hire_date)) $errors[] = 'Hire date is required.';

    // Check duplicate email
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM employees WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'An employee with this email already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, department, designation, salary, hire_date) VALUES (:name, :email, :phone, :department, :designation, :salary, :hire_date)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':department' => $department,
            ':designation' => $designation,
            ':salary' => $salary,
            ':hire_date' => $hire_date,
        ]);

        header('Location: index.php?success=' . urlencode('Employee "' . $name . '" added successfully!'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - Employee Management System</title>
    <meta name="description" content="Add a new employee record to the system">
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
            <a href="index.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-card fade-in-up" style="margin-top: 32px;">
            <h2>➕ Add New Employee</h2>
            <p class="form-subtitle">Fill in the details below to add a new employee to the system.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    ❌ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="add.php">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g. Rahul Sharma"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="e.g. rahul@company.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="e.g. 9876543210"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Engineering" <?= ($_POST['department'] ?? '') === 'Engineering' ? 'selected' : '' ?>>Engineering</option>
                            <option value="Marketing" <?= ($_POST['department'] ?? '') === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                            <option value="Human Resources" <?= ($_POST['department'] ?? '') === 'Human Resources' ? 'selected' : '' ?>>Human Resources</option>
                            <option value="Finance" <?= ($_POST['department'] ?? '') === 'Finance' ? 'selected' : '' ?>>Finance</option>
                            <option value="Sales" <?= ($_POST['department'] ?? '') === 'Sales' ? 'selected' : '' ?>>Sales</option>
                            <option value="Operations" <?= ($_POST['department'] ?? '') === 'Operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="IT" <?= ($_POST['department'] ?? '') === 'IT' ? 'selected' : '' ?>>IT</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="designation">Designation</label>
                        <input type="text" id="designation" name="designation" placeholder="e.g. Software Engineer"
                               value="<?= htmlspecialchars($_POST['designation'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="salary">Salary (₹)</label>
                        <input type="number" id="salary" name="salary" placeholder="e.g. 75000" step="0.01" min="1"
                               value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hire_date">Hire Date</label>
                        <input type="date" id="hire_date" name="hire_date"
                               value="<?= htmlspecialchars($_POST['hire_date'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-success">✅ Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
