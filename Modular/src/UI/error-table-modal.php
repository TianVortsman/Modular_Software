<?php
// Ensure this modal is hidden by default
?>
<div id="errorTableModal" class="error-table-modal" style="display: none;">
    <div class="error-modal-content">
        <div class="error-modal-header">
            <h2>Import Errors</h2>
            <span class="error-modal-close">&times;</span>
        </div>
        <div class="error-modal-body">
            <div class="error-summary">
                <p><strong>Total Rows Processed: </strong><span id="totalRows">0</span></p>
                <p><strong>Successfully Imported: </strong><span id="successRows">0</span></p>
                <p><strong>Failed Rows: </strong><span id="failedRows">0</span></p>
            </div>
            <div class="error-table-container">
                <table id="errorTable" class="error-table">
                    <thead>
                        <tr>
                            <th>Row #</th>
                            <th>Data</th>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Error rows will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="error-modal-footer">
            <button class="error-modal-btn error-modal-btn-primary" onclick="document.getElementById('errorTableModal').style.display='none'">Close</button>
            <button class="error-modal-btn error-modal-btn-secondary" onclick="downloadErrorReport()">Download Report</button>
        </div>
    </div>
</div>

<style>
/* Error Modal Specific Styles */
.error-table-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    display: none;
}

.error-modal-content {
    background-color: var(--color-background);
    margin: 5% auto;
    padding: 20px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    width: 80%;
    max-width: 1000px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.error-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.error-modal-header h2 {
    margin: 0;
    color: var(--color-text);
    font-size: 1.5rem;
}

.error-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
}

.error-modal-footer {
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.error-modal-close {
    color: var(--color-text);
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    padding: 0 5px;
}

.error-modal-close:hover {
    color: var(--color-primary);
}

.error-summary {
    margin-bottom: 20px;
    padding: 15px;
    background-color: var(--color-background-light);
    border-radius: 4px;
    border: 1px solid var(--border-color);
}

.error-summary p {
    margin: 5px 0;
    color: var(--color-text);
}

.error-table-container {
    overflow-x: auto;
    border-radius: 4px;
    border: 1px solid var(--border-color);
}

.error-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.error-table th,
.error-table td {
    padding: 12px;
    text-align: left;
    border: 1px solid var(--border-color);
    color: var(--color-text);
}

.error-table th {
    background-color: var(--color-primary);
    color: white;
    font-weight: 500;
}

.error-table tr:nth-child(even) {
    background-color: var(--color-background-light);
}

.error-table tr:hover {
    background-color: var(--color-hover);
}

/* Error Modal Button Styles */
.error-modal-btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background-color 0.2s;
}

.error-modal-btn-primary {
    background-color: var(--color-primary);
    color: white;
}

.error-modal-btn-primary:hover {
    background-color: var(--color-primary-dark, #3a5ce7);
}

.error-modal-btn-secondary {
    background-color: transparent;
    border-color: var(--border-color);
    color: var(--color-text);
}

.error-modal-btn-secondary:hover {
    background-color: var(--color-background-light);
}

/* Dark Mode Adjustments */
[data-theme="dark-mode"] .error-modal-content,
[data-theme="dark-mode-blue"] .error-modal-content {
    background-color: var(--color-background);
    border-color: var(--border-color);
}

[data-theme="dark-mode"] .error-table th,
[data-theme="dark-mode-blue"] .error-table th {
    background-color: var(--color-primary);
}

[data-theme="dark-mode"] .error-summary,
[data-theme="dark-mode-blue"] .error-summary {
    background-color: var(--color-background-light);
    border-color: var(--border-color);
}
</style>

<script>
// Update the JavaScript to use new class names
function showErrorTable(data) {
    const modal = document.getElementById('errorTableModal');
    const tbody = document.querySelector('.error-table tbody');
    const totalRows = document.getElementById('totalRows');
    const successRows = document.getElementById('successRows');
    const failedRows = document.getElementById('failedRows');

    // Clear existing rows
    tbody.innerHTML = '';

    // Update summary
    totalRows.textContent = data.totalRows || 0;
    successRows.textContent = data.successCount || 0;
    failedRows.textContent = data.errors?.length || 0;

    // Add error rows
    if (data.errors && data.errors.length > 0) {
        data.errors.forEach(error => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${error.row || 'N/A'}</td>
                <td>${error.data || 'N/A'}</td>
                <td>${error.message || 'Unknown error'}</td>
            `;
            tbody.appendChild(row);
        });
    }

    // Show modal
    modal.style.display = 'block';
}

function downloadErrorReport() {
    const table = document.querySelector('.error-table');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    let csv = 'Row #,Data,Error Message\n';
    
    rows.slice(1).forEach(row => {
        const cells = Array.from(row.cells);
        const csvRow = cells.map(cell => `"${cell.textContent}"`).join(',');
        csv += csvRow + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'import_errors.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Close modal when clicking the X or outside the modal
document.querySelector('.error-modal-close').onclick = function() {
    document.getElementById('errorTableModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('errorTableModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script> 