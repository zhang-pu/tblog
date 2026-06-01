/**
 * ZhangPu Blog - Base JavaScript
 */

// Mobile menu toggle
function toggleMobileMenu() {
    var nav = document.querySelector('.main-nav');
    if (nav) {
        nav.classList.toggle('show');
    }
}

// Add event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide messages after 5 seconds
    var messages = document.querySelectorAll('.success, .error');
    messages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                msg.remove();
            }, 500);
        }, 5000);
    });
});
