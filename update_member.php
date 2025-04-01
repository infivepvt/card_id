<?php
include 'db.php';

// Get member ID and company link from URL
$member_id = $_GET['id'] ?? null;
$company_link = $_GET['company_link'] ?? '';

if (!$member_id) {
    die("Member ID not provided!");
}

// Fetch member details
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Initialize expire with empty string if not set
$member['expire'] = $member['expire'] ?? '';

// NIC Validation function
function validateNIC($nic) {
    // Remove any whitespace
    $nic = trim($nic);
    
    // Check for old NIC format (9 digits with V/X) or new format (12 digits)
    if (preg_match('/^[0-9]{9}[vVxX]$/', $nic)) {
        return true; // Valid old NIC
    } elseif (preg_match('/^[0-9]{12}$/', $nic)) {
        return true; // Valid new NIC
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form submission
    $name = $_POST['name'];
    $title = $_POST['title'];
    $nic = $_POST['nic'];
    $register_no = $_POST['register_no'];
    $expire = $_POST['expire'] ?? ''; // Handle case when expire is not set
    $lifetime = isset($_POST['lifetime']) ? 1 : 0;
    $remark = $_POST['remark'];
    $company_link = $_POST['company_link'];
    
    // Validate NIC
    if (!validateNIC($nic)) {
        echo "<script>alert('Invalid NIC format! Please use either 9 digits with V/X or 12 digits.');</script>";
    } else {
        // Update member in database
        $update_sql = "UPDATE members SET name=?, title=?, nic=?, register_no=?, expire=?, lifetime=?, remark=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssssi", $name, $title, $nic, $register_no, $expire, $lifetime, $remark, $member_id);
        
        if ($stmt->execute()) {
            echo "<script>
                alert('Member updated successfully!');
                window.location.href = 'view_members.php?id=".urlencode($company_link)."';
            </script>";
            exit();
        } else {
            echo "<script>alert('Error updating member!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875em;
        }
        .is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h3 class="mb-4 text-center">Update Member Details</h3>
            
            <form method="POST" id="memberForm">
                <input type="hidden" name="company_link" value="<?= htmlspecialchars($company_link) ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($member['name']) ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?= htmlspecialchars($member['title']) ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">NIC Number <small class="text-muted">(Format: 123456789V or 123456789012)</small></label>
                        <input type="text" name="nic" id="nic" class="form-control" 
                               value="<?= htmlspecialchars($member['nic']) ?>" 
                               pattern="^([0-9]{9}[vVxX]|[0-9]{12})$" required>
                        <div class="invalid-feedback" id="nicError">
                            Please enter a valid NIC (9 digits with V/X or 12 digits)
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Register No</label>
                        <input type="text" name="register_no" class="form-control" 
                               value="<?= htmlspecialchars($member['register_no']) ?>" required>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="lifetime" id="lifetime" 
                                  <?= $member['lifetime'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="lifetime">Lifetime</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expire" id="expire" class="form-control" 
                               value="<?= htmlspecialchars($member['expire']) ?>" 
                               <?= $member['lifetime'] ? 'disabled' : '' ?>>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remark" class="form-control" rows="2"><?= 
                            htmlspecialchars($member['remark']) ?></textarea>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary px-4">Update</button>
                            <a href="view_members.php?id=<?= urlencode($company_link) ?>" 
                               class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle lifetime checkbox change
        document.getElementById('lifetime').addEventListener('change', function() {
            const expireField = document.getElementById('expire');
            expireField.disabled = this.checked;
            if (this.checked) expireField.value = '';
        });
        
        // Initialize form state
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('lifetime').checked) {
                document.getElementById('expire').disabled = true;
            }
        });
        
        // NIC Validation
        document.getElementById('memberForm').addEventListener('submit', function(e) {
            const nicInput = document.getElementById('nic');
            const nicError = document.getElementById('nicError');
            const nicRegex = /^([0-9]{9}[vVxX]|[0-9]{12})$/;
            
            if (!nicRegex.test(nicInput.value)) {
                e.preventDefault();
                nicInput.classList.add('is-invalid');
                nicError.style.display = 'block';
            } else {
                nicInput.classList.remove('is-invalid');
                nicError.style.display = 'none';
            }
        });
        
        // Live NIC validation as user types
        document.getElementById('nic').addEventListener('input', function() {
            const nicInput = this;
            const nicError = document.getElementById('nicError');
            const nicRegex = /^([0-9]{9}[vVxX]|[0-9]{12})$/;
            
            if (nicRegex.test(nicInput.value)) {
                nicInput.classList.remove('is-invalid');
                nicError.style.display = 'none';
            } else {
                nicInput.classList.add('is-invalid');
                nicError.style.display = 'block';
            }
        });
    </script>
</body>
</html>