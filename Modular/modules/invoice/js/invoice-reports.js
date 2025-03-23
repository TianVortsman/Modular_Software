document.addEventListener("DOMContentLoaded", () => {
    const sidebarLinks = document.querySelectorAll('.modular-nav-items a');
    const reportSections = document.querySelectorAll('.reports-section');

    function activateSection(sectionId) {
        // Remove 'active' class from all sections
        reportSections.forEach(section => {
            section.classList.remove('active');
        });

        // Remove 'active' class from all sidebar links
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
        });

        // Activate the selected section
        const selectedSection = document.getElementById(sectionId);
        if (selectedSection) {
            selectedSection.classList.add('active');
        }

        // Activate the clicked link
        const clickedLink = document.querySelector(`.modular-nav-items a[href="#${sectionId}"]`);
        if (clickedLink) {
            clickedLink.classList.add('active');
        }
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            const sectionId = link.getAttribute('href').substring(1);
            activateSection(sectionId);
        });
    });

    // Set the first section as active by default
    if (reportSections.length > 0 && sidebarLinks.length > 0) {
        activateSection(reportSections[0].id);
    }
});
