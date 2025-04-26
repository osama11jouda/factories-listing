document.addEventListener("DOMContentLoaded", function () {
  // Custom dropdown handling for user menu
  const userMenuToggle = document.getElementById("userMenuToggle");
  const userDropdownMenu = document.getElementById("userDropdownMenu");

  if (userMenuToggle && userDropdownMenu) {
    // Toggle dropdown on click
    userMenuToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      if (userDropdownMenu.classList.contains("show")) {
        userDropdownMenu.classList.remove("show");
      } else {
        userDropdownMenu.classList.add("show");
      }
    });

    // Close dropdown when clicking elsewhere
    document.addEventListener("click", function (e) {
      if (
        userDropdownMenu.classList.contains("show") &&
        !userMenuToggle.contains(e.target) &&
        !userDropdownMenu.contains(e.target)
      ) {
        userDropdownMenu.classList.remove("show");
      }
    });
  }

  // Custom menu toggle functionality
  const menuToggleBtn = document.getElementById("menuToggleBtn");
  const menuContent = document.getElementById("secondNavbarContent");

  if (menuToggleBtn && menuContent) {
    // Initialize menu state
    let menuOpen = false;

    // Add click handler to toggle button
    menuToggleBtn.addEventListener("click", function (e) {
      e.preventDefault();

      if (menuOpen) {
        // Close menu
        menuContent.classList.remove("show");
        this.setAttribute("aria-expanded", "false");
        menuOpen = false;
      } else {
        // Open menu
        menuContent.classList.add("show");
        this.setAttribute("aria-expanded", "true");
        menuOpen = true;
      }
    });

    // Close menu when clicking menu items
    const menuLinks = menuContent.querySelectorAll(".nav-link");
    menuLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        if (menuOpen && window.innerWidth < 992) {
          menuContent.classList.remove("show");
          menuToggleBtn.setAttribute("aria-expanded", "false");
          menuOpen = false;
        }
      });
    });

    // Close menu when clicking outside
    document.addEventListener("click", function (e) {
      if (
        menuOpen &&
        !menuContent.contains(e.target) &&
        e.target !== menuToggleBtn &&
        !menuToggleBtn.contains(e.target)
      ) {
        menuContent.classList.remove("show");
        menuToggleBtn.setAttribute("aria-expanded", "false");
        menuOpen = false;
      }
    });
  }

  // Initialize Bootstrap components
  var tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
  if (tooltips.length > 0) {
    [].forEach.call(tooltips, function (tooltip) {
      new bootstrap.Tooltip(tooltip);
    });
  }

  // Get the current page path
  const currentPath = window.location.pathname;

  // Add active class to current navigation item
  document
    .querySelectorAll(".navbar-nav .nav-item")
    .forEach(function (navItem) {
      const navLink = navItem.querySelector(".nav-link");
      if (navLink && navLink.getAttribute("href") === currentPath) {
        navItem.classList.add("active");
      }
    });

  // Sticky navbar functionality
  const navbar = document.querySelector(".navbar");
  if (navbar) {
    window.addEventListener("scroll", function () {
      if (window.pageYOffset > 50) {
        navbar.classList.add("sticky-navbar");
      } else {
        navbar.classList.remove("sticky-navbar");
      }
    });
  }

  // Smooth dropdown animation
  const dropdownMenus = document.querySelectorAll(".dropdown-menu");
  dropdownMenus.forEach(function (menu) {
    menu.style.transition = "all 0.3s ease";
    menu.style.opacity = "0";
    menu.style.transform = "translateY(10px)";
  });

  document.querySelectorAll(".dropdown").forEach(function (dropdown) {
    dropdown.addEventListener("show.bs.dropdown", function () {
      const menu = dropdown.querySelector(".dropdown-menu");
      setTimeout(function () {
        menu.style.opacity = "1";
        menu.style.transform = "translateY(0)";
      }, 10);
    });

    dropdown.addEventListener("hide.bs.dropdown", function () {
      const menu = dropdown.querySelector(".dropdown-menu");
      menu.style.opacity = "0";
      menu.style.transform = "translateY(10px)";
    });
  });

  // Hero Slider functionality
  const heroSlider = document.querySelector(".hero-slider");
  if (heroSlider) {
    let currentSlide = 0;
    const slides = heroSlider.querySelectorAll(".hero-slide");
    const totalSlides = slides.length;

    // Initialize slider
    if (totalSlides > 0) {
      slides.forEach((slide, index) => {
        if (index !== 0) {
          slide.style.display = "none";
        }
      });

      // Auto-rotate slides
      setInterval(() => {
        slides[currentSlide].style.display = "none";
        currentSlide = (currentSlide + 1) % totalSlides;
        slides[currentSlide].style.display = "block";
      }, 5000);
    }
  }

  // Enhanced Hero Slider functionality
  const heroSliderElements = document.querySelectorAll(".hero-slider");
  heroSliderElements.forEach((heroSlider) => {
    if (heroSlider) {
      let currentSlide = 0;
      const slides = heroSlider.querySelectorAll(".hero-slide");
      const dots = heroSlider.querySelectorAll(".slider-dot");
      const totalSlides = slides.length;

      // Function to change slide
      function goToSlide(slideIndex) {
        // Remove active class from all slides
        slides.forEach((slide) => slide.classList.remove("active"));
        dots.forEach((dot) => dot.classList.remove("active"));

        // Add active class to current slide
        slides[slideIndex].classList.add("active");
        dots[slideIndex].classList.add("active");

        currentSlide = slideIndex;
      }

      // Set up click events for dots
      dots.forEach((dot, index) => {
        dot.addEventListener("click", () => goToSlide(index));
      });

      // Auto slide change
      function nextSlide() {
        goToSlide((currentSlide + 1) % totalSlides);
      }

      // Change slide every 5 seconds
      setInterval(nextSlide, 5000);

      // Initialize first slide
      goToSlide(0);
    }
  });

  // Animation for sections when scrolling
  const animateOnScroll = function () {
    const sections = document.querySelectorAll("section:not(.hero-slider)");

    sections.forEach((section) => {
      const sectionPosition = section.getBoundingClientRect().top;
      const screenPosition = window.innerHeight / 1.3;

      if (sectionPosition < screenPosition) {
        section.style.opacity = "1";
        section.style.transform = "translateY(0)";
      }
    });
  };

  // Initial styling for sections
  document.querySelectorAll("section:not(.hero-slider)").forEach((section) => {
    section.style.opacity = "0";
    section.style.transform = "translateY(30px)";
    section.style.transition = "all 1s ease";
  });

  // Listen for scroll events
  window.addEventListener("scroll", animateOnScroll);

  // Initial check for animations
  animateOnScroll();

  // Form validation
  const forms = document.querySelectorAll(".needs-validation");
  Array.from(forms).forEach((form) => {
    form.addEventListener(
      "submit",
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add("was-validated");
      },
      false
    );
  });

  // Login/Register tab switching
  const authTabs = document.querySelectorAll(".auth-tabs .nav-link");
  if (authTabs.length > 0) {
    authTabs.forEach((tab) => {
      tab.addEventListener("click", function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));

        // Hide all tab contents
        document.querySelectorAll(".tab-pane").forEach((pane) => {
          pane.classList.remove("show", "active");
        });

        // Show the target tab content
        target.classList.add("show", "active");

        // Update active tab
        authTabs.forEach((t) => t.classList.remove("active"));
        this.classList.add("active");
      });
    });
  }

  // Password strength meter for profile page
  const newPasswordInput = document.getElementById("new_password");
  if (newPasswordInput) {
    newPasswordInput.addEventListener("input", function () {
      const password = this.value;
      let strength = 0;
      let color = "#dc3545"; // Default: red/weak

      if (password.length >= 8) strength += 25;
      if (password.match(/[a-z]+/)) strength += 25;
      if (password.match(/[A-Z]+/)) strength += 25;
      if (password.match(/[0-9]+/) || password.match(/[^a-zA-Z0-9]+/))
        strength += 25;

      if (strength > 75) color = "#28a745"; // Strong: green
      else if (strength > 50) color = "#ffc107"; // Medium: yellow
      else if (strength > 25) color = "#fd7e14"; // Weak: orange

      const strengthMeter = document.getElementById(
        "password-strength-meter-fill"
      );
      if (strengthMeter) {
        strengthMeter.style.width = strength + "%";
        strengthMeter.style.backgroundColor = color;

        const helpText = document.getElementById("passwordHelp");
        if (helpText) {
          if (strength <= 25)
            helpText.innerHTML = "Weak password. Try adding more characters.";
          else if (strength <= 50)
            helpText.innerHTML =
              "Fair password. Add uppercase letters or numbers.";
          else if (strength <= 75)
            helpText.innerHTML =
              "Good password. Consider adding special characters.";
          else helpText.innerHTML = "Strong password!";
        }
      }
    });
  }

  /* Factory Details Page Functions */
  function changeMainImage(imagePath, thumbnail) {
    // Update main image
    document.getElementById("mainImage").src = imagePath;

    // Update active thumbnail
    let thumbnails = document.querySelectorAll(".gallery-thumbnail");
    thumbnails.forEach((item) => item.classList.remove("active"));
    thumbnail.classList.add("active");
  }

  // Initialize factory map if available
  const factoryMap = document.getElementById("factory-map");
  if (factoryMap) {
    // Wait for coordinates from PHP
    const lat = parseFloat(factoryMap.getAttribute("data-lat"));
    const lng = parseFloat(factoryMap.getAttribute("data-lng"));

    if (!isNaN(lat) && !isNaN(lng)) {
      // Create map instance
      const map = L.map("factory-map").setView([lat, lng], 14);

      // Add OpenStreetMap tile layer
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "© OpenStreetMap contributors",
      }).addTo(map);

      // Add marker for factory location
      const factoryTitle = factoryMap.getAttribute("data-title");
      L.marker([lat, lng]).addTo(map).bindPopup(factoryTitle).openPopup();

      // Fix for map rendering issues - force a resize after the page loads
      setTimeout(function () {
        map.invalidateSize();
      }, 100);
    }
  }
});
