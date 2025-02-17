document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab");
    const tabContents = document.querySelectorAll(".tab-content");
    const searchInput = document.getElementById("search-input");
    const modal = document.getElementById("modal-upload-pdf");
    const openModalButton = document.getElementById("openPdfModalButton");
    const closeModalButton = document.querySelector(".modal-upload-pdf-close");
    const dropzone = document.getElementById('pdf-dropzone'); // Dropzone element
    const fileInput = document.getElementById("pdf-file-input"); // Hidden file input
    const itemsTableBody = document.querySelector('.modal-upload-pdf-table tbody'); // Table body for displaying items

    // Show the first tab content by default
    tabContents[0].classList.add("active");

    // Search functionality
    searchInput.addEventListener("input", () => {
        const filter = searchInput.value.toLowerCase();
        tabContents.forEach((content) => {
            const cards = content.querySelectorAll(".product-card");
            cards.forEach((card) => {
                const title = card.querySelector(".product-title").textContent.toLowerCase();
                card.style.display = title.includes(filter) ? "" : "none";
            });
        });
    });

    // Modal open and close functionality
    openModalButton.addEventListener("click", () => {
        modal.style.display = "block";
        modal.classList.add('show');
    });

    closeModalButton.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Close the modal when clicking outside of it
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Handle dropzone click event to open file dialog
    dropzone.addEventListener("click", () => {
        fileInput.click(); // Trigger file upload dialog
    });

    // Handle file selection
    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0]; // Get the selected file

        if (file) {
            // Check if the selected file is a PDF
            if (file.type === 'application/pdf') {
                processPDF(file);
            } else {
                alert("Please upload a valid PDF file.");
            }
        }
    });

    // Add drag and drop events
    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault(); // Prevent default to allow drop
        dropzone.classList.add('hover'); // Add hover class for visual feedback
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('hover'); // Remove hover class
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault(); // Prevent default behavior (open file)
        dropzone.classList.remove('hover'); // Remove hover class

        const files = event.dataTransfer.files; // Get dropped files
        if (files.length > 0) {
            const file = files[0]; // Get the first file
            handleFile(file); // Process the dropped file
        }
    });

    // Function to process the uploaded PDF
    function processPDF(file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const pdfData = e.target.result; // Get the file data

            // Here you can use a library like pdf.js to read the PDF and extract the items
            pdfjsLib.getDocument({ data: pdfData }).promise.then(function(pdf) {
                // Loop through pages and extract text
                let numPages = pdf.numPages;
                let items = []; // Array to hold extracted items

                let pagePromises = [];
                for (let i = 1; i <= numPages; i++) {
                    pagePromises.push(
                        pdf.getPage(i).then(function(page) {
                            return page.getTextContent().then(function(textContent) {
                                // Process the text content to extract items
                                let textItems = textContent.items.map(item => item.str).join(" ");
                                // Here you would implement your own logic to parse the items
                                // For demonstration, let's assume we just add the text to the items array
                                items.push(textItems);
                            });
                        })
                    );
                }

                // Wait for all pages to be processed
                Promise.all(pagePromises).then(function() {
                    displayExtractedItems(items);
                });
            });
        };

        reader.readAsArrayBuffer(file); // Read file as array buffer
    }

    // Function to display extracted items in the table
    function displayExtractedItems(items) {
        // Clear existing items in the table
        itemsTableBody.innerHTML = '';

        items.forEach(item => {
            const row = document.createElement('tr');
            const cellItem = document.createElement('td');
            const cellQuantity = document.createElement('td');

            cellItem.textContent = item; // Assuming each item is just text
            cellQuantity.textContent = '1'; // Set a default quantity for demonstration

            row.appendChild(cellItem);
            row.appendChild(cellQuantity);
            itemsTableBody.appendChild(row);
        });

        // Optionally, you can display a success message or update the UI
        alert("Items extracted successfully!");
    }
});