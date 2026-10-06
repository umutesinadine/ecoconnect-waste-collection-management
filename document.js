document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    
    if (mobileMenuToggle) {
        const nav = document.querySelector('nav');
        
        mobileMenuToggle.addEventListener('click', function() {
            nav.classList.toggle('open');
            mobileMenuToggle.classList.toggle('open');
        });
    }
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
})

document.addEventListener('DOMContentLoaded', function() {
    const pickupForm = document.getElementById('pickupForm');
    
    if (pickupForm) {
        
        initMap();
        
        // Handle custom amount field visibility
        const wasteAmountSelect = document.getElementById('wasteAmount');
        const customAmountContainer = document.getElementById('customAmountContainer');
        
        if (wasteAmountSelect && customAmountContainer) {
            wasteAmountSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customAmountContainer.style.display = 'block';
                } else {
                    customAmountContainer.style.display = 'none';
                }
            });
        }
        
        // Form validation
        pickupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple validation example - expand as needed
            let isValid = true;
            const requiredFields = pickupForm.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (isValid) {