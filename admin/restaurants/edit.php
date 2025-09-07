// admin/restaurants/edit.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE restaurants SET name=?, address=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['address'], $_POST['id']]);
    header("Location: /admin/restaurants?success=1");
}