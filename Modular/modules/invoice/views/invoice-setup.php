<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Redirect to remove the query parameter from the URL
    header("Location: invoice-setup.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: ../../index.php");
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
    <title>Invoice Setup</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-setup.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
</head>
<body id="invoice-setup">
    <?php include ('../../../src/UI/sidebar.php') ?>
    <div class="container">
        <header class="setup-header">
            <h1><span class="material-icons">settings</span> Invoice Setup</h1>
            <p class="setup-description">Configure all invoice-related settings and system behavior</p>
        </header>

        <div class="setup-container">
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Product Setup Tab -->
                <div id="products" class="tab-panel active">
                    <div class="setup-section">
                        <h2>Product Categories</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <div class="card-header">
                                    <h3>Manage Categories</h3>
                                    <button class="btn-primary" onclick="openCategoryModal()">
                                        <span class="material-icons">add</span> Add Category
                                    </button>
                                </div>
                                <div class="card-content">
                                    <div class="filter-controls">
                                        <select id="category-type-filter" class="form-select">
                                            <option value="">All Types</option>
                                        </select>
                                    </div>
                                    <div id="product-categories-list" class="data-list">
                                        <!-- Categories will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="setup-section">
                        <h2>Product Subcategories</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <div class="card-header">
                                    <h3>Manage Subcategories</h3>
                                    <button class="btn-primary" onclick="openSubcategoryModal()">
                                        <span class="material-icons">add</span> Add Subcategory
                                    </button>
                                </div>
                                <div class="card-content">
                                    <div class="filter-controls">
                                        <select id="subcategory-type-filter" class="form-select">
                                            <option value="">All Types</option>
                                        </select>
                                        <select id="subcategory-category-filter" class="form-select">
                                            <option value="">All Categories</option>
                                        </select>
                                    </div>
                                    <div id="product-subcategories-list" class="data-list">
                                        <!-- Subcategories will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank & Company Info Tab -->
                <div id="banking" class="tab-panel">
                    <div class="setup-section">
                        <h2>Bank Information</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <form id="bank-info-form" class="setup-form">
                                    <div class="form-group">
                                        <label for="bank-name">Bank Name</label>
                                        <input type="text" id="bank-name" name="bank_name" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="bank-branch">Branch</label>
                                        <input type="text" id="bank-branch" name="bank_branch" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="account-number">Account Number</label>
                                        <input type="text" id="account-number" name="account_number" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="swift-code">SWIFT Code</label>
                                        <input type="text" id="swift-code" name="swift_code" class="form-input">
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <span class="material-icons">save</span> Save Bank Info
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="setup-section">
                        <h2>Company Information</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <form id="company-info-form" class="setup-form">
                                    <div class="form-group">
                                        <label for="company-name">Company Name</label>
                                        <input type="text" id="company-name" name="company_name" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="company-address">Address</label>
                                        <textarea id="company-address" name="company_address" class="form-textarea" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="company-phone">Phone</label>
                                        <input type="tel" id="company-phone" name="company_phone" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label for="company-email">Email</label>
                                        <input type="email" id="company-email" name="company_email" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label for="vat-number">VAT Number</label>
                                        <input type="text" id="vat-number" name="vat_number" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label for="registration-number">Registration Number</label>
                                        <input type="text" id="registration-number" name="registration_number" class="form-input">
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <span class="material-icons">save</span> Save Company Info
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Configuration Tab -->
                <div id="sales" class="tab-panel">
                    <div class="setup-section">
                        <h2>Sales Targets</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <div class="card-header">
                                    <h3>Target Configuration</h3>
                                    <button class="btn-primary" onclick="openSalesTargetModal()">
                                        <span class="material-icons">add</span> Add Target
                                    </button>
                                </div>
                                <div class="card-content">
                                    <div class="target-settings">
                                        <div class="form-group">
                                            <label class="form-label">Target Period</label>
                                            <select id="target-period" class="form-select">
                                                <option value="monthly">Monthly</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="yearly">Yearly</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">
                                                <input type="checkbox" id="track-actual-vs-target" class="form-checkbox">
                                                Track actual invoice totals vs targets
                                            </label>
                                        </div>
                                    </div>
                                    <div id="sales-targets-list" class="data-list">
                                        <!-- Sales targets will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suppliers Tab -->
                <div id="suppliers" class="tab-panel">
                    <div class="setup-section">
                        <h2>Supplier Management</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <div class="card-header">
                                    <h3>Manage Suppliers</h3>
                                    <button class="btn-primary" onclick="openSupplierModal()">
                                        <span class="material-icons">add</span> Add Supplier
                                    </button>
                                </div>
                                <div class="card-content">
                                    <div id="suppliers-list" class="data-list">
                                        <!-- Suppliers will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Notes Tab -->
                <div id="credit" class="tab-panel">
                    <section class="credit-policy-section">
                        <h2>Credit Notes & Refund Policy</h2>
                        <form id="credit-policy-form" class="credit-policy-form">
                            <div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" id="allow-credit-notes" name="allow_credit_notes" class="form-checkbox">
                                    Allow credit notes
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" id="require-approval" name="require_approval" class="form-checkbox">
                                    Require approval before refunds
                                </label>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <span class="material-icons">save</span> Save Policy
                                </button>
                            </div>
                        </form>
                    </section>
                    <!-- Credit Reasons Section -->
                    <section id="credit-reasons-section">
                        <h3>Credit Reasons</h3>
                        <p>Manage reasons for issuing credit notes (e.g., returns, overpayment, goodwill).</p>
                        <div id="creditReasonsList" class="data-list">
                            <!-- Credit reasons will be loaded here -->
                        </div>
                        <button id="addCreditReasonBtn" class="btn btn-primary">Add Credit Reason</button>
                    </section>
                </div>

                <!-- Invoice Numbering Tab -->
                <div id="numbering" class="tab-panel">
                    <div class="setup-section">
                        <h2>Invoice Preferences</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <form id="numbering-form" class="setup-form">
                                    <div class="form-group">
                                        <label for="invoice-prefix">Invoice Number Prefix</label>
                                        <input type="text" id="invoice-prefix" name="invoice_prefix" class="form-input" placeholder="e.g., INV-2025-">
                                    </div>
                                    <div class="form-group">
                                        <label for="starting-number">Starting Number</label>
                                        <input type="number" id="starting-number" name="starting_number" class="form-input" min="1" placeholder="1">
                                    </div>
                                    <div class="form-group">
                                        <label for="current-number">Current Number</label>
                                        <input type="number" id="current-number" name="current_number" class="form-input" min="1" placeholder="(auto)">
                                    </div>
                                    <div class="form-group">
                                        <label for="date-format">Date Format</label>
                                        <select id="date-format" name="date_format" class="form-select">
                                            <option value="Y-m-d">YYYY-MM-DD</option>
                                            <option value="d/m/Y">DD/MM/YYYY</option>
                                            <option value="m/d/Y">MM/DD/YYYY</option>
                                            <option value="d-m-Y">DD-MM-YYYY</option>
                                        </select>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <span class="material-icons">save</span> Save Numbering Settings
                                        </button>
                                    </div>
                                </form>
                                <!-- Invoice Template Preferences -->
                                <form id="template-form" class="setup-form" style="margin-top:32px;">
                                    <div class="form-group">
                                        <label class="form-label">Choose your invoice template:</label>
                                        <div class="template-options" style="display: flex; gap: 32px; flex-wrap: wrap;">
                                            <label style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                                <input type="radio" name="template" value="modern-blue">
                                                <span style="font-size: 0.95em; margin-bottom: 4px;">Modern Blue</span>
                                                <div style="width: 120px; height: 80px; border-radius: 8px; border: 2px solid #1a73e8; background: linear-gradient(135deg, #e3eafc 60%, #1a73e8 100%); display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding: 8px; box-shadow: 0 2px 8px #e3eafc;">
                                                    <div style="width: 60px; height: 12px; background: #1a73e8; border-radius: 4px;"></div>
                                                    <div style="width: 40px; height: 8px; background: #e3eafc; border-radius: 4px; margin-top: 4px;"></div>
                                                </div>
                                                <button type="button" class="btn-secondary" style="margin-top:8px;" onclick="previewInvoiceTemplate('modern-blue')">Preview</button>
                                            </label>
                                            <label style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                                <input type="radio" name="template" value="classic-grey">
                                                <span style="font-size: 0.95em; margin-bottom: 4px;">Classic Grey</span>
                                                <div style="width: 120px; height: 80px; border-radius: 8px; border: 2px solid #bbb; background: linear-gradient(135deg, #f4f4f4 60%, #bbb 100%); display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding: 8px; box-shadow: 0 2px 8px #ccc;">
                                                    <div style="width: 60px; height: 12px; background: #bbb; border-radius: 4px;"></div>
                                                    <div style="width: 40px; height: 8px; background: #f4f4f4; border-radius: 4px; margin-top: 4px;"></div>
                                                </div>
                                                <button type="button" class="btn-secondary" style="margin-top:8px;" onclick="previewInvoiceTemplate('classic-grey')">Preview</button>
                                            </label>
                                            <label style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                                <input type="radio" name="template" value="elegant-dark">
                                                <span style="font-size: 0.95em; margin-bottom: 4px;">Elegant Dark</span>
                                                <div style="width: 120px; height: 80px; border-radius: 8px; border: 2px solid #f7c873; background: linear-gradient(135deg, #23272b 60%, #f7c873 100%); display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding: 8px; box-shadow: 0 2px 8px #23272b;">
                                                    <div style="width: 60px; height: 12px; background: #f7c873; border-radius: 4px;"></div>
                                                    <div style="width: 40px; height: 8px; background: #23272b; border-radius: 4px; margin-top: 4px;"></div>
                                                </div>
                                                <button type="button" class="btn-secondary" style="margin-top:8px;" onclick="previewInvoiceTemplate('elegant-dark')">Preview</button>
                                            </label>
                                            <label style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                                <input type="radio" name="template" value="modern-clean">
                                                <span style="font-size: 0.95em; margin-bottom: 4px;">Modern Clean</span>
                                                <div style="width: 120px; height: 80px; border-radius: 8px; border: 2px solid #333; background: linear-gradient(135deg, #f8f9fa 60%, #fff 100%); display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding: 8px; box-shadow: 0 2px 8px #eee;">
                                                    <div style="width: 60px; height: 12px; background: #333; border-radius: 4px;"></div>
                                                    <div style="width: 40px; height: 8px; background: #f1f3f5; border-radius: 4px; margin-top: 4px;"></div>
                                                </div>
                                                <button type="button" class="btn-secondary" style="margin-top:8px;" onclick="previewInvoiceTemplate('modern-clean')">Preview</button>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <span class="material-icons">save</span> Save Preferences
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Footer Tab -->
                <div id="terms" class="tab-panel">
                    <div class="setup-section">
                        <h2>Payment Terms & Footer</h2>
                        <div class="setup-grid">
                            <div class="setup-card">
                                <form id="terms-form" class="setup-form">
                                    <div class="form-group">
                                        <label for="default-payment-terms">Default Payment Terms</label>
                                        <input type="text" id="default-payment-terms" name="default_payment_terms" class="form-input" placeholder="e.g., Payable within 30 days">
                                    </div>
                                    <div class="form-group">
                                        <label for="default-due-days">Default Due Days</label>
                                        <input type="number" id="default-due-days" name="default_due_days" class="form-input" min="0" max="365" placeholder="30">
                                    </div>
                                    <div class="form-group">
                                        <label for="invoice-footer">Invoice Footer Text</label>
                                        <textarea id="invoice-footer" name="invoice_footer" class="form-textarea" rows="4" placeholder="Custom footer text for invoice PDFs"></textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <span class="material-icons">save</span> Save Terms & Footer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include modals -->
    <?php include '../modals/category-modal.php'; ?>
    <?php include '../modals/subcategory-modal.php'; ?>
    <?php include '../modals/supplier-modal.php'; ?>
    <?php include '../modals/sales-target-modal.php'; ?>
    <?php include '../modals/credit-reason-modal.php'; ?>

    <!-- Include response and loading modals -->
    <?php include '../../../src/UI/response-modal.php'; ?>
    <?php include '../../../src/UI/loading-modal.php'; ?>

    <script type="module" src="../js/setup-screen.js"></script>
</body>
</html>