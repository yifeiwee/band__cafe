// Band Cafe - Enhanced UI JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all enhancements
    initAnimations();
    initFormEnhancements();
    initTooltips();
    initNotifications();
    initThemeEffects();
});

// Animation initializations
function initAnimations() {
    // Add entrance animations to cards
    const cards = document.querySelectorAll('.bg-white, .glass');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-fade-in');
    });

    // Add floating animation to decorative elements
    const musicIcons = document.querySelectorAll('svg');
    musicIcons.forEach(icon => {
        if (icon.closest('.floating, .floating-delayed, .floating-slow')) {
            return; // Skip if already has floating class
        }
        
        // Randomly assign floating classes to some icons
        if (Math.random() > 0.7) {
            const floatingClasses = ['floating', 'floating-delayed', 'floating-slow'];
            const randomClass = floatingClasses[Math.floor(Math.random() * floatingClasses.length)];
            icon.classList.add(randomClass);
        }
    });
}

// Form enhancements
function initFormEnhancements() {
    // Real-time input validation
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateInput(this);
            }
        });
    });

    // Enhanced checkbox interactions
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            if (label) {
                if (this.checked) {
                    label.classList.add('checked');
                    addRippleEffect(label);
                } else {
                    label.classList.remove('checked');
                }
            }
        });
    });
}

// Input validation
function validateInput(input) {
    const value = input.value.trim();
    const isRequired = input.hasAttribute('required');
    let isValid = true;

    // Remove existing error states
    input.classList.remove('error', 'border-red-500');
    const errorMsg = input.parentNode.querySelector('.error-message');
    if (errorMsg) errorMsg.remove();

    if (isRequired && !value) {
        isValid = false;
        showInputError(input, 'This field is required');
    } else if (input.type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        showInputError(input, 'Please enter a valid email address');
    } else if (input.type === 'time' && value && !isValidTime(value)) {
        isValid = false;
        showInputError(input, 'Please enter a valid time');
    }

    if (isValid) {
        input.classList.add('border-green-500');
        setTimeout(() => input.classList.remove('border-green-500'), 2000);
    }

    return isValid;
}

function showInputError(input, message) {
    input.classList.add('error', 'border-red-500');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message text-red-500 text-sm mt-1 message-slide-in';
    errorDiv.textContent = message;
    
    input.parentNode.appendChild(errorDiv);
}

function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });

    return isValid;
}

// Utility validation functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidTime(time) {
    const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
    return timeRegex.test(time);
}

// Notification system
function initNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    
    const typeClasses = {
        success: 'bg-green-500 border-green-600',
        error: 'bg-red-500 border-red-600',
        warning: 'bg-yellow-500 border-yellow-600',
        info: 'bg-blue-500 border-blue-600'
    };

    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    notification.className = `
        ${typeClasses[type]} text-white px-6 py-4 rounded-xl shadow-lg border 
        transform translate-x-full transition-transform duration-300 
        flex items-center space-x-3 max-w-sm
    `;

    notification.innerHTML = `
        <span class="text-xl">${icons[type]}</span>
        <span class="flex-1">${message}</span>
        <button class="text-white hover:text-gray-200 ml-2" onclick="closeNotification(this.parentNode)">✕</button>
    `;

    container.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove
    if (duration > 0) {
        setTimeout(() => {
            closeNotification(notification);
        }, duration);
    }
}

function closeNotification(notification) {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

// Ripple effect for interactive elements
function addRippleEffect(element) {
    const ripple = document.createElement('span');
    ripple.className = 'absolute inset-0 bg-white opacity-25 rounded-full scale-0';
    ripple.style.animation = 'ripple 0.6s ease-out';
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.parentNode.removeChild(ripple);
        }
    }, 600);
}

// Add ripple animation CSS
if (!document.querySelector('#ripple-style')) {
    const style = document.createElement('style');
    style.id = 'ripple-style';
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Tooltip system
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const text = e.target.getAttribute('data-tooltip');
    if (!text) return;

    const tooltip = document.createElement('div');
    tooltip.id = 'tooltip';
    tooltip.className = 'absolute z-50 bg-gray-900 text-white px-3 py-2 rounded-lg text-sm pointer-events-none';
    tooltip.textContent = text;
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    tooltip.style.opacity = '0';
    tooltip.style.transform = 'translateY(10px)';
    
    setTimeout(() => {
        tooltip.style.opacity = '1';
        tooltip.style.transform = 'translateY(0)';
    }, 10);
}

function hideTooltip() {
    const tooltip = document.getElementById('tooltip');
    if (tooltip) {
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'translateY(10px)';
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        }, 200);
    }
}

// Theme effects
function initThemeEffects() {
    // Add music note decorations randomly
    const cards = document.querySelectorAll('.bg-white, .bg-gradient-to-br');
    cards.forEach(card => {
        if (Math.random() > 0.8) {
            card.classList.add('music-note');
        } else if (Math.random() > 0.9) {
            card.classList.add('music-notes');
        }
    });

    // Add subtle parallax effect to background elements
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.floating, .floating-delayed, .floating-slow');
        
        parallaxElements.forEach((element, index) => {
            const speed = 0.5 + (index * 0.1);
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

// Export functions for global use
window.BandCafe = {
    showNotification,
    closeNotification,
    addRippleEffect
};
