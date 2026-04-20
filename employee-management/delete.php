<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: index.php?error=' . urlencode('Invalid employee ID.'));
    exit;
}

// Check if employee exists
$stmt = $pdo->prepare("SELECT name FROM employees WHERE id = :id");
$stmt->execute([':id' => $id]);
$employee = $stmt->fetch();

if (!$employee) {
    header('Location: index.php?error=' . urlencode('Employee not found.'));
    exit;
}

// Delete the employee
$stmt = $pdo->prepare("DELETE FROM employees WHERE id = :id");
$stmt->execute([':id' => $id]);

header('Location: index.php?success=' . urlencode('Employee "' . $employee['name'] . '" deleted successfully!'));
exit;
?>
