<?php
include 'db.php';

// Validate if company_id exists in URL
if (!isset($_GET['company_id']) || empty($_GET['company_id'])) {
    die("Invalid company ID!");
}

$company_link = $_GET['company_id'];

// Get company_id using company_link
$stmt = $conn->prepare("SELECT id FROM companies WHERE company_link = ?");
$stmt->bind_param("s", $company_link);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: Invalid company reference!");
} else {
    $company = $result->fetch_assoc();
    $company_id = $company['id'];
}

// Validation functions
function validateName($name) {
    if (empty($name)) {
        return "Name is required";
    }
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        return "Only letters and spaces allowed";
    }
    if (strlen($name) > 100) {
        return "Name must be less than 100 characters";
    }
    return true;
}

function validateTitle($title) {
    if (empty($title)) {
        return "Title is required";
    }
    if (strlen($title) > 50) {
        return "Title must be less than 50 characters";
    }
    return true;
}

function validateNIC($nic) {
    if (empty($nic)) {
        return "NIC is required";
    }
    // Validate both old (9 digits) and new (12 digits) NIC formats
    if (!preg_match("/^([0-9]{9}[vVxX]|[0-9]{12})$/", $nic)) {
        return "Invalid NIC format (ex: 123456789V or 123456789012)";
    }
    return true;
}

// Function to generate a unique filename
function generateUniqueFileName($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

$allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $name = trim($_POST['name']);
    $title = trim($_POST['title']);
    $nic = trim($_POST['nic']);
    
    // Validate name
    $nameValidation = validateName($name);
    if ($nameValidation !== true) {
        $errors['name'] = $nameValidation;
    }
    
    // Validate title
    $titleValidation = validateTitle($title);
    if ($titleValidation !== true) {
        $errors['title'] = $titleValidation;
    }
    
    // Validate NIC
    $nicValidation = validateNIC($nic);
    if ($nicValidation !== true) {
        $errors['nic'] = $nicValidation;
    }
    
    // Only proceed if no validation errors
    if (empty($errors)) {
        $name = htmlspecialchars($name);
        $title = htmlspecialchars($title);
        $nic = htmlspecialchars($nic);
        $register_no = htmlspecialchars($_POST['register_no']);
        $expire = !empty($_POST['expire']) ? htmlspecialchars($_POST['expire']) : NULL;
        $lifetime = isset($_POST['lifetime']) ? 1 : 0;
        $remark = !empty($_POST['remark']) ? htmlspecialchars($_POST['remark']) : NULL;

        // File uploads
        $target_dir = "uploads/";
        
        // Profile Photo Upload
        if (!empty($_FILES['profile_photo']['name'])) {
            $profile_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($profile_ext, $allowed_file_types)) {
                $errors['profile_photo'] = "Invalid profile photo format. Allowed formats: jpg, jpeg, png, gif";
            } else {
                $profile_photo = generateUniqueFileName($_FILES['profile_photo']['name']);
                $profile_photo_path = $target_dir . $profile_photo;
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $profile_photo_path);
            }
        } else {
            $errors['profile_photo'] = "Profile photo is required!";
        }

        // Signature Upload (Optional)
        $signature = NULL;
        if (!empty($_FILES['signature']['name'])) {
            $signature_ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
            if (!in_array($signature_ext, $allowed_file_types)) {
                $errors['signature'] = "Invalid signature format. Allowed formats: jpg, jpeg, png, gif";
            } else {
                $signature = generateUniqueFileName($_FILES['signature']['name']);
                $signature_path = $target_dir . $signature;
                move_uploaded_file($_FILES['signature']['tmp_name'], $signature_path);
            }
        }

        // Insert into database if no errors
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO members (company_id, name, title, nic, register_no, expire, lifetime, remark, profile_photo, signature) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssisss", $company_id, $name, $title, $nic, $register_no, $expire, $lifetime, $remark, $profile_photo, $signature);

            if ($stmt->execute()) {
                header("Location: view_members.php?id=$company_link");
                exit();
            } else {
                $errors['database'] = "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .lifetime-container {
            margin-top: 5px;
            font-weight: bold;
            margin-left: 10px;
        }
        .form-check-input {
            width: 30px;
            height: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Add New Member</h2>
        
        <?php if (!empty($errors['database'])): ?>
            <div class="alert alert-danger"><?= $errors['database'] ?></div>
        <?php endif; ?>
        
        <form action="add_member.php?company_id=<?= htmlspecialchars($company_link) ?>" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company_id) ?>">

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span>:</label>
                    <input type="text" name="name" id="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                           placeholder="Enter member's name" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span>:</label>
                    <input type="text" name="title" id="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                           value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                           placeholder="Enter member's title" required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="error-message"><?= $errors['title'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="nic" class="form-label">NIC <span class="text-danger">*</span>:</label>
                    <input type="text" name="nic" id="nic" class="form-control <?= isset($errors['nic']) ? 'is-invalid' : '' ?>" 
                           value="<?= isset($_POST['nic']) ? htmlspecialchars($_POST['nic']) : '' ?>" 
                           placeholder="Enter NIC (123456789V or 123456789012)" required>
                    <?php if (isset($errors['nic'])): ?>
                        <div class="error-message"><?= $errors['nic'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="register_no" class="form-label">Register No <span class="text-danger">*</span>:</label>
                    <input type="text" name="register_no" id="register_no" class="form-control" 
                           value="<?= isset($_POST['register_no']) ? htmlspecialchars($_POST['register_no']) : '' ?>" 
                           placeholder="Enter register number" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="profile_photo" class="form-label">Profile Photo <span class="text-danger">*</span>:</label>
                    <input type="file" name="profile_photo" accept="image/*" class="form-control <?= isset($errors['profile_photo']) ? 'is-invalid' : '' ?>" required>
                    <?php if (isset($errors['profile_photo'])): ?>
                        <div class="error-message"><?= $errors['profile_photo'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="signature" class="form-label">Signature:</label>
                    <input type="file" name="signature" accept="image/*" class="form-control <?= isset($errors['signature']) ? 'is-invalid' : '' ?>">
                    <?php if (isset($errors['signature'])): ?>
                        <div class="error-message"><?= $errors['signature'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label for="remark" class="form-label">Remark:</label>
                    <textarea name="remark" id="remark" class="form-control" rows="3" placeholder="Any additional remarks"><?= isset($_POST['remark']) ? htmlspecialchars($_POST['remark']) : '' ?></textarea>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">                  
                    <h5>The warranty period you need for this card...</h5>
                    <br>
                    <input type="checkbox" name="lifetime" class="form-check-input" value="1" id="lifetime" <?= isset($_POST['lifetime']) ? 'checked' : '' ?>>
                    <label for="lifetime" class="lifetime-container">Life time</label>
                    <br><br>
                    <label for="expire" class="form-label">Expire Date:</label>
                    <input type="date" name="expire" id="expire" class="form-control" 
                           value="<?= isset($_POST['expire']) ? htmlspecialchars($_POST['expire']) : '' ?>"
                           <?= isset($_POST['lifetime']) ? 'disabled' : '' ?>>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Add Member</button>
        </form>
    </div>

    <!-- Lifetime JavaScript -->
    <script>
        document.getElementById('lifetime').addEventListener('change', function () {
            var expireDateField = document.getElementById('expire');
            if (this.checked) {
                expireDateField.disabled = true;
                expireDateField.value = '';
            } else {
                expireDateField.disabled = false;
            }
        });
    </script>
</body>
</html>