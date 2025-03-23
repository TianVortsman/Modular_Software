function openTab(event, tabId) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    // Remove active state from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    // Show the selected tab content
    document.getElementById(tabId).classList.add('active');
    // Add active state to the clicked button
    event.currentTarget.classList.add('active');
}