<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['signature']) && isset($_POST['member_id'])) {
    $member_id = $_POST['member_id'];

    function generateUniqueFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    $target_dir = "uploads/";
    $newFileName = generateUniqueFileName($_FILES['signature']['name']);
    $target_file = $target_dir . $newFileName;

    if (move_uploaded_file($_FILES['signature']['tmp_name'], $target_file)) {
        // Update database with new file name
        $stmt = $conn->prepare("UPDATE members SET signature = ? WHERE id = ?");
        $stmt->bind_param("si", $newFileName, $member_id);
        $stmt->execute();
        header("Location: ".$_SERVER['HTTP_REFERER']); // Redirect back
    } else {
        echo "File upload failed!";
    }
}
?>
