<?php
require_once 'config/database.php';

$errors = [];
$employee = null;

// Get employee ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: index.php?error=' . urlencode('Invalid employee ID.'));
    exit;
}

// Fetch employee
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
$stmt->execute([':id' => $id]);
$employee = $stmt->fetch();

if (!$employee) {
    header('Location: index.php?error=' . urlencode('Employee not found.'));
    exit;
}

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

    // Check duplicate email (excluding current employee)
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM employees WHERE email = :email AND id != :id");
        $check->execute([':email' => $email, ':id' => $id]);
        if ($check->fetch()) {
            $errors[] = 'An employee with this email already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE employees SET name = :name, email = :email, phone = :phone, department = :department, designation = :designation, salary = :salary, hire_date = :hire_date WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':department' => $department,
            ':designation' => $designation,
            ':salary' => $salary,
            ':hire_date' => $hire_date,
            ':id' => $id,
        ]);

        header('Location: index.php?success=' . urlencode('Employee "' . $name . '" updated successfully!'));
        exit;
    }

    // Overwrite employee data with POST data for form re-display
    $employee['name'] = $name;
    $employee['email'] = $email;
    $employee['phone'] = $phone;
    $employee['department'] = $department;
    $employee['designation'] = $designation;
    $employee['salary'] = $salary;
    $employee['hire_date'] = $hire_date;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Employee Management System</title>
    <meta name="description" content="Edit employee record details">
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
            <h2>✏️ Edit Employee</h2>
            <p class="form-subtitle">Update the employee details below.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    ❌ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?= $id ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g. Rahul Sharma"
                               value="<?= htmlspecialchars($employee['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="e.g. rahul@company.com"
                               value="<?= htmlspecialchars($employee['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="e.g. 9876543210"
                               value="<?= htmlspecialchars($employee['phone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php
                            $depts = ['Engineering', 'Marketing', 'Human Resources', 'Finance', 'Sales', 'Operations', 'IT'];
                            foreach ($depts as $dept):
                            ?>
                                <option value="<?= $dept ?>" <?= $employee['department'] === $dept ? 'selected' : '' ?>><?= $dept ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="designation">Designation</label>
                        <input type="text" id="designation" name="designation" placeholder="e.g. Software Engineer"
                               value="<?= htmlspecialchars($employee['designation']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="salary">Salary (₹)</label>
                        <input type="number" id="salary" name="salary" placeholder="e.g. 75000" step="0.01" min="1"
                               value="<?= htmlspecialchars($employee['salary']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hire_date">Hire Date</label>
                        <input type="date" id="hire_date" name="hire_date"
                               value="<?= htmlspecialchars($employee['hire_date']) ?>" required>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-success">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
