<?php
include 'db.php';

if (isset($_GET['id'])) {
    $company_link = $_GET['id'];

    $sql = "SELECT * FROM companies WHERE company_link = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $company_link);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $company = $result->fetch_assoc();
    } else {
        echo "Company not found!";
        exit();
    }

    $query = $conn->prepare("SELECT * FROM members WHERE company_id = ?");
    $query->bind_param("i", $company['id']);
    $query->execute();
    $result1 = $query->get_result();
} else {
    echo "No ID provided!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Company Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Base styles */
        body {
            font-family: 'Roboto', sans-serif;
            padding-bottom: 60px;
            background-color: #f8f9fa;
        }

        .container {
            padding: 15px;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Company details table */
        .company-details table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .company-details th,
        .company-details td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }

        .company-details th {
            background-color: #f1f1f1;
            width: 30%;
        }

        /* Responsive table container */
        .table-responsive-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Members table styling */
        .members-table {
            min-width: 1000px;
            width: 100%;
            margin-bottom: 0;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .members-table th,
        .members-table td {
            padding: 10px 8px;
            vertical-align: middle;
            word-wrap: break-word;
            border: 1px solid #dee2e6;
        }

        .members-table th {
            background-color: #f1f1f1;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Column width adjustments */
        .members-table th:nth-child(1),
        .members-table td:nth-child(1) {
            width: 150px;
            /* Name */
        }

        .members-table th:nth-child(2),
        .members-table td:nth-child(2) {
            width: 100px;
            /* Title */
        }

        .members-table th:nth-child(3),
        .members-table td:nth-child(3) {
            width: 120px;
            /* NIC */
        }

        .members-table th:nth-child(4),
        .members-table td:nth-child(4) {
            width: 100px;
            /* Register No */
        }

        .members-table th:nth-child(5),
        .members-table td:nth-child(5) {
            width: 80px;
            /* Expire */
        }

        .members-table th:nth-child(6),
        .members-table td:nth-child(6) {
            width: 80px;
            /* Lifetime */
        }

        .members-table th:nth-child(7),
        .members-table td:nth-child(7) {
            width: 150px;
            /* Remark */
        }

        .members-table th:nth-child(8),
        .members-table td:nth-child(8) {
            width: 120px;
            /* Profile Photo */
        }

        .members-table th:nth-child(9),
        .members-table td:nth-child(9) {
            width: 120px;
            /* Signature */
        }

        .members-table th:nth-child(10),
        .members-table td:nth-child(10) {
            width: 100px;
            /* Action */
        }

        .members-table th:nth-child(11),
        .members-table td:nth-child(11) {
            width: 120px;
            /* Status */
        }

        /* Zebra striping */
        .members-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .members-table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        /* Image styling */
        .img-thumbnail {
            max-width: 80px;
            height: auto;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Button styling */
        .custom-btn {
            padding: 8px 12px;
            font-size: 14px;
            margin: 5px;
            white-space: nowrap;
            border-radius: 4px;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .action-buttons .btn {
            margin: 2px;
            padding: 5px 8px;
            font-size: 12px;
            min-width: 30px;
        }

        /* Fullscreen mode */
        /* Fullscreen mode specific styles */
        #membersTableContainerFullScreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            z-index: 1000;
            overflow: auto;
            display: none;
            padding: 20px;
        }

        #membersTableFullScreen {
            width: 100%;
            table-layout: auto;
            /* Changed from fixed to auto for better column sizing */
        }

        #membersTableFullScreen th,
        #membersTableFullScreen td {
            padding: 12px 8px;
            /* Slightly larger padding in fullscreen */
            vertical-align: middle;
        }

        /* Make remark column wider in fullscreen */
        #membersTableFullScreen th:nth-child(8),
        #membersTableFullScreen td:nth-child(8) {
            width: 300px;
            /* Wider remark column */
            max-width: 300px;
        }

        /* Fullscreen remark styling */
        #membersTableFullScreen .remark-cell {
            max-width: 300px;
        }

        #membersTableFullScreen .full-remark-popup {
            max-width: 400px;
        }

        .close-fullscreen-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1010;
            padding: 8px 15px;
            font-size: 16px;
        }

        /* Adjust other columns in fullscreen to accommodate wider remark column */
        #membersTableFullScreen th:nth-child(1),
        #membersTableFullScreen td:nth-child(1) {
            width: auto;
        }

        #membersTableFullScreen th:nth-child(2),
        #membersTableFullScreen td:nth-child(2) {
            width: auto;
        }

        /* Toggle switch styles */
        .toggle-container {
            display: flex;
            align-items: center;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #28a745;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(26px);
        }

        .toggle-label {
            margin-left: 10px;
            font-weight: normal;
            font-size: 14px;
        }

        .status-pending {
            color: #dc3545;
        }

        .status-approved {
            color: #28a745;
        }

        .toggle-disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .login-hint {
            font-size: 0.8rem;
            color: #6c757d;
            display: block;
            margin-top: 5px;
        }

        .download-btn {
            display: inline-block;
        }

        .restricted {
            display: none;
        }

        .password-verified .restricted {
            display: inline-block;
        }

        .btn-disabled {
            opacity: 0.65;
            pointer-events: none;
        }

        .table-actions-enabled .restricted {
            display: inline-block !important;
        }

        .status-toggles-enabled .toggle-switch {
            pointer-events: auto !important;
        }

        .status-toggles-enabled .status-toggle {
            pointer-events: auto !important;
        }

        .status-toggles-enabled .login-hint {
            display: none !important;
        }

        /* Hover effects */
        #membersTableFullScreen tbody tr:hover,
        .members-table tbody tr:hover {
            background-color: #e9f7ef !important;
            transition: background-color 0.2s ease;
        }

        /* Remark cell styling */
        .remark-cell {
            position: relative;
            cursor: pointer;
        }

        .remark-content {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .full-remark-popup {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 100;
            width: auto;
            max-width: 300px;
            word-wrap: break-word;
            white-space: normal;
            display: none;
            left: 0;
            top: 100%;
        }

        .remark-cell:hover .full-remark-popup {
            display: block;
        }

        .copied-feedback {
            position: absolute;
            background: #4CAF50;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h4 {
                font-size: 18px;
            }

            h2 {
                font-size: 20px;
            }

            .company-details th,
            .company-details td {
                padding: 8px;
                font-size: 14px;
            }

            .members-table th,
            .members-table td {
                font-size: 13px;
                padding: 8px 6px;
            }

            .img-thumbnail {
                max-width: 60px;
            }

            .custom-btn {
                padding: 6px 10px;
                font-size: 13px;
                margin: 3px;
            }

            .action-buttons .btn {
                padding: 4px 6px;
                font-size: 11px;
            }

            .toggle-switch {
                width: 40px;
                height: 20px;
            }

            .toggle-slider:before {
                height: 14px;
                width: 14px;
            }

            .toggle-label {
                font-size: 12px;
                margin-left: 8px;
            }

            .login-hint {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 8px;
            }

            h4 {
                font-size: 16px;
            }

            h2 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .company-details th,
            .company-details td {
                padding: 6px;
                font-size: 13px;
            }

            .members-table th,
            .members-table td {
                font-size: 12px;
                padding: 6px 4px;
            }

            .custom-btn {
                padding: 5px 8px;
                font-size: 12px;
                margin: 2px;
            }

            .img-thumbnail {
                max-width: 50px;
            }

            .action-buttons .btn {
                padding: 3px 5px;
                font-size: 10px;
            }

            .toggle-label {
                font-size: 11px;
            }

            /* Shorten column headers for very small screens */
            .members-table th:nth-child(4) {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 60px;
            }

            .members-table th:nth-child(1) {
                width: 130px;
            }
        }

        /* Extra small devices (phones, 400px and down) */
        @media (max-width: 400px) {
            .container {
                padding: 5px;
            }

            .custom-btn {
                padding: 4px 6px;
                font-size: 11px;
            }

            .members-table th,
            .members-table td {
                font-size: 11px;
                padding: 5px 3px;
            }

            .img-thumbnail {
                max-width: 40px;
            }

            .toggle-switch {
                width: 35px;
                height: 18px;
            }

            .toggle-slider:before {
                height: 12px;
                width: 12px;
            }
        }

        .disabled {
            opacity: 0.5;
            pointer-events: none;
            cursor: not-allowed;
        }

        /* Add this to your style section */
        td[onclick] {
            cursor: pointer;
        }

        td[onclick]:hover {
            background-color: #f0f8ff;
            /* Light blue background on hover */
        }

        /* Make sure the feedback appears properly */
        .copied-feedback {
            position: absolute;
            background: #4CAF50;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            z-index: 100;
        }

        /* For cells that need relative positioning for the feedback */
        td[onclick] {
            position: relative;
        }
    </style>
