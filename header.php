<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">ID Card Details</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home.php') ? 'active' : ''; ?>" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'add_new_company.php') ? 'active' : ''; ?>" href="add_new_company.php">Add Company</a>
                </li>
               
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<style>
   
    .navbar {
        border-bottom: 2px solid #ddd;
        padding: 1rem 0; 
        transition: background-color 0.3s ease; 
    }
    .navbar-light .navbar-nav .nav-link {
        font-size: 1.1rem;
        font-weight: 500;
        transition: color 0.3s ease-in-out, background-color 0.3s ease-in-out; 
    }  
    .navbar-light .navbar-nav .nav-link:hover {
        color: #fff; 
        background-color: #0056b3; 
        text-decoration: none; 
        border-radius: 5px; 
    }  
    .navbar-nav .nav-link.active {
        color: #fff; 
        background-color: rgb(103, 166, 233); 
        font-weight: 700;
        border-radius: 5px; 
    }
    .navbar-toggler-icon {
        background-color: rgb(255, 255, 255); 
    }   
    .navbar-brand {
        font-size: 1.3rem;
        font-weight: 700;
        letter-spacing: 2px; 
    }   
    @media (min-width: 992px) {
        .navbar-nav .nav-item {
            margin-left: 1rem; 
        }
    }  
    .navbar:hover {
        background-color: #f8f9fa; 
    }
</style>
