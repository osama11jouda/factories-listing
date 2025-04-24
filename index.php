<?php include 'includes/header.php'; ?>

<!-- Full width container to ensure hero slider spans entire width -->
<div class="full-width-container">
    <!-- Hero Slider Section (removed all margin and padding) -->
    <section class="hero-slider ">
        <div class="hero-slide active">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1>Investment Opportunities in Syria</h1>
                    <p class="lead">Discover factories available for sale and rent in Syria</p>
                    <a href="register.php" class="btn btn-primary btn-lg">Explore Opportunities</a>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1>The Best Industrial Properties</h1>
                    <p class="lead">Connect with business owners and find your next industrial venture</p>
                    <a href="register.php" class="btn btn-primary btn-lg">Register Now</a>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1>Professional Industrial Brokerage</h1>
                    <p class="lead">We help you find the perfect factory for your business needs</p>
                    <a href="factories.php" class="btn btn-primary btn-lg">Browse Factories</a>
                </div>
            </div>
        </div>
        <div class="slider-nav">
            <div class="slider-dot active"></div>
            <div class="slider-dot"></div>
            <div class="slider-dot"></div>
        </div>
    </section>
</div>

<!-- About Company Section -->
<section class="about-section container">
    <div class="container-wide">
        <h2 class="section-title">About Our Company</h2>
        <div class="row align-items-center">
            <div class="col-lg-6">
                <p>We are a professional brokerage firm specializing in industrial properties in Syria. Our mission is to connect investors with business owners to facilitate the sale and rental of factories across Syria.</p>
                <p>With years of experience in the Syrian market, we understand the unique challenges and opportunities of investing in industrial properties in the region.</p>
                <a href="about.php" class="btn btn-outline-primary">Learn More About Us</a>
            </div>
            <div class="col-lg-6">
                <img src="images/company-image.jpg" alt="Our Company" class="img-fluid rounded">
            </div>
        </div>
    </div>
</section>

<!-- Registration Required Section -->
<section class="registration-section container">
    <div class="container-wide">
        <h2 class="section-title">Registration Required</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <p class="lead">To view our exclusive investment opportunities, registration on our platform is required.</p>
                <p>We provide carefully vetted industrial properties for serious investors. Once registered, you'll gain access to our complete database of factories available for sale and rent.</p>
                <div class="mt-4">
                    <h5>Registration Benefits:</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success mr-2"></i> Access detailed information about available factories</li>
                        <li><i class="fas fa-check-circle text-success mr-2"></i> View high-quality images of properties</li>
                        <li><i class="fas fa-check-circle text-success mr-2"></i> Connect directly with property owners</li>
                        <li><i class="fas fa-check-circle text-success mr-2"></i> Receive notifications about new listings</li>
                    </ul>
                </div>
                <a href="register.php" class="btn btn-primary btn-lg mt-4">Register Now</a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section container">
    <div class="container-wide">
        <h2 class="section-title">Contact Us</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5><i class="fas fa-map-marker-alt text-primary mr-2"></i> Our Office</h5>
                        <p>Damascus, Syria</p>
                        
                        <h5><i class="fas fa-phone text-primary mr-2"></i> Phone</h5>
                        <p>+963 XX XXXX XXX</p>
                        
                        <h5><i class="fas fa-envelope text-primary mr-2"></i> Email</h5>
                        <p>info@syrianfactories.com</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Send Us a Message</h5>
                        <form action="contact.php" method="post">
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="subject" placeholder="Subject" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="message" placeholder="Your Message" rows="3" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-block" style="background-color: #28a745; border-color: #28a745;">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>