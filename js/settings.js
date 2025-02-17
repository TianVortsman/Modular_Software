// Select all sidebar links and setting sections
const sidebarLinks = document.querySelectorAll('.modular-nav-items a');
const settingSections = document.querySelectorAll('.setting-section');

// Function to handle section activation for any link
function activateSection(sectionId) {
    console.log('Section to activate:', sectionId);

    // Hide all sections
    settingSections.forEach(section => {
        section.classList.remove('active');
        console.log('Removed active class from section:', section.id);
    });

    // Remove the active class from all links
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        console.log('Removed active class from link:', link.href);
    });

    // Show the selected section by adding the active class
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.classList.add('active');
        console.log('Activated section:', sectionId);
    } else {
        console.log('No section found with id:', sectionId);
    }

    // Add the active class to the clicked menu item
    const clickedLink = document.querySelector(`a[href="#${sectionId}"]`);
    if (clickedLink) {
        clickedLink.classList.add('active');
        console.log('Added active class to clicked link:', clickedLink.href);
    }
}

// Optionally, show the first section by default when the page loads
if (settingSections.length > 0) {
    settingSections[0].classList.add('active');
    sidebarLinks[0].classList.add('active');
    console.log('Activated default section and link:', settingSections[0].id, sidebarLinks[0].href);
}


// Function to update the summary (you can modify this function as needed)
function updateSummary(sectionId) {
    const summaryElement = document.getElementById('summary'); // Assuming there's an element with id 'summary'
    if (summaryElement) {
        switch (sectionId) {
            case 'invoice-preferences':
                summaryElement.textContent = 'You are viewing Invoice Preferences.';
                break;
            case 'company-settings':
                summaryElement.textContent = 'You are viewing Company Settings.';
                break;
            // Add cases for other sections as needed
            default:
                summaryElement.textContent = 'Select a section to see details.';
        }
    }
}