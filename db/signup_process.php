<?php
include __DIR__ . '/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $firstName = $_POST['firstName'];
  $lastName = $_POST['lastName'];
  $email = $_POST['signupEmail'];
  $contact = $_POST['contactNumber'];
  $password = password_hash($_POST['signupPassword'], PASSWORD_DEFAULT);
  // determine role by email rule: ends with "admin@popmart.com"
  $isAdmin = (bool)preg_match('/admin@popmart\.com$/i', $email);
  $role = $isAdmin ? 'admin' : 'customer';

  try {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, contact_number, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $email, $contact, $password, $role]);
    echo "success";
  } catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
      echo "duplicate_email";
    } else {
      echo "error: " . $e->getMessage();
    }
  }
} else {
  echo "Invalid request method";
}
?>
