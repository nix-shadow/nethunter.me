document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking a nav link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', function() {
            navLinks.classList.remove('active');
            if (hamburger) hamburger.classList.remove('active');
        });
    });
    
    // Smooth scroll to sections
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
    
    // Form submission handling
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            const encrypt = document.getElementById('encrypt')?.checked || false;
            
            // Basic validation
            if (!name || !email || !subject || !message) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Here you would typically send the form data to a server
            alert('Message sent successfully! I will contact you soon.');
            contactForm.reset();
        });
    }
    
    // Skill bars animation
    function animateSkills() {
        const skillBars = document.querySelectorAll('.skill-progress');
        
        skillBars.forEach(bar => {
            // Get the width from inline style
            const targetWidth = bar.style.width;
            
            // Reset width to 0
            bar.style.width = '0';
            
            // Animate to target width
            setTimeout(() => {
                bar.style.transition = 'width 1s ease';
                bar.style.width = targetWidth;
            }, 300);
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
    // Initial check
    setTimeout(checkSkillsVisibility, 500);
    
    // Simple matrix background effect
    function createMatrixEffect() {
        const canvas = document.createElement('canvas');
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
        
        if (canvas.getContext) {
            const ctx = canvas.getContext('2d');
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
        }
        
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    }
    
    // Initialize matrix effect with a slight delay
    setTimeout(createMatrixEffect, 1000);
    
    // Blinking cursor effect
    const blinkElements = document.querySelectorAll('.blink');
    blinkElements.forEach(el => {
        el.classList.add('blink');
    });
    
    // Log a message to console for fun
    console.log("%c🔒 Access Granted: Welcome to nix-shadow's portfolio", "color: #00FF41; font-size: 14px; font-weight: bold;");
    console.log("%cRunning security scan...", "color: #00FF41; font-size: 12px;");
});
