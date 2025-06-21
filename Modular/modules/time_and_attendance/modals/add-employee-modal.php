<!-- Add Employee Modal -->
<div id="add-employee-modal" class="add-emp-modal">
    <div class="add-emp-modal-content">
        <div class="add-emp-modal-header">
            <h2><i class="material-icons">person_add</i> Add New Employee</h2>
            <span class="add-emp-modal-close material-icons">close</span>
        </div>
        <div class="add-emp-modal-body">
            <form id="addEmployeeForm" class="add-emp-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>
                    <div class="form-row">
                        <div class="add-emp-form-group">
                            <label for="employeeNumber">Employee Number <span class="add-emp-required">*</span></label>
                            <div class="input-with-icon">
                                <i class="material-icons input-icon">badge</i>
                                <input type="text" id="employeeNumber" name="employeeNumber" required>
                            </div>
                            <span class="add-emp-error" id="employeeNumber-error"></span>
                        </div>

                        <div class="add-emp-form-group">
                            <label for="clockNumber">Clock Number <span class="add-emp-required">*</span></label>
                            <div class="input-with-icon">
                                <i class="material-icons input-icon">timer</i>
                                <input type="text" id="clockNumber" name="clockNumber" required>
                            </div>
                            <span class="add-emp-error" id="clockNumber-error"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="add-emp-form-group">
                            <label for="firstName">First Name <span class="add-emp-required">*</span></label>
                            <div class="input-with-icon">
                                <i class="material-icons input-icon">person</i>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <span class="add-emp-error" id="firstName-error"></span>
                        </div>

                        <div class="add-emp-form-group">
                            <label for="lastName">Last Name <span class="add-emp-required">*</span></label>
                            <div class="input-with-icon">
                                <i class="material-icons input-icon">person</i>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                            <span class="add-emp-error" id="lastName-error"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="isSales">Sales Employee</label>
                        <input type="checkbox" id="isSales" name="isSales">
                    </div>

                    <div class="form-notice">
                        <i class="material-icons notice-icon">info</i>
                        <p>Enter the essential employee information. You can add more details later in the employee profile.</p>
                    </div>
                </div>

                <div class="add-emp-modal-footer">
                    <button type="button" class="add-emp-btn add-emp-btn-secondary cancel-btn">
                        <i class="material-icons">close</i> Cancel
                    </button>
                    <button type="submit" class="add-emp-btn add-emp-btn-primary">
                        <i class="material-icons">save</i> Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Add Employee Modal - Modernized Styling */
.add-emp-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
    transition: opacity 0.3s ease;
}

.add-emp-modal-content {
    background: linear-gradient(145deg, var(--color-background) 0%, var(--color-background-light) 100%);
    margin: 0;
    padding: 0;
    border-radius: 16px;
    width: 90%;
    max-width: 650px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(var(--border-rgb), 0.08);
    transform: translateY(20px);
    transition: transform 0.3s ease;
    overflow: hidden;
    position: relative;
}

.add-emp-modal.show .add-emp-modal-content {
    transform: translateY(0);
}

.add-emp-modal-header {
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark, #3a5ce7) 100%);
    border-radius: 16px 16px 0 0;
    position: relative;
    overflow: hidden;
}

.add-emp-modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.25) 0%, transparent 80%);
}

.add-emp-modal-header h2 {
    margin: 0;
    color: white;
    font-size: 1.4rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.add-emp-modal-header h2 i {
    font-size: 1.5rem;
}

.add-emp-modal-close {
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.add-emp-modal-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: rotate(90deg);
}

.add-emp-modal-body {
    padding: 1.5rem;
    max-height: calc(80vh - 130px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--color-primary) var(--color-background-light);
}

.add-emp-modal-body::-webkit-scrollbar {
    width: 8px;
}

.add-emp-modal-body::-webkit-scrollbar-track {
    background: var(--color-background-light);
    border-radius: 4px;
}

.add-emp-modal-body::-webkit-scrollbar-thumb {
    background-color: var(--color-primary);
    border-radius: 4px;
}

.add-emp-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-section {
    background: var(--color-secondary, #333);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid var(--border-color, #4A4A4D);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--color-primary);
    margin: 0 0 1.25rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(var(--border-rgb), 0.1);
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--color-primary);
    border-radius: 3px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.add-emp-form-group {
    position: relative;
    margin-bottom: 1rem;
}

.add-emp-form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--color-text-light, #ffffff);
    font-weight: 500;
    font-size: 0.95rem;
}

.input-with-icon {
    position: relative;
    display: block;
    width: 100%;
    margin-bottom: 5px;
}

.input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-secondary, #757575);
    font-size: 1.2rem;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 2;
}

