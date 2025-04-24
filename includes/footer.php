</main>
    <footer class="footer-section">
        <!-- Back to top button -->
        <div class="back-to-top">
            <a href="#" id="back-to-top-btn"><i class="fas fa-arrow-up"></i></a>
        </div>

        <!-- Main footer content -->
        <div class="footer-main py-5">
            <div class="container">
                <div class="row">
                    <!-- About column -->
                    <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                        <h4 class="footer-heading">Syrian Factories</h4>
                        <p class="footer-text">Your gateway to investment opportunities in Syria. We connect investors with the best factory opportunities across the country.</p>
                        <div class="social-links mt-4">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    
                    <!-- Quick links column -->
                    <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                        <h4 class="footer-heading">Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="factories.php">Factories</a></li>
                            <li><a href="about.php">About Us</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact column -->
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <h4 class="footer-heading">Contact Us</h4>
                        <ul class="footer-contact">
                            <li><i class="fas fa-map-marker-alt"></i> Damascus, Syria</li>
                            <li><i class="fas fa-phone"></i> +963 XX XXXX XXX</li>
                            <li><i class="fas fa-envelope"></i> info@syrianfactories.com</li>
                            <li><i class="fas fa-clock"></i> Mon-Fri: 9:00 AM - 5:00 PM</li>
                        </ul>
                    </div>
                    
                    <!-- Newsletter column -->
                    <div class="col-lg-3 col-md-6">
                        <h4 class="footer-heading">Newsletter</h4>
                        <p class="footer-text">Subscribe to our newsletter to receive updates on new factory listings.</p>
                        <form class="footer-newsletter mt-3">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email" aria-label="Your Email">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright section -->
        <div class="footer-bottom py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; <?php echo date('Y'); ?> Syrian Factories. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <div class="footer-links-secondary">
                            <a href="#">Privacy Policy</a>
                            <a href="#">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Footer Styling -->
    <style>
    .footer-section {
        background-color: #2d3e50;
        color: rgba(255, 255, 255, 0.8);
        position: relative;
    }
    
    .footer-main {
        background-color: #2d3e50;
    }
    
    .footer-bottom {
        background-color: #212e3c;
        font-size: 0.9rem;
    }
    
    .footer-heading {
        color: white;
        font-size: 1.3rem;
        margin-bottom: 1.2rem;
        position: relative;
        padding-bottom: 0.8rem;
    }
    
    .footer-heading::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background-color: #28a745;
    }
    
    .footer-text {
        margin-bottom: 1rem;
        line-height: 1.6;
    }
    
    .social-links {
        display: flex;
    }
    
    .social-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        margin-right: 0.7rem;
        color: white;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background-color: #28a745;
        color: white;
        transform: translateY(-3px);
    }
    
    .footer-links {
        list-style: none;
        padding-left: 0;
    }
    
    .footer-links li {
        margin-bottom: 0.8rem;
    }
    
    .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        position: relative;
        padding-left: 15px;
    }
    
    .footer-links a:before {
        content: "›";
        position: absolute;
        left: 0;
        font-size: 1.2rem;
        color: #28a745;
        transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
        color: white;
        text-decoration: none;
        padding-left: 20px;
    }
    
    .footer-contact {
        list-style: none;
        padding-left: 0;
    }
    
    .footer-contact li {
        margin-bottom: 0.8rem;
        display: flex;
        align-items: flex-start;
    }
    
    .footer-contact li i {
        color: #28a745;
        width: 20px;
        margin-right: 10px;
        margin-top: 4px;
    }
    
    .footer-newsletter .form-control {
        background-color: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
    }
    
    .footer-newsletter .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }
    
    .footer-newsletter .btn-primary {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .footer-newsletter .btn-primary:hover {
        background-color: #218838;
        border-color: #218838;
    }
    
    .footer-links-secondary {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    
    .footer-links-secondary a {
        color: rgba(255, 255, 255, 0.6);
        margin-left: 1.5rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .footer-links-secondary a:hover {
        color: white;
        text-decoration: none;
    }
    
    .back-to-top {
        position: absolute;
        top: -25px;
        right: 25px;
    }
    
    #back-to-top-btn {
        width: 50px;
        height: 50px;
        background-color: #28a745;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    #back-to-top-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    @media (max-width: 767px) {
        .footer-links-secondary {
            justify-content: flex-start;
            margin-top: 0.5rem;
        }
        
        .footer-links-secondary a {
            margin-left: 0;
            margin-right: 1.5rem;
        }
        
        .back-to-top {
            right: 15px;
        }
    }
    </style>

    <!-- Back to top script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopBtn = document.getElementById('back-to-top-btn');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.opacity = '1';
            } else {
                backToTopBtn.style.opacity = '0';
            }
        });
        
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>