document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    }
    
    // Close menu when clicking nav links on mobile
    const navItems = document.querySelectorAll('.nav-links li a');
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            if (hamburger.classList.contains('active')) {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });
    });
    
    // Matrix background animation
    function createMatrixBackground() {
        const canvas = document.createElement('canvas');
        document.querySelector('.matrix-bg').appendChild(canvas);
        const ctx = canvas.getContext('2d');
        
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const chars = '01アイウエオカキクケコサシスセソタチツテト';
        const charArr = chars.split('');
        
        const fontSize = 12;
        const columns = canvas.width / fontSize;
        
        const drops = [];
        for (let i = 0; i < columns; i++) {
            drops[i] = Math.floor(Math.random() * -100);
        }
        
        function drawMatrix() {
            ctx.fillStyle = 'rgba(10, 25, 47, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = 'rgba(100, 255, 218, 0.08)';
            ctx.font = `${fontSize}px monospace`;
            
            for (let i = 0; i < drops.length; i++) {
                const text = charArr[Math.floor(Math.random() * charArr.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.99) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }
        
        // Run the matrix animation at a low framerate to save performance
        setInterval(drawMatrix, 50);
        
        // Resize canvas when window resizes
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    }
    
    // Only create the matrix background on devices that can handle it
    if (window.innerWidth > 768) {
        createMatrixBackground();
    }
    
    // Typing effect for terminal
    const terminalLines = document.querySelectorAll('.terminal-content p');
    let lineIndex = 0;
    
    function typeTerminalLine() {
        if (lineIndex < terminalLines.length) {
            terminalLines[lineIndex].style.opacity = '1';
            lineIndex++;
            setTimeout(typeTerminalLine, 800);
        }
    }
    
    // Start typing animation when terminal is in view
    const terminal = document.querySelector('.terminal-window');
    if (terminal) {
        // Hide all lines initially
        terminalLines.forEach(line => {
            line.style.opacity = '0';
            line.style.transition = 'opacity 0.3s ease';
        });
        
        // Start typing animation after a short delay
        setTimeout(typeTerminalLine, 800);
    }
    
    // Handle form submission
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // In a real application, you would handle the form submission to a backend service here
            alert('This is a demo form. In a real application, your message would be sent.');
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80, // Adjust for header height
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Header scroll effect
    const header = document.querySelector('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.style.backgroundColor = 'rgba(10, 25, 47, 0.95)';
            header.style.boxShadow = '0 10px 30px -10px rgba(0, 0, 0, 0.3)';
        } else {
            header.style.backgroundColor = 'rgba(10, 25, 47, 0.9)';
            header.style.boxShadow = 'none';
        }
    });
    
    // Animation for skill bars when they come into view
    const skillBars = document.querySelectorAll('.skill-progress');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const skillObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const width = entry.target.style.width;
                entry.target.style.width = '0';
                setTimeout(() => {
                    entry.target.style.width = width;
                }, 100);
                skillObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    skillBars.forEach(bar => {
        skillObserver.observe(bar);
    });
});