</head>

<body>
    <div class="container mt-3">
        <img src="images/logo-infive.png" alt="Logo" class="img-fluid" style="max-width: 150px; height: auto;">
        <h4 class="mb-3 text-center">Company Order</h4>

        <!-- Company Details -->
        <div class="company-details mb-4">
            <table class="table table-bordered">
                <tr>
                    <th>Order Number</th>
                    <td><?= $company['order_id'] ?></td>
                </tr>
                <tr>
                    <th>Company Name</th>
                    <td><?= $company['company_name'] ?></td>
                </tr>
                <tr>
                    <th>Contact Number</th>
                    <td><?= $company['contact_number'] ?></td>
                </tr>
                <tr>
                    <th>Date and Time</th>
                    <td><?= $company['datetime'] ?></td>
                </tr>
                <tr>
                    <th>Card Quantity</th>
                    <td><?= $company['card_qty'] ?></td>
                </tr>
            </table>
        </div>

        <!-- Members List -->
        <div class="mt-4">
            <h2 class="mb-3">Members List</h2>

            <!-- Action Buttons -->
            <div class="text-center mb-3 d-flex flex-wrap justify-content-center gap-2">
                <button id="addMemberBtn" class="btn btn-success custom-btn"
                    onclick="verifyPasswordForAddMember('<?= $company_link ?>')">
                    <i class="bi bi-plus-circle"></i> Add Member
                </button>
                <button class="btn btn-primary custom-btn full-screen-btn" onclick="toggleFullScreen()">
                    <i class="bi bi-arrows-fullscreen"></i> Full Screen
                </button>
                <button id="enableStatusBtn" class="btn btn-info custom-btn" onclick="verifyPasswordForStatusUpdate()">
                    <i class="bi bi-toggle-on"></i> Enable Status
                </button>
            </div>

            <!-- Scrollable Members Table -->
            <div class="table-responsive-container">
                <table class="table table-bordered members-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>NIC</th>
                            <th>Reg No</th>
                            <th>Expire</th>
                            <th>Life</th>
                            <th>Remark</th>
                            <th>Photo</th>
                            <th>Signature</th>
                            <th class="action-buttons">Action</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        while ($row = $result1->fetch_assoc()) {
                            ?>
                            <tr>
                                <td onclick="copyToClipboard(this)"><strong><?= $count++ ?>.</strong>
                                    <?= htmlspecialchars($row['name']) ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['title']) ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['nic']) ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['register_no']) ?></td>
                                <td onclick="copyToClipboard(this)">
                                    <?= !empty($row['expire']) ? htmlspecialchars($row['expire']) : 'No' ?>
                                </td>
                                <td onclick="copyToClipboard(this)"><?= $row['lifetime'] ? "Yes" : "No" ?></td>
                                <td class="remark-cell" onclick="copyToClipboard(this)" title="Click to copy"
                                    data-fulltext="<?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : 'No Remark' ?>">
                                    <div class="remark-content">
                                        <?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : 'No Remark' ?>
                                    </div>
                                    <div class="full-remark-popup">
                                        <?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : 'No Remark' ?>
                                    </div>
                                </td>

                                <!-- Profile Photo Column -->
                                <td class="text-center">
                                    <?php if (!empty($row['profile_photo'])): ?>
                                        <a href="uploads/<?= $row['profile_photo'] ?>" target="_blank">
                                            <img src="uploads/<?= $row['profile_photo'] ?>" class="img-thumbnail"
                                                alt="Profile Photo">
                                        </a>
                                        <div class="d-flex justify-content-center">
                                            <a href="uploads/<?= $row['profile_photo'] ?>" download
                                                class="text-primary mx-1 download-btn">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#"
                                                class="text-secondary mx-1 <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                                <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="document.getElementById(\'uploadProfile_' . $row['id'] . '\').click();"' ?>>
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_profile.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="profile_photo" id="uploadProfile_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span>No Photo</span>
                                        <div class="d-flex justify-content-center">
                                            <a href="#" class="text-secondary mx-1 restricted"
                                                onclick="document.getElementById('uploadProfile_<?= $row['id'] ?>').click();">
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_profile.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="profile_photo" id="uploadProfile_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <!-- #region -->
                                <!-- Signature Column -->
                                <td class="text-center">
                                    <?php if (!empty($row['signature'])): ?>
                                        <a href="uploads/<?= $row['signature'] ?>" target="_blank">
                                            <img src="uploads/<?= $row['signature'] ?>" class="img-thumbnail" alt="Signature">
                                        </a>
                                        <div class="d-flex justify-content-center">
                                            <a href="uploads/<?= $row['signature'] ?>" download
                                                class="text-primary mx-1 download-btn">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#"
                                                class="text-secondary mx-1 upload-btn <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                                <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="document.getElementById(\'uploadSignature_' . $row['id'] . '\').click();"' ?> title="Upload Signature">
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_signature.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="signature" id="uploadSignature_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span>No Signature</span>
                                        <div class="d-flex justify-content-center">
                                            <a href="#"
                                                class="text-secondary mx-1 upload-btn <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                                <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="document.getElementById(\'uploadSignature_' . $row['id'] . '\').click();"' ?> title="Upload Signature">
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_signature.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="signature" id="uploadSignature_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="action-buttons">
                                    <!-- Update Button with Company Link -->
                                    <a href="update_member.php?id=<?= $row['id'] ?>&company_link=<?= urlencode($company['company_link']) ?>"
                                        class="btn btn-warning btn-sm <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                        title="Edit Member" <?= $row['status'] == 1 ? 'onclick="return false;"' : '' ?>>
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Delete Button with Confirmation -->
                                    <a href="delete_member.php?id=<?= $row['id'] ?>&company_link=<?= urlencode($company['company_link']) ?>"
                                        class="btn btn-danger btn-sm <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                        <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="return confirm(\'Are you sure you want to delete this member?\');"' ?> title="Delete Member">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>

                                <!-- Status Toggle Column -->
                                <td class="status-cell">
                                    <div class="toggle-container">
                                        <label class="toggle-switch toggle-disabled">
                                            <input type="checkbox" class="status-toggle" data-member-id="<?= $row['id'] ?>"
                                                <?= isset($row['status']) && $row['status'] == 1 ? 'checked' : '' ?> disabled>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span
                                            class="toggle-label <?= isset($row['status']) && $row['status'] == 1 ? 'status-approved' : 'status-pending' ?>">
                                            <?= isset($row['status']) && $row['status'] == 1 ? 'Approved' : 'Pending' ?>
                                        </span>
                                    </div>
                                    <span class="login-hint">(Login required)</span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Fullscreen Table View -->
    <div id="membersTableContainerFullScreen">
        <button class="btn btn-danger close-fullscreen-btn" onclick="closeFullScreen()">
            <i class="bi bi-x-lg"></i> Close
        </button>
        <div class="container mt-5">
            <div class="table-responsive-container">
                <table class="table table-bordered" id="membersTableFullScreen">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Title</th>
                            <th>NIC</th>
                            <th>Reg No</th>
                            <th>Expire</th>
                            <th>Life</th>
                            <th>Remark</th>
                            <th>Photo</th>
                            <th>Signature</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        $result1->data_seek(0);
                        while ($row = $result1->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['name']) ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['title']) ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['nic']) ?></td>
                                <td onclick="copyToClipboard(this)"><?= htmlspecialchars($row['register_no']) ?></td>
                                <td onclick="copyToClipboard(this)">
                                    <?= !empty($row['expire']) ? htmlspecialchars($row['expire']) : 'No' ?>
                                </td>
                                <td onclick="copyToClipboard(this)"><?= $row['lifetime'] ? "Yes" : "No" ?></td>
                                <td class="remark-cell" onclick="copyToClipboard(this)" title="Click to copy"
                                    data-fulltext="<?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : 'No Remark' ?>">
                                    <div class="remark-content">
                                        <?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : 'No Remark' ?>
                                    </div>
                                    <div class="full-remark-popup">
                                        <?= !empty($row['remark']) ? htmlspecialchars($row['remark']) : 'No Remark' ?>
                                    </div>
                                </td>
                                <!-- Profile Photo Column -->
                                <td class="text-center">
                                    <?php if (!empty($row['profile_photo'])): ?>
                                        <a href="uploads/<?= $row['profile_photo'] ?>" target="_blank">
                                            <img src="uploads/<?= $row['profile_photo'] ?>" class="img-thumbnail"
                                                alt="Profile Photo">
                                        </a>
                                        <div class="d-flex justify-content-center">
                                            <a href="uploads/<?= $row['profile_photo'] ?>" download
                                                class="text-primary mx-1 download-btn">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#"
                                                class="text-secondary mx-1 <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                                <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="document.getElementById(\'uploadProfile_' . $row['id'] . '\').click();"' ?>>
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_profile.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="profile_photo" id="uploadProfile_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span>No Photo</span>
                                        <div class="d-flex justify-content-center">
                                            <a href="#" class="text-secondary mx-1 restricted"
                                                onclick="document.getElementById('uploadProfile_<?= $row['id'] ?>').click();">
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_profile.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="profile_photo" id="uploadProfile_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Signature Column -->
                                <td class="text-center">
                                    <?php if (!empty($row['signature'])): ?>
                                        <a href="uploads/<?= $row['signature'] ?>" target="_blank">
                                            <img src="uploads/<?= $row['signature'] ?>" class="img-thumbnail" alt="Signature">
                                        </a>
                                        <div class="d-flex justify-content-center">
                                            <a href="uploads/<?= $row['signature'] ?>" download
                                                class="text-primary mx-1 download-btn">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#"
                                                class="text-secondary mx-1 upload-btn <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                                <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="document.getElementById(\'uploadSignature_' . $row['id'] . '\').click();"' ?> title="Upload Signature">
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_signature.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="signature" id="uploadSignature_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span>Signature</span>
                                        <div class="d-flex justify-content-center">
                                            <a href="#"
                                                class="text-secondary mx-1 upload-btn <?= $row['status'] == 1 ? 'restricted disabled' : 'restricted' ?>"
                                                <?= $row['status'] == 1 ? 'onclick="return false;"' : 'onclick="document.getElementById(\'uploadSignature_' . $row['id'] . '\').click();"' ?> title="Upload Signature">
                                                <i class="fas fa-upload"></i>
                                            </a>
                                            <form action="upload_signature.php" method="POST" enctype="multipart/form-data"
                                                style="display: none;">
                                                <input type="file" name="signature" id="uploadSignature_<?= $row['id'] ?>"
                                                    onchange="this.form.submit();">
                                                <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Status Toggle Column -->
                                <td class="status-cell">
                                    <div class="toggle-container">
                                        <label class="toggle-switch toggle-disabled">
                                            <input type="checkbox" class="status-toggle" data-member-id="<?= $row['id'] ?>"
                                                <?= isset($row['status']) && $row['status'] == 1 ? 'checked' : '' ?> disabled>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span
                                            class="toggle-label <?= isset($row['status']) && $row['status'] == 1 ? 'status-approved' : 'status-pending' ?>">
                                            <?= isset($row['status']) && $row['status'] == 1 ? 'Approved' : 'Pending' ?>
                                        </span>
                                    </div>
                                    <span class="login-hint">(Login required)</span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleFullScreen() {
            // Make sure the fullscreen table has the latest status values
            document.querySelectorAll('.status-toggle').forEach(toggle => {
                const memberId = toggle.dataset.memberId;
                const isChecked = toggle.checked;
                $(`#membersTableFullScreen .status-toggle[data-member-id="${memberId}"]`).prop('checked', isChecked);

                const label = $(`#membersTableFullScreen .status-toggle[data-member-id="${memberId}"]`).closest('.toggle-container').find('.toggle-label');
                if (isChecked) {
                    label.text('Approved').removeClass('status-pending').addClass('status-approved');
                } else {
                    label.text('Pending').removeClass('status-approved').addClass('status-pending');
                }
            });

            document.getElementById('membersTableContainerFullScreen').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeFullScreen() {
            document.getElementById('membersTableContainerFullScreen').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Function to verify password for adding a member (enables table actions only)
        function verifyPasswordForAddMember(companyId) {
            // Check if already verified
            if (sessionStorage.getItem("tableActionsVerified")) {
                window.location.href = "add_member.php?company_id=" + encodeURIComponent(companyId);
                return;
            }

            var password = prompt("Enter the password to enable table actions:");
            if (password === null) return;

            var correctPassword = "Infive@2025"; // Change this to your desired password

            if (password === correctPassword) {
                sessionStorage.setItem("tableActionsVerified", "true");
                enableTableActions();
                window.location.href = "add_member.php?company_id=" + encodeURIComponent(companyId);
            } else {
                alert("Incorrect password! Access denied.");
            }
        }

        // Function to verify password for enabling status toggle updates only
        function verifyPasswordForStatusUpdate() {
            // Check if already verified
            if (sessionStorage.getItem("statusTogglesVerified")) {
                enableStatusToggles();
                return;
            }

            var password = prompt("Enter the password to enable status updates:");
            if (password === null) return;

            var correctPassword = "Test@2025"; // Change this to your desired password

            if (password === correctPassword) {
                sessionStorage.setItem("statusTogglesVerified", "true");
                enableStatusToggles();
                alert("Status update access granted. You can now update member statuses.");
            } else {
                alert("Incorrect password! Access denied.");
            }
        }

        // Function to enable all table actions (upload/download/update/delete)
        function enableTableActions() {
            // Add class to body to show all restricted table action elements
            document.body.classList.add('table-actions-enabled');

            // Change the add member button to show it's enabled
            const addBtn = document.getElementById("addMemberBtn");
            addBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Add Member (Enabled)';
            addBtn.classList.remove("btn-success");
            addBtn.classList.add("btn-primary");
        }

        // Function to enable status toggles only
        function enableStatusToggles() {
            // Add class to body to enable status toggles
            document.body.classList.add('status-toggles-enabled');

            // Remove disabled attribute from all status toggles
            document.querySelectorAll('.status-toggle').forEach(toggle => {
                toggle.disabled = false;
            });

            // Change the "Enable Status Update" button to show it's enabled
            const enableBtn = document.getElementById("enableStatusBtn");
            enableBtn.innerHTML = '<i class="bi bi-toggle-on"></i> Status Enabled';
            enableBtn.classList.remove("btn-info");
            enableBtn.classList.add("btn-success");
            enableBtn.classList.add("btn-disabled");
            enableBtn.onclick = null;

            // Initialize toggle functionality
            initializeToggleSwitches();
        }

        // Initialize toggle switches functionality
        function initializeToggleSwitches() {
            $('.status-toggle').change(function () {
                var memberId = $(this).data('member-id');
                var isChecked = $(this).is(':checked') ? 1 : 0;
                var toggleContainer = $(this).closest('.toggle-container');
                var statusLabel = toggleContainer.find('.toggle-label');
                var row = $(this).closest('tr');

                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: {
                        member_id: memberId,
                        status: isChecked
                    },
                    success: function (response) {
                        // Update the current table
                        if (isChecked) {
                            statusLabel.text('Approved').removeClass('status-pending').addClass('status-approved');
                            // Disable action buttons in this row
                            row.find('.action-buttons a').addClass('disabled').attr('onclick', 'return false;');
                            row.find('.upload-btn').addClass('disabled').attr('onclick', 'return false;');
                        } else {
                            statusLabel.text('Pending').removeClass('status-approved').addClass('status-pending');
                            // Enable action buttons in this row
                            row.find('.action-buttons a').removeClass('disabled');
                            row.find('.upload-btn').removeClass('disabled');
                        }

                        // Update the corresponding toggle in the other table
                        const otherToggle = $(`.status-toggle[data-member-id="${memberId}"]`).not(this);
                        otherToggle.prop('checked', isChecked);

                        const otherLabel = otherToggle.closest('.toggle-container').find('.toggle-label');
                        const otherRow = otherToggle.closest('tr');

                        if (isChecked) {
                            otherLabel.text('Approved').removeClass('status-pending').addClass('status-approved');
                            // Disable action buttons in other table's row
                            otherRow.find('.action-buttons a').addClass('disabled').attr('onclick', 'return false;');
                            otherRow.find('.upload-btn').addClass('disabled').attr('onclick', 'return false;');
                        } else {
                            otherLabel.text('Pending').removeClass('status-approved').addClass('status-pending');
                            // Enable action buttons in other table's row
                            otherRow.find('.action-buttons a').removeClass('disabled');
                            otherRow.find('.upload-btn').removeClass('disabled');
                        }
                    },
                    error: function () {
                        alert('Error updating status');
                        // Revert the toggle if there's an error
                        $(this).prop('checked', !isChecked);
                    }
                });
            });
        }

        // On page load
        document.addEventListener("DOMContentLoaded", function () {
            // Check if table actions were previously verified
            if (sessionStorage.getItem("tableActionsVerified")) {
                enableTableActions();
            }

            // Check if status toggles were previously verified
            if (sessionStorage.getItem("statusTogglesVerified")) {
                enableStatusToggles();
            }
        });

        function copyToClipboard(element) {
            // Get the text content, excluding any child elements like buttons or icons
            let text = element.innerText.trim();

            // For remark cells, use the data-fulltext attribute if available
            if (element.classList.contains('remark-cell')) {
                text = element.getAttribute('data-fulltext');
            }

            // Remove the numbering prefix from name cells (like "1. John Doe")
            if (element.cellIndex === 0) { // If it's the first column (Name)
                text = text.replace(/^\d+\.\s*/, ''); // Remove "1. " prefix
            }

            // Create a temporary textarea element to facilitate copying
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';  // Prevent scrolling to bottom
            document.body.appendChild(textarea);
            textarea.select();

            try {
                // Execute the copy command
                const successful = document.execCommand('copy');
                if (successful) {
                    // Create and show feedback element
                    const feedback = document.createElement('div');
                    feedback.className = 'copied-feedback';
                    feedback.textContent = 'Copied!';
                    element.appendChild(feedback);

                    feedback.style.display = 'block';
                    setTimeout(() => {
                        feedback.style.display = 'none';
                        element.removeChild(feedback);
                    }, 1000);
                }
            } catch (err) {
                console.error('Failed to copy: ', err);
            } finally {
                // Clean up
                document.body.removeChild(textarea);
            }
        }
    </script>
</body>

</html>