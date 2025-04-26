<?php
include 'includes/header.php';
require_once 'models/ContactMessage.php';

// Initialize variables
$success = false;
$error = '';
$name = $email = $subject = $message = '';

// Check if factory subject is passed
if (isset($_GET['subject']) && !empty($_GET['subject'])) {
    $subject = $_GET['subject'];
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Validate input
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Create a new message using the model
        $contactMessage = new ContactMessage();
        $contactMessage->setName($name);
        $contactMessage->setEmail($email);
        $contactMessage->setSubject($subject);
        $contactMessage->setMessage($message);
        
        if ($contactMessage->create()) {
            $success = true;
            // Reset form after successful submission
            $name = $email = $subject = $message = '';
        } else {
            $error = "Error: Unable to send message. Please try again later.";
        }
    }
}
?>

<div class="container-wide py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="section-title text-center mb-5">Contact Us</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading">Thank You for Contacting Us!</h4>
                    <p>Your message has been sent successfully. We'll get back to you as soon as possible.</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h5>Our Location</h5>
                                <p>123 Industry Street<br>Damascus, Syria</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <h5>Phone Number</h5>
                                <p>+963 11 123 4567<br>+963 11 987 6543</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h5>Email Address</h5>
                                <p>info@syrianfactories.com<br>support@syrianfactories.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="name">Your Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg btn-block" style="background-color: #28a745; border-color: #28a745;">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d106456.24512628337!2d36.24862536010464!3d33.51231299953048!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1518e6dc413cc6a7%3A0x6b9f66ebd1e394f2!2sDamascus%2C%20Syria!5e0!3m2!1sen!2sus!4v1618592064772!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-info-item {
    padding: 15px;
    transition: transform 0.3s ease-in-out;
}

.contact-info-item:hover {
    transform: translateY(-5px);
}

.contact-icon {
    font-size: 2.5rem;
    color:rgb(32, 179, 96);
    margin-bottom: 15px;
}

.contact-info-item h5 {
    font-weight: 600;
    margin-bottom: 10px;
}

.contact-info-item p {
    color: #6c757d;
    margin-bottom: 0;
}
</style>

<?php include 'includes/footer.php'; ?>