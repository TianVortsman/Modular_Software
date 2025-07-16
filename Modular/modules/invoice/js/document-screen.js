// Tab switching logic for document sections

document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.document-tab-button');
    const sections = document.querySelectorAll('.document-section');
    // Show only the first section by default
    sections.forEach((sec, idx) => {
        sec.style.display = idx === 0 ? '' : 'none';
    });
    tabButtons.forEach((btn, idx) => {
        btn.classList.toggle('active', idx === 0);
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            sections.forEach(sec => {
                if (sec.id === this.dataset.section) {
                    sec.style.display = '';
                } else {
                    sec.style.display = 'none';
                }
            });
        });
    });
});
