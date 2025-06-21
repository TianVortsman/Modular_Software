<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Optionally, redirect to remove the query parameter from the URL
    header("Location: ../views/dashboard.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: ../admin/techlogin.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');

$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Import Center</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/root.css">
    <link rel="stylesheet" href="../assets/css/imports.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/imports.js"></script>
    <script src="../assets/js/toggle-theme.js"></script>
</head>
<body id="importing">
    <?php include('../../src/UI/sidebar.php'); ?>
    <?php include('../../src/UI/loading-modal.php'); ?>
    <?php include('../../src/UI/response-modal.php'); ?>
    <?php include('../../src/UI/error-table-modal.php'); ?>
    
    <div class="import-container">
        <div class="import-header">
            <h1>Data Import Center</h1>
            <p>Import and manage your data efficiently across all modules</p>
        </div>

        <!-- Time & Attendance Tab -->
        <div class="tab-content active" id="timeandatt">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">person_add</span>Employees</h3>
                    <div class="upload-zone" data-module="timeandatt" data-type="Employees">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <small>Required sheet name: "Employees"</small>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">schedule</span>Time Entries</h3>
                    <div class="upload-zone" data-module="timeandatt" data-type="Time Entries">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <small>Required sheet name: "TimeEntries"</small>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">work_history</span>Shifts</h3>
                    <div class="upload-zone" data-module="timeandatt" data-type="Shifts">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <small>Required sheet name: "Shifts"</small>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounting Module Tab -->
        <div class="tab-content" id="accounting">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">receipt_long</span>Transactions</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">account_balance</span>Chart of Accounts</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Management Tab -->
        <div class="tab-content" id="payroll">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">payments</span>Salary Data</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">account_balance</span>Tax Information</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Control Tab -->
        <div class="tab-content" id="access">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">security</span>Access Permissions</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">group</span>User Groups</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Management Tab -->
        <div class="tab-content" id="asset">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">inventory</span>Assets</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">build</span>Maintenance Records</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fleet Management Tab -->
        <div class="tab-content" id="fleet">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">directions_car</span>Vehicles</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">local_gas_station</span>Fuel Records</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support/Help Desk Tab -->
        <div class="tab-content" id="support">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">support_agent</span>Tickets</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">category</span>Categories</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Relationship Tab -->
        <div class="tab-content" id="crm">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">people</span>Customers</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">business</span>Companies</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Management Tab -->
        <div class="tab-content" id="inventory">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">inventory_2</span>Products</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">local_shipping</span>Suppliers</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Management Tab -->
        <div class="tab-content" id="project">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">assignment</span>Projects</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">task</span>Tasks</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Human Resources Tab -->
        <div class="tab-content" id="hr">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">person</span>Employees</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">school</span>Training Records</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Management Tab -->
        <div class="tab-content" id="invoice">
            <div class="import-grid">
                <div class="import-card">
                    <h3><span class="material-icons">receipt</span>Invoices</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
                <div class="import-card">
                    <h3><span class="material-icons">inventory_2</span>Products</h3>
                    <div class="upload-zone">
                        <span class="material-icons">cloud_upload</span>
                        <p>Drag & drop your Excel file here</p>
                        <button class="btn btn-primary">Choose File</button>
                    </div>
                    <div class="import-actions">
                        <button class="btn btn-primary">Import</button>
                        <button class="btn btn-secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced drag and drop functionality with error handling
        document.querySelectorAll('.upload-zone').forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length) {
                    handleFiles(files, zone);
                }
            });

            // File input button click
            const button = zone.querySelector('.btn-primary');
            button.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.xlsx, .xls';
                input.click();

                input.addEventListener('change', (e) => {
                    if (e.target.files.length) {
                        handleFiles(e.target.files, zone);
                    }
                });
            });

            // Import button click
            const importBtn = zone.parentElement.querySelector('.import-actions .btn-primary');
            importBtn.addEventListener('click', () => {
                const fileText = zone.querySelector('p').textContent;
                if (!fileText.startsWith('Selected:')) {
                    showResponseModal('warning', 'Please select a file first');
                    return;
                }
                
                // Get the module and import type from the card
                const card = zone.closest('.import-card');
                const importType = card.querySelector('h3').textContent;
                const moduleTab = card.closest('.tab-content').id;
                
                handleImport(moduleTab, importType, zone);
            });

            // Clear button click
            const clearBtn = zone.parentElement.querySelector('.import-actions .btn-secondary');
            clearBtn.addEventListener('click', () => {
                zone.querySelector('p').textContent = 'Drag & drop your Excel file here';
                showResponseModal('success', 'File selection cleared');
            });
        });

        // Function to activate a section (tab)
        function activateSection(sectionId) {
            // Remove active class from all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Add active class to the selected tab content
            const selectedContent = document.getElementById(sectionId);
            if (selectedContent) {
                selectedContent.classList.add('active');
            }
        }

        async function handleImport(moduleTab, importType, zone) {
            try {
                const fileInput = zone.querySelector('input[type="file"]');
                if (!fileInput || !fileInput.files[0]) {
                    throw new Error('Please select a file first');
                }

                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('module', moduleTab);
                formData.append('type', zone.dataset.type);
                formData.append('account_number', '<?php echo $account_number; ?>');

                showLoadingModal('Processing import...');

                const response = await fetch('/src/Core/Imports/process-import.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                hideLoadingModal();

                if (!response.ok) {
                    throw new Error(result.message || 'Import failed');
                }

                // Show success message
                showResponseModal(result.success ? 'success' : 'warning', result.message);

                // If there are any errors, show them in the error table
                if (result.errors && result.errors.length > 0) {
                    showErrorTable({
                        totalRows: result.totalRows,
                        successCount: result.successCount,
                        errors: result.errors.map(error => {
                            // Handle both string errors and error objects
                            if (typeof error === 'string') {
                                // Parse error message if it's in the format "Row X: Message"
                                const rowMatch = error.match(/Row (\d+):(.*)/);
                                if (rowMatch) {
                                    return {
                                        row: rowMatch[1],
                                        message: rowMatch[2].trim(),
                                        data: 'N/A'
                                    };
                                }
                                return {
                                    row: 'N/A',
                                    message: error,
                                    data: 'N/A'
                                };
                            } else {
                                // Handle error object format
                                return {
                                    row: error.row || 'N/A',
                                    message: error.message || 'Unknown error',
                                    data: error.data || 'N/A'
                                };
                            }
                        })
                    });
                }
                
                // Clear the file selection after import attempt
                if (result.success) {
                    zone.querySelector('p').textContent = 'Drag & drop your Excel file here';
                    if (fileInput) {
                        fileInput.value = '';
                    }
                }

            } catch (error) {
                console.error('Import error:', error);
                hideLoadingModal();
                showResponseModal('error', error.message || 'An error occurred during import');
            }
        }

        // Enhanced file handling
        function handleFiles(files, zone) {
            const file = files[0];
            const allowedTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ];

            if (!allowedTypes.includes(file.type)) {
                showResponseModal('error', 'Please upload an Excel file (.xlsx or .xls)');
                return;
            }

            // Create file input if it doesn't exist
            let fileInput = zone.querySelector('input[type="file"]');
            if (!fileInput) {
                fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.xlsx, .xls';
                fileInput.style.display = 'none';
                zone.appendChild(fileInput);
            }

            // Create a new FileList-like object
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            // Update the upload zone to show the file name
            const p = zone.querySelector('p');
            p.textContent = `Selected: ${file.name}`;
            showResponseModal('success', 'File selected successfully');
        }
    </script>
</body>
</html>
