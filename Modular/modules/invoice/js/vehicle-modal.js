// Open the Modal when the "View Details" button is clicked
document.querySelectorAll('.open-vehicle-modal').forEach(button => {
    button.addEventListener('click', function() {
        openModal();
    });
});

// Open Modal
function openModal() {
    document.getElementById('vehicleDetailsModal').classList.remove('hidden');
}

// Close Modal
function closeModal() {
    document.getElementById('vehicleDetailsModal').classList.add('hidden');
}

function showSection(sectionId) {
    const sections = document.querySelectorAll('.vehicle-section');
    const links = document.querySelectorAll('.section-link');

    // Hide all sections and remove active class from links
    sections.forEach(section => section.classList.add('hidden'));
    links.forEach(link => link.classList.remove('active'));

    // Show the selected section and add active class to the clicked link
    document.getElementById(sectionId).classList.remove('hidden');
    document.querySelector(`.section-link-${sectionId}`).classList.add('active');
}

// Set the default section to be shown when the page loads
window.onload = () => {
    showSection('vehicle-specifications');
};

// Switch car images when thumbnails are clicked
function changeImage(imageSrc) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = imageSrc; // Change main image to the clicked thumbnail
}