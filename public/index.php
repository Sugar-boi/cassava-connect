<?php
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Background image */
.hero-section {
    background: url('/cassava-connect/uploads/cassava_img.jpg') no-repeat center center;
    background-size: cover;
    height: 80vh;
    /* display: flex; */
    align-items: center;
    position: relative;
}

/* Dark overlay for readability */
.hero-overlay {
    background: rgba(0, 0, 0, 0.5);
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Text styling */
.hero-content {
    text-align: center;
    color: white;
}

.hero-content h1 {
    font-size: 3rem;
    font-weight: bold;
}
.card{
transition:0.3s;
}

.card:hover{
transform:translateY(-8px);
box-shadow:0 10px 25px rgba(0,0,0,0.15);
}
.hero-content{
    max-width: 700px;
    margin: 0 auto;
}




[
</style>

<!-- HERO SECTION -->
<div class="hero-section">
    <div class="hero-overlay">
        <div class="hero-content">

            <h1>Welcome to Cassava Connect 🌱</h1>
            <p class="lead mt-3">
                Buy and sell cassava products — Payment on Delivery.
            </p>

            <div class="mt-4">
                <a href="products.php" class="btn btn-success btn-lg me-3">Browse Products</a>
                <a href="register.php" class="btn btn-light btn-lg">Get Started</a>
            </div>

        </div>
    </div>
</div>

<!-- FEATURES SECTION -->
<div class="container py-5 text-center">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4">
            <i class="bi bi-cart-fill text-success" style="font-size: 40px;"></i>
            <h4 class="text-success">Easy Buying</h4>
            <p>Find cassava products from trusted vendors near you.</p>
        </div>
</div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4">
            <i class="bi bi-arrow-up-right text-success" style="font-size: 40px;"></i>
            <h4 class="text-success">Sell Quickly</h4>
            <p>Vendors can easily list and manage their products.</p>
        </div>
</div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4">
            <i class="bi bi-shield-check text-success" style="font-size: 40px;"></i>
            <h4 class="text-success">Secure Deals</h4>
            <p>Payment on delivery ensures safe transactions.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>