.add-emp-form-group input {
    width: 80%;
    padding: 0.75rem 0.75rem 0.75rem 2.5rem;
    border: 1px solid var(--border-color, #4A4A4D);
    border-radius: 8px;
    background-color: var(--input-bg, #2C2C2E);
    color: var(--color-text-light, #ffffff);
    font-size: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.add-emp-form-group input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 0, 123, 255), 0.15);
}

.add-emp-form-group input:focus + .input-icon {
    color: var(--color-primary);
}

/* Contrast fixes for dark themes */
[data-theme="dark-mode"] .add-emp-form-group input,
[data-theme="dark-mode-blue"] .add-emp-form-group input,
[data-theme="dark-mode-brown"] .add-emp-form-group input,
[data-theme="dark-mode-red"] .add-emp-form-group input {
    background-color: var(--input-bg, rgba(44, 44, 46, 0.8));
    border-color: var(--border-color, rgba(74, 74, 77, 0.8));
}

/* Contrast fixes for light themes */
[data-theme="light-mode"] .add-emp-form-group input,
[data-theme="light-mode-brown"] .add-emp-form-group input,
[data-theme="light-mode-green"] .add-emp-form-group input {
    background-color: #ffffff;
    border-color: var(--border-color, rgba(0, 0, 0, 0.2));
    color: var(--color-text-dark, #000000);
}

.add-emp-error {
    display: none;
    color: var(--color-error, #F44336);
    font-size: 0.875rem;
    margin-top: 0.5rem;
    padding-left: 2.5rem;
}

.add-emp-form-group.has-error input {
    border-color: var(--color-error, #F44336);
    box-shadow: 0 0 0 2px rgba(244, 67, 54, 0.15);
}

.add-emp-form-group.has-error .add-emp-error {
    display: block;
}

.add-emp-form-group.has-error .input-icon {
    color: #ff4757;
}

.add-emp-required {
    color: #ff4757;
    margin-left: 0.25rem;
}

.form-notice {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background-color: rgba(var(--primary-rgb), 0.08);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 0.5rem;
}

.notice-icon {
    color: var(--color-primary);
    font-size: 1.25rem;
    margin-top: 0.125rem;
}

.form-notice p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--color-text-light);
    line-height: 1.5;
}

.add-emp-modal-footer {
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(to bottom, rgba(var(--background-rgb), 0.3), rgba(var(--background-rgb), 0.5));
    border-top: 1px solid rgba(var(--border-rgb), 0.1);
    position: relative;
}

.add-emp-btn {
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.add-emp-btn i {
    font-size: 1.1rem;
}

.add-emp-btn-primary {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary, #3a5ce7) 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.3);
}

.add-emp-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(var(--primary-rgb), 0.35);
}

.add-emp-btn-primary:active {
    transform: translateY(1px);
    box-shadow: 0 2px 5px rgba(var(--primary-rgb), 0.4);
}

.add-emp-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: translateY(0);
}

.add-emp-btn-secondary {
    background: rgba(var(--background-rgb), 0.3);
    color: var(--color-text-light);
    border: 1px solid rgba(var(--border-rgb), 0.15);
}

.add-emp-btn-secondary:hover {
    background: rgba(var(--background-rgb), 0.5);
    transform: translateY(-2px);
}

.add-emp-btn-secondary:active {
    transform: translateY(1px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .add-emp-modal-content {
        width: 95%;
        max-height: 90vh;
    }
    
    .add-emp-modal-body {
        max-height: calc(90vh - 140px);
    }
    
    .add-emp-modal-footer {
        padding: 1rem;
        flex-direction: column-reverse;
        gap: 0.75rem;
    }
    
    .add-emp-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Update the script section in add-employee-modal.php
document.addEventListener('DOMContentLoaded', function() {
    const addEmployeeModal = document.getElementById('add-employee-modal');
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const closeBtn = addEmployeeModal.querySelector('.add-emp-modal-close');
    const cancelBtn = addEmployeeModal.querySelector('.cancel-btn');

    // Only handle modal closing since opening is now handled by the sidebar button
    function hideModal() {
        addEmployeeModal.classList.remove('show');
        // Wait for the animation to complete before hiding the modal
        setTimeout(() => {
            addEmployeeModal.style.display = 'none';
            addEmployeeForm.reset();
            clearErrors();
        }, 300);
    }

    function clearErrors() {
        document.querySelectorAll('.add-emp-error').forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        document.querySelectorAll('.add-emp-form-group').forEach(group => {
            group.classList.remove('has-error');
        });
    }

    function showError(fieldId, message) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const formGroup = document.getElementById(fieldId).closest('.add-emp-form-group');
        
        if (errorElement && formGroup) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            formGroup.classList.add('has-error');
            
            // Add focus to the field with error
            document.getElementById(fieldId).focus();
        }
    }

    function clearFieldError(field) {
        const formGroup = field.closest('.add-emp-form-group');
        if (formGroup) {
            formGroup.classList.remove('has-error');
            const errorElement = formGroup.querySelector('.add-emp-error');
            if (errorElement) {
                errorElement.style.display = 'none';
                errorElement.textContent = '';
            }
        }
    }

    // Attach close/cancel event handlers
    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
            hideModal();
        }
    });

    // Handle Escape key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && addEmployeeModal.classList.contains('show')) {
            hideModal();
        }
    });

    // Handle form submission
    if (addEmployeeForm) {
        // Add a flag to track submission state
        let isSubmitting = false;
        
        addEmployeeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Prevent double submissions
            if (isSubmitting) {
                console.log('Form already submitting, please wait...');
                return;
            }
            
            // Set submission flag and disable submit button
            isSubmitting = true;
            const submitButton = addEmployeeForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="material-icons">hourglass_top</i> Adding...';
            }
            
            // Clear previous errors and state
            clearErrors();
            
            const formData = new FormData(addEmployeeForm);
            const employeeData = {
                employee_number: formData.get('employeeNumber'),
                clock_number: formData.get('clockNumber'),
                first_name: formData.get('firstName'),
                last_name: formData.get('lastName'),
                csrf_token: formData.get('csrf_token')
            };

            try {
                // Show loading indicator if available
                const loadingModal = document.getElementById('loadingModal');
                if (loadingModal) {
                    loadingModal.classList.remove('hidden');
                }
                
                // Change the endpoint to match your API file
                const response = await fetch('../api/employee-api.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(employeeData)
                });

                const result = await response.json();
                
                // Hide loading indicator
                if (loadingModal) {
                    loadingModal.classList.add('hidden');
                }

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to add employee');
                }

                if (result.success) {
                    // Refresh employee list
                    if (typeof loadEmployees === 'function') {
                        await loadEmployees();
                    }
                    // Hide modal
                    hideModal();
                    // Show success message
                    showNotification('Employee added successfully', 'success');
                } else if (result.message === 'Employee number already exists') {
                    showError('employeeNumber', 'Employee number already exists');
                } else {
                    throw new Error(result.message || 'Failed to add employee');
                }
            } catch (error) {
                console.error('Error adding employee:', error);
                if (error.message === 'Employee number already exists') {
                    showError('employeeNumber', 'Employee number already exists');
                } else {
                    showNotification(error.message, 'error');
                }
            } finally {
                // Reset submission state and enable button
                isSubmitting = false;
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="material-icons">save</i> Add Employee';
                }
                
                // Hide loading indicator if it's still showing
                const loadingModal = document.getElementById('loadingModal');
                if (loadingModal) {
                    loadingModal.classList.add('hidden');
                }
            }
        });

        // Clear errors when any input changes
        addEmployeeForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                clearFieldError(input);
            });
        });
    }
});

// The openAddEmployeeModal function for the sidebar button call
function openAddEmployeeModal() {
    const addEmployeeModal = document.getElementById('add-employee-modal');
    if (!addEmployeeModal) {
        console.error('Add Employee modal not found!');
        return;
    }
    
    // Show modal first with display flex
    addEmployeeModal.style.display = 'flex';
    
    // Force browser reflow before adding the show class for animation
    void addEmployeeModal.offsetWidth;
    
    // Add show class for animation
    addEmployeeModal.classList.add('show');
    
    // Reset form if it exists
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    if (addEmployeeForm) {
        addEmployeeForm.reset();
        
        // Clear any errors
        document.querySelectorAll('.add-emp-error').forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        document.querySelectorAll('.add-emp-form-group').forEach(group => {
            group.classList.remove('has-error');
        });
    }
}
</script> 