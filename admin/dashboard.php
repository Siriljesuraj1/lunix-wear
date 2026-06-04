<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LUNIX WEAR</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="/assets/images/logo-main.png" alt="LUNIX WEAR">
                <h3>ADMIN</h3>
            </div>
            
            <nav class="admin-nav">
                <a href="/admin/dashboard.php" class="nav-item active">📊 Dashboard</a>
                <a href="/admin/products.php" class="nav-item">📦 Products</a>
                <a href="/admin/orders.php" class="nav-item">📋 Orders</a>
                <a href="/admin/reviews.php" class="nav-item">⭐ Reviews</a>
                <a href="/admin/custom-orders.php" class="nav-item">🎨 Custom Orders</a>
                <a href="/admin/bulk-orders.php" class="nav-item">📦 Bulk Orders</a>
                <a href="/admin/users.php" class="nav-item">👥 Users</a>
                <a href="/admin/settings.php" class="nav-item">⚙️ Settings</a>
                <a href="/auth/logout.php" class="nav-item logout">🚪 Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                </div>
            </header>

            <!-- Dashboard Stats -->
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number" id="total-orders">0</p>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p class="stat-number" id="total-revenue">₹0</p>
                </div>
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p class="stat-number" id="total-products">0</p>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="stat-number" id="total-users">0</p>
                </div>
            </section>

            <!-- Recent Orders -->
            <section class="dashboard-section">
                <h2>Recent Orders</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-table">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
