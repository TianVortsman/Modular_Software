document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to the clicked tab
            tab.classList.add('active');
            
            // Show the corresponding tab content
            const tabId = tab.dataset.tab;
            document.querySelector(`#${tabId}`).classList.add('active');
        });
    });
    // Modal open/close
    const openModalBtns = document.querySelectorAll('.open-modal');
    const closeModalBtns = document.querySelectorAll('.modal-close');
    const modals = document.querySelectorAll('.modal');
    const modalOverlay = document.querySelector('.modal-overlay');

    openModalBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = btn.dataset.modalId;
            const modal = document.querySelector(`#${modalId}`);
            modal.style.display = 'block';
            modalOverlay.style.display = 'flex';
        });
    });

    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = btn.closest('.modal');
            modal.style.display = 'none';
            modalOverlay.style.display = 'none';
        });
    });

    // Close modals when clicking outside of modal content
    // modalOverlay.addEventListener('click', function (e) {
    //     if (e.target === modalOverlay) {
    //         modals.forEach(modal => {
    //             modal.style.display = 'none';
    //         });
    //         modalOverlay.style.display = 'none';
    //     }
    // });

    // // Handle bulk actions (checkbox select and action button)
    // const bulkSelectCheckbox = document.querySelector('#bulk-select');
    // const bulkActionBtns = document.querySelectorAll('.btn-action');
    // const rows = document.querySelectorAll('.table tr');

    // bulkSelectCheckbox.addEventListener('change', function () {
    //     const isChecked = bulkSelectCheckbox.checked;
    //     rows.forEach(row => {
    //         const checkbox = row.querySelector('input[type="checkbox"]');
    //         if (checkbox) {
    //             checkbox.checked = isChecked;
    //         }
    //     });
    // });

    // bulkActionBtns.forEach(btn => {
    //     btn.addEventListener('click', function () {
    //         // Get selected rows and handle bulk actions (like deleting or marking paid)
    //         const selectedRows = Array.from(rows).filter(row => row.querySelector('input[type="checkbox"]:checked'));
    //         if (selectedRows.length > 0) {
    //             // Example: Apply bulk action like marking invoices as paid
    //             selectedRows.forEach(row => {
    //                 // Simulating action: changing badge class
    //                 const badge = row.querySelector('.badge');
    //                 if (badge) {
    //                     badge.classList.remove('pending');
    //                     badge.classList.add('paid');
    //                 }
    //             });
    //             alert(`${selectedRows.length} items processed.`);
    //         } else {
    //             alert('Please select at least one row to apply the action.');
    //         }
    //     });
    // });

    // Handle form submissions in modals
    const modalForms = document.querySelectorAll('.modal form');
    modalForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Handle form data (For now, just show an alert)
            const formData = new FormData(form);
            let data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });

            console.log('Form Submitted:', data);
            alert('Form Submitted!');
            
            // Close the modal
            const modal = form.closest('.modal');
            modal.style.display = 'none';
            modalOverlay.style.display = 'none';
        });
    });

    // Modal for adding a payment/credit/refund (with example logic)
    const addPaymentModal = document.querySelector('#add-payment-modal');
    const addPaymentForm = document.querySelector('#add-payment-form');

    if (addPaymentForm) {
        addPaymentForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const paymentAmount = addPaymentForm.querySelector('input[name="payment-amount"]').value;
            const paymentMethod = addPaymentForm.querySelector('select[name="payment-method"]').value;

            if (!paymentAmount || !paymentMethod) {
                alert('Please fill in all fields.');
                return;
            }

            // Simulate adding a new payment row
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="checkbox"></td>
                <td>New Payment</td>
                <td>${paymentAmount}</td>
                <td>${paymentMethod}</td>
                <td><span class="badge paid">Paid</span></td>
                <td><button class="button">View</button></td>
            `;
            document.querySelector('.table tbody').appendChild(newRow);

            // Close the modal
            addPaymentModal.style.display = 'none';
            modalOverlay.style.display = 'none';
            alert('Payment added successfully.');
        });
    }
});
