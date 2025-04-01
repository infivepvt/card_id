<?php
include 'db.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View companies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #jobTable tbody tr:hover {
            background-color: rgb(172, 232, 250);
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">View companies</h1>
        <div class="mb-4">
            <input type="text" id="searchBar" class="form-control" placeholder="Search by Order Number or Company Name"
                onkeyup="searchJobs()">
        </div>

        <table class="table table-bordered table-striped" id="jobTable">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Company Name</th>
                    <th>Contact Number</th>
                    <th>Date and Time</th>
                    <th>Card Quantity</th>
                    <th>Action</th>
                    <th>Copy Link</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM companies ORDER BY datetime DESC";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <td><?= $row['order_id'] ?></td>
                        <td><?= $row['company_name'] ?></td>
                        <td><?= $row['contact_number'] ?></td>
                        <td><?= $row['datetime'] ?></td>
                        <td><?= $row['card_qty'] ?></td>
                        <td>
                            <a href="view_members.php?id=<?= urlencode($row['company_link']) ?>" class="btn btn-info">View Members</a>
                        </td>
                        <td>
                            <button class="btn btn-secondary"
                                onclick="copyLink('https://infiveprint.com/id/view_members.php?id=<?= $row['company_link'] ?>')">Copy Link</button>
                        </td>
                        <td>
                            <a href="delete_company.php?id=<?= $row['id'] ?>" class="btn btn-danger"
                                onclick="return confirm('WARNING: This will permanently delete the company and ALL its members. Continue?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <!-- Modal for editing company -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="companyId" name="id">
                        <div class="mb-3">
                            <label for="orderId" class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="orderId" name="order_id" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="companyName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="companyName" name="company_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNumber" name="contact_number"
                                pattern="^\d{10}$" title="Enter a valid 10-digit phone number" required>
                            <div id="contactNumberError" class="invalid-feedback">
                                Please enter a valid 10-digit phone number.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cardQty" class="form-label">Card Quantity</label>
                            <input type="number" class="form-control" id="cardQty" name="card_qty" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('contactNumber').addEventListener('input', function (event) {
            const inputField = this;
            inputField.value = inputField.value.replace(/[^0-9]/g, '');
            const contactNumber = inputField.value;
            const contactNumberError = document.getElementById('contactNumberError');
            const regex = /^\d{10}$/;
            if (!regex.test(contactNumber)) {
                contactNumberError.style.display = 'block';
                inputField.classList.add('is-invalid');
            } else {
                contactNumberError.style.display = 'none';
                inputField.classList.remove('is-invalid');
            }
        });

        function copyLink(url) {
            const tempInput = document.createElement('input');
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Link copied to clipboard!');
        }

        document.querySelectorAll('#jobTable tbody tr').forEach(row => {
            row.addEventListener('dblclick', function () {
                const id = this.getAttribute('data-id');
                const orderId = this.cells[0].textContent;
                const companyName = this.cells[1].textContent;
                const contactNumber = this.cells[2].textContent;
                const cardQty = this.cells[4].textContent;

                document.getElementById('companyId').value = id;
                document.getElementById('orderId').value = orderId;
                document.getElementById('companyName').value = companyName;
                document.getElementById('contactNumber').value = contactNumber;
                document.getElementById('cardQty').value = cardQty;

                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            });
        });

        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('update_company.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Company updated successfully!');
                        location.reload();
                    } else {
                        alert('Failed to update company.');
                    }
                })
                .catch(error => alert('Error updating company: ' + error));
        });

        function searchJobs() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const rows = document.querySelectorAll('#jobTable tbody tr');

            rows.forEach(row => {
                const orderNumber = row.cells[0].textContent.toLowerCase();
                const companyName = row.cells[1].textContent.toLowerCase();

                if (orderNumber.includes(searchTerm) || companyName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>
