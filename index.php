<?php
// ============================================================
// EcoConnect - Professional Homepage
// Revolutionizing Waste Management with On-Demand Collection
// ============================================================
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

// Get stats for homepage
$stats = ['customers' => 0, 'collections' => 0, 'waste_collected' => 0, 'cashiers' => 0];
try {
    $stats['customers'] = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'customer' AND status = 'active'")->fetch()['c'];
    $stats['collections'] = $db->query("SELECT COUNT(*) as c FROM collection_requests WHERE status = 'completed'")->fetch()['c'];
    $stats['cashiers'] = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'cashier' AND status = 'active'")->fetch()['c'];
    $total_waste = $db->query("SELECT COALESCE(SUM(waste_quantity_kg), 0) as t FROM collection_requests WHERE status = 'completed'")->fetch()['t'];
    $stats['waste_collected'] = number_format($total_waste, 0);
} catch (PDOException $e) {}

// Get waste categories
$categories = [];
try {
    $categories = $db->query("SELECT * FROM waste_categories WHERE is_active = 1 ORDER BY category_name")->fetchAll();
} catch (PDOException $e) {}

$page_title = 'EcoConnect - Revolutionizing Waste Management with On-Demand Collection';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/jpg" sizes="32x32" href="favicon-32x32.jpg">
    <link rel="icon" type="image/jpg" sizes="16x16" href="favicon-16x16.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="stylesheet_style.css">
    <style>
        :root { --primary: #2e7d32; --primary-light: #1a936f; --primary-dark: #114b5f; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f9f9f9; }
        
        .hero {
            background: linear-gradient(135deg, rgba(17,75,95,0.95), rgba(46,125,50,0.9)), 
                        url('images/background-leaves.jpg') center/cover no-repeat;
            min-height: 85vh;
            display: flex;
            align-items: center;
            color: white;
            padding: 80px 0;
        }
        .hero h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.2; }
        .hero p { font-size: 1.2rem; opacity: 0.95; margin-bottom: 30px; max-width: 600px; }
        .hero .btn-hero { padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; }
        .hero-stats { margin-top: 50px; display: flex; gap: 40px; flex-wrap: wrap; }
        .hero-stats .stat-item { text-align: center; }
        .hero-stats .stat-number { font-size: 2.5rem; font-weight: 800; }
        .hero-stats .stat-label { font-size: 0.9rem; opacity: 0.8; }
        .hero-image-wrapper { position: relative; perspective: 1000px; }
        .hero-image-wrapper img { border-radius: 20px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); transform: rotateY(-5deg); max-width: 100%; height: auto; }
        
        .section-title { text-align: center; margin-bottom: 60px; }
        .section-title h2 { font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); }
        .section-title p { color: #666; font-size: 1.1rem; max-width: 600px; margin: 15px auto 0; }
        
        .service-card {
            background: white; padding: 35px 25px; border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center;
            transition: all 0.3s; border-bottom: 4px solid transparent; height: 100%;
        }
        .service-card:hover { transform: translateY(-10px); box-shadow: 0 20px 50px rgba(0,0,0,0.1); border-bottom-color: var(--primary); }
        .service-card .icon { font-size: 3rem; color: var(--primary); margin-bottom: 20px; }
        .service-card h4 { font-weight: 700; margin-bottom: 15px; color: var(--primary-dark); }
        .service-card p { color: #666; font-size: 0.95rem; }
        
        .step-card {
            text-align: center; padding: 30px 20px;
            position: relative;
        }
        .step-number {
            width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 1.5rem; font-weight: 700;
        }
        .step-card h4 { font-weight: 700; color: var(--primary-dark); margin-bottom: 15px; }
        .step-card p { color: #666; }
        
        .category-card {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: all 0.3s;
        }
        .category-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .category-card img { width: 100%; height: 200px; object-fit: cover; }
        .category-card .card-body { padding: 20px; text-align: center; }
        .category-card h5 { font-weight: 700; color: var(--primary-dark); }
        .category-card .price { color: var(--primary); font-weight: 600; font-size: 1.1rem; }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white; padding: 80px 0; text-align: center;
        }
        .cta-section h2 { font-size: 2.5rem; font-weight: 700; margin-bottom: 20px; }
        .cta-section p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto; }
        
        .testimonial-card {
            background: white; padding: 30px; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .testimonial-card .stars { color: #ffc107; margin-bottom: 15px; }
        .testimonial-card p { color: #555; font-style: italic; }
        .testimonial-card .author { font-weight: 600; color: var(--primary-dark); margin-top: 15px; }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.2rem; }
            .hero-stats { gap: 20px; }
            .hero-stats .stat-number { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="images/gisellelogo.png" alt="EcoConnect Logo">
                <h1>EcoConnect</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="about_us.php">About</a></li>
                </ul>
            </nav>
            <div class="user-actions">
                <?php if (isLoggedIn()): ?>
                    <?php if (hasRole('admin')): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary">Dashboard</a>
                    <?php elseif (hasRole('cashier')): ?>
                        <a href="cashier/dashboard.php" class="btn btn-primary">Dashboard</a>
                    <?php else: ?>
                        <a href="customer/dashboard.php" class="btn btn-primary">Dashboard</a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Revolutionizing Waste Management with On-Demand Collection</h1>
                    <p>Join EcoConnect and be part of the solution. Schedule waste pickups, track collections, and contribute to a cleaner, greener environment — all from one platform.</p>
                    <div>
                        <?php if (!isLoggedIn()): ?>
                            <a href="auth/register.php" class="btn btn-light btn-hero">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </a>
                        <?php else: ?>
                            <a href="customer/request.php" class="btn btn-light btn-hero">
                                <i class="fas fa-plus-circle me-2"></i>Request Pickup
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['customers'] ?>+</div>
                            <div class="stat-label">Happy Customers</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['collections'] ?>+</div>
                            <div class="stat-label">Collections Done</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['waste_collected'] ?> kg</div>
                            <div class="stat-label">Waste Collected</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <div class="hero-image-wrapper">
                        <img src="images/mix%20waste%201.jpg" alt="Waste Management" onerror="this.src='images/gisellelogo.png'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" style="padding: 80px 0;">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>Comprehensive waste management solutions tailored for homes, businesses, and communities</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-truck"></i></div>
                        <h4>On-Demand Collection</h4>
                        <p>Schedule waste pickups at your convenience. Choose your preferred date and time, and we'll handle the rest.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-recycle"></i></div>
                        <h4>Waste Segregation</h4>
                        <p>We collect and process different waste categories separately to maximize recycling and minimize environmental impact.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <h4>Digital Payments</h4>
                        <p>Pay securely through multiple payment methods including Mobile Money, Bank Transfer, and Cash. Get digital receipts instantly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-chart-line"></i></div>
                        <h4>Real-Time Tracking</h4>
                        <p>Track your collection requests in real-time from pending to completion. Get notified at every step.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <h4>Community Impact</h4>
                        <p>Join a growing community working towards a cleaner environment. Every collection contributes to a sustainable future.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="icon"><i class="fas fa-file-pdf"></i></div>
                        <h4>Digital Receipts</h4>
                        <p>Receive professional printable receipts and PDF downloads for all your waste collection payments.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" style="padding: 80px 0; background: #f0f7f4;">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Getting started with EcoConnect is simple. Follow these steps to schedule your first waste collection.</p>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Create Account</h4>
                        <p>Register as a customer and set up your profile with your contact details and address.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Submit Request</h4>
                        <p>Select waste category, enter quantity, choose pickup date and location.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Collection</h4>
                        <p>Our team arrives at your location to collect the waste at the scheduled time.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Payment & Receipt</h4>
                        <p>Pay for the service and receive a professional receipt. Easy and transparent.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Waste Categories -->
    <section id="categories" style="padding: 80px 0;">
        <div class="container">
            <div class="section-title">
                <h2>Waste Categories We Collect</h2>
                <p>We accept a wide range of waste types. Check the categories below to see what we collect.</p>
            </div>
            <div class="row g-4">
                <?php
                $category_images = [
                    'Plastic' => 'images/plastic.jpg',
                    'Paper' => 'images/paper.jpg',
                    'Glass' => 'images/glass.jpg',
                    'Electronic' => 'images/electronic.jpg',
                    'Organic' => 'images/organic.jpg',
                    'Metal' => 'images/metal.jpg',
                    'Textile' => 'images/mix waste 2.jpg'
                ];
                $i = 0;
                if (count($categories) > 0):
                    foreach ($categories as $cat):
                        $img = $category_images[explode(' ', $cat['category_name'])[0]] ?? 'images/mix waste 1.jpg';
                        if ($i >= 6) break; $i++;
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="category-card">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($cat['category_name']) ?>" onerror="this.src='images/gisellelogo.png'">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($cat['category_name']) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars(substr($cat['category_description'] ?? '', 0, 50)) ?></p>
                            <div class="price"><?= formatCurrency($cat['price_per_kg']) ?> / kg</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Waste categories are being set up. Check back soon!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Make a Difference?</h2>
            <p>Join EcoConnect today and take the first step towards responsible waste management. Together, we can create a cleaner, greener future for our communities.</p>
            <p class="mb-0">Sign in to request a pickup or create an account to start managing waste collection services easily.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/gisellelogo.png" alt="EcoConnect Logo">
                    <h3>EcoConnect</h3>
                    <p>Revolutionizing waste management with on-demand collection services for a cleaner, greener environment.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#categories">Categories</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="about_us.php">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope me-2"></i>uwajenezagiselle@gmail.com</p>
                    <p><i class="fas fa-phone me-2"></i>+250780777252</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Kigali, Rwanda</p>
                    <p><i class="fas fa-clock me-2"></i>Mon-Sat 8:00 AM - 6:00 PM</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 EcoConnect. All rights reserved. | Waste Management Solutions</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>