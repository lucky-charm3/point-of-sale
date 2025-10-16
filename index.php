<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New DF Hotel POS - Point of Sale System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css"/>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-hotel"></i>
                    <h1>New DF Hotel</h1>
                </div>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="auth-buttons">
                    <a href="./auth/login.php" class="btn btn-login">Login</a>
                </div>
            </div>
        </div>
    </header>

    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h2>Revolutionize Your Hotel Management</h2>
                <p> POS system designed specifically for New DF Hotel to streamline operations, increase efficiency, and enhance guest experience.</p>
                <a href="#features" class="btn-large">Discover Features</a>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Designed to meet all your hotel management needs</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3>Sales Management</h3>
                    <p>Handle quick sales, normal sales, and order tracking with our intuitive interface.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Comprehensive Reports</h3>
                    <p>Generate detailed sales, income, inventory, and expense reports with a few clicks.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Money Management</h3>
                    <p>Track expenses, debts, purchases, and banking all in one place.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <h3>Barcode Integration</h3>
                    <p>Easily manage inventory with our barcode scanning and generation system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3>Role Management</h3>
                    <p>Admin, cashier, and manager roles with appropriate permissions for each.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Inventory Tracking</h3>
                    <p>Keep track of your stock levels and receive alerts when items are running low.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About HotelPro POS</h2>
                    <p>HotelPro POS is a comprehensive point of sale system designed specifically for hotels and hospitality businesses. Our system simplifies complex operations, allowing you to focus on providing exceptional guest experiences.</p>
                    <p>With years of industry experience, we've developed a solution that addresses the unique challenges faced by hoteliers. Our system integrates all aspects of hotel management into one seamless platform.</p>
                    <p>Whether you run a small boutique hotel or a large resort, HotelPro POS scales to meet your needs while maintaining the highest standards of reliability and security.</p>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Hotel reception">
                </div>
            </div>
        </div>
    </section>

    
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch</h2>
                <p>Have questions? We'd love to hear from you</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h2>Contact Us</h2>
                    <p>Our team is here to answer any questions you might have about HotelPro POS. Reach out to us and we'll respond as soon as we can.</p>
                    <ul class="contact-details">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Business Avenue, Dar es Salaam, Tanzania</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+255 123 456 789</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>hotel@gmail.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Mon - Fri: 8:00 AM - 6:00 PM</span>
                        </li>
                    </ul>
                </div>
                <div class="contact-form">
                    <form>
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>HotelPro POS</h3>
                    <p>Advanced point of sale system designed specifically for the hospitality industry.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Data Protection</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 HotelPro POS. All rights reserved.</p>
                <p>System designed by methynix software company</p>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        const contactForm = document.querySelector('.contact-form form');
        if(contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value;
                
                if(name && email && subject && message) {
                    alert('Thank you for your message! We will get back to you soon.');
                    contactForm.reset();
                } else {
                    alert('Please fill in all fields.');
                }
            });
        }
    </script>
</body>
</html>