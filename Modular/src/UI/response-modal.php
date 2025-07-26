<!-- Response Modal - Now handled by sidebar.js globally -->
<!-- This file is kept for backward compatibility but modal functionality is in sidebar.js -->

<script>
// Ensure modal functions are available (handled by sidebar.js)
document.addEventListener('DOMContentLoaded', function() {
    // Check if sidebar.js loaded the modal functions
    if (typeof window.showResponseModal === 'function') {
        console.log('✅ Response modal system loaded successfully from sidebar.js');
    } else {
        console.warn('⚠️ Response modal system not loaded - ensure sidebar.js is included');
        
        // Simple fallback
        window.showResponseModal = function(message, type = 'info') {
            alert(message);
            return Promise.resolve(true);
        };
        
        window.closeResponseModal = function() {
            // No-op fallback
        };
        
        window.handleApiResponse = function(data) {
            if (data && data.success === false) {
                const msg = data.message || data.error || 'An error occurred';
                alert(msg);
                throw new Error(msg);
            }
            return data;
        };
    }
});
</script>
