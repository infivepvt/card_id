<?php
include 'db.php';
date_default_timezone_set('Asia/Colombo');
$current_time = date('Y-m-d\TH:i');  // Format for datetime-local input

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted date or use current time if empty
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d\TH:i');

    // Convert to MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
    $mysql_datetime = date('Y-m-d H:i:s', strtotime($date));

    $order_number = $_POST['order_number'];
    $company_name = $_POST['company_name'];
    $contact_number = $_POST['contact_number'];
    $card_qty = $_POST['card_qty'];

    // Generate a unique link
    $unique_id = uniqid();
    $company_link = "view_members.php?id=" . $unique_id;

    // Check if order number already exists
    $check_sql = "SELECT COUNT(*) FROM companies WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $order_number);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo '<div class="alert alert-danger mt-3">Error: Order number already exists. Please enter a unique order number.</div>';
    } else {
        // Insert into the companies table
        $sql = "INSERT INTO companies (company_name, contact_number, order_id, card_qty, datetime, company_link)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssiss', $company_name, $contact_number, $order_number, $card_qty, $mysql_datetime, $company_link);

        if ($stmt->execute()) {
            header('Location: home.php');
            exit();
        } else {
            echo '<div class="alert alert-danger mt-3">Error: ' . $stmt->error . '</div>';
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-label {
            font-weight: bold;
        }

        .form-control {
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>
    <div class="container mt-5">
        <h1 class="mb-4">Add Company</h1>
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date" class="form-label">Date and Time</label>
                    <input type="datetime-local" name="date" id="date" class="form-control"
                        value="<?php echo $current_time; ?>" min="<?php echo $current_time; ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="order_number" class="form-label">Order Number</label>
                    <input type="text" name="order_number" id="order_number" class="form-control" required
                        pattern="^[A-Za-z0-9]+$" title="Order number can only contain alphanumeric characters.">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" pattern="^\d{10}$"
                        title="Enter a valid 10-digit phone number" required>
                    <div id="contactNumberError" class="invalid-feedback">
                        Please enter a valid 10-digit phone number.
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="card_qty" class="form-label">Card Quantity</label>
                    <input type="number" name="card_qty" id="card_qty" class="form-control" min="1" required>
                    <div id="cardQtyError" class="invalid-feedback">Please enter a valid positive number.</div>

                </div>
            </div>

            <button type="submit" class="btn btn-primary">Add Company</button>
        </form>
    </div>

    <script>
        document.getElementById('contact_number').addEventListener('input', function (event) {
            // Only allow numbers (0-9)
            this.value = this.value.replace(/[^0-9]/g, '');

            // Check if the input matches the 10-digit pattern
            const contactNumber = this.value;
            const contactNumberError = document.getElementById('contactNumberError');

            const regex = /^\d{10}$/;
            if (!regex.test(contactNumber)) {
                contactNumberError.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                contactNumberError.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('card_qty').addEventListener('input', function (event) {
            if (this.value < 1) {
                this.value = "";
                document.getElementById('cardQtyError').style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                document.getElementById('cardQtyError').style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>