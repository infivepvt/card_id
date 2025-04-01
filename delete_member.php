<?php
include 'db.php';

// Get member ID and company link from URL
$member_id = $_GET['id'] ?? null;
$company_link = $_GET['company_link'] ?? '';

if (!$member_id) {
    die("<script>
        alert('Member ID not provided!');
        window.history.back();
    </script>");
}

// Delete member from database
$delete_sql = "DELETE FROM members WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $member_id);

if ($stmt->execute()) {
    echo "<script>
        alert('Member deleted successfully!');
        window.location.href = 'view_members.php?id=".urlencode($company_link)."';
    </script>";
} else {
    echo "<script>
        alert('Error deleting member!');
        window.location.href = 'view_members.php?id=".urlencode($company_link)."';
    </script>";
}
exit();
?>