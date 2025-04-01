<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $order_id = $_POST['order_id'];
    $company_name = $_POST['company_name'];
    $contact_number = $_POST['contact_number'];
    $card_qty = $_POST['card_qty'];

    // Update query
    $sql = "UPDATE companies SET order_id = ?, company_name = ?, contact_number = ?, card_qty = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $order_id, $company_name, $contact_number, $card_qty, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
}
?>
