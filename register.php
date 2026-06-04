<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LUNIX WEAR</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'components/header.php'; ?>

    <section style="padding: 4rem 2rem; max-width: 500px; margin: 2rem auto;">
        <div class="card">
            <h2 style="text-align: center;">Create Account</h2>
            
            <form id="register-form">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone (India)</label>
                    <input type="text" id="phone" name="phone" placeholder="10-digit number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: #666;">Min 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>

                <p style="text-align: center; margin-top: 1rem;">
                    Already have account? <a href="/login.php" style="color: var(--primary-gold);">Login</a>
                </p>
            </form>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <script>
        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

            try {
                const response = await fetch('/auth/register_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert('Registration successful! Please login.');
                    window.location.href = '/login.php';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Registration failed. Please try again.');
            }
        });
    </script>
</body>
</html>
