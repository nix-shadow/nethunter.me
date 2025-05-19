document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    hamburger.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        hamburger.classList.toggle('active');
    });

    // Close mobile menu when clicking a nav link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', function() {
            navLinks.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });

    // Scroll to section on click with smooth animation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                const headerHeight = document.querySelector('header').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Header scroll effect
    const header = document.querySelector('header');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Terminal typing animation
    function typeEffect(element, text, speed = 50, startDelay = 0) {
        setTimeout(() => {
            let i = 0;
            const timer = setInterval(() => {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                } else {
                    clearInterval(timer);
                }
            }, speed);
        }, startDelay);
    }

    // Matrix background effect (minimal version)
    function createMatrixEffect() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        document.body.appendChild(canvas);

        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.zIndex = '-1';
        canvas.style.opacity = '0.05';

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const characters = '01';
        const fontSize = 14;
        const columns = canvas.width / fontSize;

        const drops = [];
        for (let i = 0; i < columns; i++) {
            drops[i] = 1;
        }

        function draw() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#00FF41';
            ctx.font = fontSize + 'px monospace';

            for (let i = 0; i < drops.length; i++) {
                const text = characters.charAt(Math.floor(Math.random() * characters.length));
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);

                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }

                drops[i]++;
            }
        }

        setInterval(draw, 33);

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    }

    // Create subtle matrix effect in background
    createMatrixEffect();

    // Form submission with PGP option
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            const encrypt = document.getElementById('encrypt').checked;

            // Basic validation
            if (!name || !email || !subject || !message) {
                alert('Please fill in all required fields.');
                return;
            }

            // Handle PGP encryption (simulated)
            let finalMessage = message;
            if (encrypt) {
                // In a real implementation, this would actually encrypt the message with PGP
                finalMessage = "-----BEGIN PGP MESSAGE-----\n" +
                    btoa(message).match(/.{1,64}/g).join('\n') +
                    "\n-----END PGP MESSAGE-----";

                alert('Your message has been encrypted with PGP. In a real implementation, this would use your actual public key.');
            }

            // Here you would typically send the form data to a server
            // For this example, we're just showing a success message
            alert('Message sent successfully! We will contact you soon.');
            contactForm.reset();
        });
    }

    // Add animation to skill bars
    function animateSkills() {
        const skillBars = document.querySelectorAll('.skill-progress');

        skillBars.forEach(bar => {
            // Get the width from inline style
            const targetWidth = bar.style.width;

            // Reset width to 0
            bar.style.width = '0';

            // Animate to target width
            setTimeout(() => {
                bar.style.transition = 'width 1.5s cubic-bezier(0.1, 0.5, 0.1, 1)';
                bar.style.width = targetWidth;
            }, 200);
        });
    }

    // Check if element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.bottom >= 0
        );
    }

    // Trigger skill animation when skills section comes into view
    const skillsSection = document.querySelector('#skills');
    let animationTriggered = false;

    function checkSkillsVisibility() {
        if (skillsSection && isInViewport(skillsSection) && !animationTriggered) {
            animateSkills();
            animationTriggered = true;
        }
    }

    // Check on scroll and on page load
    window.addEventListener('scroll', checkSkillsVisibility);
    checkSkillsVisibility();

    // Project card hover effects with glitch effect
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';

            // Add glitch effect
            this.classList.add('glitch-effect');
            setTimeout(() => {
                this.classList.remove('glitch-effect');
            }, 500);
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add a cool scanning effect to project images on hover
    const projectImages = document.querySelectorAll('.project-image');
    projectImages.forEach(image => {
        image.addEventListener('mouseenter', function() {
            this.style.position = 'relative';

            const scanLine = document.createElement('div');
            scanLine.style.position = 'absolute';
            scanLine.style.top = '0';
            scanLine.style.left = '0';
            scanLine.style.width = '100%';
            scanLine.style.height = '2px';
            scanLine.style.background = 'rgba(0, 255, 65, 0.8)';
            scanLine.style.boxShadow = '0 0 8px rgba(0, 255, 65, 0.8)';
            scanLine.style.zIndex = '1';
            scanLine.style.animation = 'scan 1.5s linear infinite';

            this.appendChild(scanLine);

            // Add a CSS animation
            const style = document.createElement('style');
            style.type = 'text/css';
            style.innerHTML = `
                @keyframes scan {
                    0% {
                        top: 0;
                    }
                    100% {
                        top: 100%;
                    }
                }
            `;
            document.getElementsByTagName('head')[0].appendChild(style);
        });

        image.addEventListener('mouseleave', function() {
            const scanLine = this.querySelector('div');
            if (scanLine) {
                scanLine.remove();
            }
        });
    });

    // Simulated console message for cybersecurity theme
    console.log("%c🔒 Access Granted: Welcome to nix-shadow's portfolio", "color: #00FF41; font-size: 14px; font-weight: bold;");
    console.log("%cRunning security scan...", "color: #00FF41; font-size: 12px;");
    setTimeout(() => {
        console.log("%c✓ All systems secure", "color: #00FF41; font-size: 12px;");
    }, 2000);
});