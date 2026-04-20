<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo "Connected!\n";
    $pdo->exec("FLUSH PRIVILEGES");
    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY 'root123'");
    echo "Password reset to 'root123' successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
