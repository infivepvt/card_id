<?php
// Include the database connection
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if the id is passed in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete related records from members table first
        $sql_delete_members = "DELETE FROM members WHERE company_id = ?";
        $stmt_members = $conn->prepare($sql_delete_members);
        $stmt_members->bind_param("i", $id);
        $stmt_members->execute();
        $stmt_members->close();

        // Now delete the company
        $sql_delete_company = "DELETE FROM companies WHERE id = ?";
        $stmt_company = $conn->prepare($sql_delete_company);
        $stmt_company->bind_param("i", $id);
        $stmt_company->execute();
        $stmt_company->close();

        // Commit the transaction
        $conn->commit();

        // Redirect to home page with success message
        header('Location: home.php?status=deleted');
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    header('Location: home.php');
    exit();
}

// Close the database connection
$conn->close();
?>