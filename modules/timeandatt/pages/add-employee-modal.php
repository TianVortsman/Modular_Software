<!-- Add Employee Modal -->
<div id="add-employee-modal" class="add-emp-modal">
    <div class="add-emp-modal-content">
        <div class="add-emp-modal-header">
            <h2>Add New Employee</h2>
            <span class="add-emp-modal-close">&times;</span>
        </div>
        <div class="add-emp-modal-body">
            <form id="addEmployeeForm" class="add-emp-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="add-emp-form-group">
                    <label for="employeeNumber">Employee Number <span class="add-emp-required">*</span></label>
                    <input type="text" id="employeeNumber" name="employeeNumber" required>
                    <span class="add-emp-error" id="employeeNumber-error"></span>
                </div>

                <div class="add-emp-form-group">
                    <label for="clockNumber">Clock Number <span class="add-emp-required">*</span></label>
                    <input type="text" id="clockNumber" name="clockNumber" required>
                    <span class="add-emp-error" id="clockNumber-error"></span>
                </div>

                <div class="add-emp-form-group">
                    <label for="firstName">First Name <span class="add-emp-required">*</span></label>
                    <input type="text" id="firstName" name="firstName" required>
                    <span class="add-emp-error" id="firstName-error"></span>
                </div>

                <div class="add-emp-form-group">
                    <label for="lastName">Last Name <span class="add-emp-required">*</span></label>
                    <input type="text" id="lastName" name="lastName" required>
                    <span class="add-emp-error" id="lastName-error"></span>
                </div>

                <div class="add-emp-modal-footer">
                    <button type="button" class="add-emp-btn add-emp-btn-secondary cancel-btn">Cancel</button>
                    <button type="submit" class="add-emp-btn add-emp-btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Add Employee Modal Specific Styles */
.add-emp-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.add-emp-modal-content {
    background-color: var(--color-background);
    margin: 0;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border-color);
}

.add-emp-modal-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--color-primary);
    border-radius: 8px 8px 0 0;
}

.add-emp-modal-header h2 {
    margin: 0;
    color: white;
    font-size: 1.25rem;
}

.add-emp-modal-close {
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.add-emp-modal-close:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.add-emp-modal-body {
    padding: 1.5rem;
}

.add-emp-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.add-emp-form-group {
    margin-bottom: 1rem;
    position: relative;
}

.add-emp-form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--color-text);
    font-weight: 500;
}

.add-emp-form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background-color: var(--color-background);
    color: var(--color-text);
    font-size: 1rem;
    transition: border-color 0.2s;
}

.add-emp-form-group input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(var(--color-primary-rgb), 0.1);
}

.add-emp-error {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    position: absolute;
    bottom: -1.25rem;
    left: 0;
}

.add-emp-form-group.has-error input {
    border-color: #dc3545;
}

.add-emp-form-group.has-error .add-emp-error {
    display: block;
}

.add-emp-required {
    color: #dc3545;
    margin-left: 0.25rem;
}

.add-emp-modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin: 1rem -1.5rem -1.5rem;
    background-color: var(--color-background-light);
    border-radius: 0 0 8px 8px;
}

.add-emp-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.add-emp-btn-primary {
    background-color: var(--color-primary);
    color: white;
}

.add-emp-btn-primary:hover {
    background-color: var(--color-primary-dark, #3a5ce7);
}

.add-emp-btn-secondary {
    background-color: transparent;
    color: var(--color-text);
    border: 1px solid var(--border-color);
}

.add-emp-btn-secondary:hover {
    background-color: var(--color-background-light);
}

/* Dark Mode Adjustments */
[data-theme="dark-mode"] .add-emp-modal-content,
[data-theme="dark-mode-blue"] .add-emp-modal-content {
    background-color: var(--color-background);
    border-color: var(--border-color);
}

[data-theme="dark-mode"] .add-emp-form-group input,
[data-theme="dark-mode-blue"] .add-emp-form-group input {
    background-color: var(--color-background-light);
    border-color: var(--border-color);
    color: var(--color-text);
}

[data-theme="dark-mode"] .add-emp-modal-footer,
[data-theme="dark-mode-blue"] .add-emp-modal-footer {
    background-color: var(--color-background-light);
    border-color: var(--border-color);
}

/* Responsive Design */
@media (max-width: 640px) {
    .add-emp-modal-content {
        width: 95%;
        margin: 1rem;
    }

    .add-emp-modal-body {
        padding: 1rem;
    }

    .add-emp-modal-footer {
        padding: 1rem;
        margin: 1rem -1rem -1rem;
        flex-direction: column-reverse;
        gap: 0.5rem;
    }

    .add-emp-btn {
        width: 100%;
        padding: 0.875rem;
    }
}
</style>

<script>
// Update event listeners to use new class names
document.addEventListener('DOMContentLoaded', function() {
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const addEmployeeModal = document.getElementById('add-employee-modal');
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const closeBtn = addEmployeeModal.querySelector('.add-emp-modal-close');
    const cancelBtn = addEmployeeModal.querySelector('.cancel-btn');

    function showModal() {
        addEmployeeModal.style.display = 'flex';
        addEmployeeForm.reset();
        clearErrors();
    }

    function hideModal() {
        addEmployeeModal.style.display = 'none';
        addEmployeeForm.reset();
        clearErrors();
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

    if (addEmployeeBtn) addEmployeeBtn.addEventListener('click', showModal);
    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
            hideModal();
        }
    });

    // Handle form submission
    if (addEmployeeForm) {
        addEmployeeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
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
                const response = await fetch('../api/employees.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(employeeData)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to add employee');
                }

                if (result.success) {
                    // Refresh employee list
                    await loadEmployees();
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
</script> 