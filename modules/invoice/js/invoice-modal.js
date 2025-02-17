

function removeR(input) {
    // Remove the 'R' symbol while typing
    if (input.value.startsWith('R')) {
        input.value = input.value.slice(1); // Remove 'R' symbol
    }
}

function formatPrice(input) {
    let value = parseFloat(input.value.replace(/[^0-9.-]+/g, "")); // Remove any non-numeric characters
    
    if (!isNaN(value)) {
        if (value < 0) {
            input.value = `-R${Math.abs(value).toFixed(2)}`; // Format the value with '-R' for negative values
        } else {
            input.value = `R${value.toFixed(2)}`; // Format the value with 'R' for positive values
        }
    } else {
        input.value = "R0.00"; // Default if the value is not a valid number
    }
}

function formatDiscount(input) {
    // Strip out all non-numeric characters except for the decimal point and minus sign
    let value = input.value.replace(/[^0-9.-]/g, "");

    // Handle the case where the input is empty or just "-"
    if (value === "" || value === "-") {
        input.value = "-R0.00";
        return;
    }

    // Parse the value as a float
    let parsedValue = parseFloat(value);

    // If it's a valid number, format as negative with "R"
    if (!isNaN(parsedValue)) {
        // Ensure the value is negative
        if (parsedValue > 0) {
            parsedValue = -parsedValue;
        }
        input.value = `-R${Math.abs(parsedValue).toFixed(2)}`;
    } else {
        // If not a valid number, reset to the default
        input.value = "-R0.00";
    }
}




function closeInvoiceModal() {
    document.getElementById('invoice-modal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    function switchModal(type) {
        console.log('Switching modal to type:', type); // Debug log

        // Get modal elements
        const invoiceHeader = document.querySelector('.modal-invoice-header h2');
        const dealershipDetails = document.querySelector('.modal-dealership-vehicle-details');
        const dealershipExtras = document.querySelector('.modal-dealership-extras');
        const invoiceTable = document.querySelector('.modal-invoice-table-container');
        const recurringDateInput = document.getElementById('current-date');

        // Reset visibility of elements
        if (dealershipDetails && dealershipExtras && invoiceTable) {
            dealershipDetails.style.display = 'none';
            dealershipExtras.style.display = 'none';
            invoiceTable.style.display = 'block'; // Default back to normal invoice
        }

        if (recurringDateInput) recurringDateInput.disabled = true;

        // Switch based on invoice type
        switch (type) {
            case 'quotation':
                if (invoiceHeader) invoiceHeader.textContent = 'New Quotation';
                break;

            case 'vehicle-quotation':
                if (invoiceHeader) invoiceHeader.textContent = 'New Vehicle Quotation';
                if (dealershipDetails) dealershipDetails.style.display = 'block';
                if (dealershipExtras) dealershipExtras.style.display = 'block';
                if (invoiceTable) invoiceTable.style.display = 'none';
                setupPartRowListeners();
                updatePartTotals();
                break;

            case 'standard-invoice':
                if (invoiceHeader) invoiceHeader.textContent = 'New Invoice';
                break;

            case 'vehicle-invoice':
                if (invoiceHeader) invoiceHeader.textContent = 'New Vehicle Invoice';
                if (dealershipDetails) dealershipDetails.style.display = 'block';
                if (dealershipExtras) dealershipExtras.style.display = 'block';
                if (invoiceTable) invoiceTable.style.display = 'none';
                break;

            case 'recurring-invoice':
                if (invoiceHeader) invoiceHeader.textContent = 'New Recurring Invoice';
                if (recurringDateInput) recurringDateInput.disabled = false;
                break;

            default:
                if (invoiceHeader) invoiceHeader.textContent = 'New Invoice';
                break;
        }

        console.log('Modal visibility updated.');
    }

    // Add event listener to the dropdown
    const invoiceTypeDropdown = document.getElementById('invoice-type');
    if (invoiceTypeDropdown) {
        invoiceTypeDropdown.addEventListener('change', function () {
            const selectedType = this.value;
            console.log('Dropdown changed to:', selectedType); // Debug log
            switchModal(selectedType);
        });
    } else {
        console.error('Invoice type dropdown not found!');
    }
});

// Set the current date in the input field when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('current-date');
    const today = new Date();
    
    // Format the date as yyyy-mm-dd (ISO format) for the input field
    const formattedDate = today.toISOString().split('T')[0];
    
    // Set the value of the date input field
    dateInput.value = formattedDate;
  });

  function addItem() {
    console.log('Adding item...');

    // Get the invoice type from the dropdown
    const invoiceType = document.getElementById('invoice-type').value;

    // Get the relevant tables
    const invoiceTable = document.getElementById('invoice-table');
    const vehicleTable = document.getElementById('dealership-vehicle-table');

    // Define a variable to hold the table to add rows to
    let table;

    // Determine the table based on the selected invoice type
    if (invoiceType === 'vehicle-quotation' || invoiceType === 'vehicle-invoice') {
        // Use the vehicle table for vehicle-related invoices
        table = vehicleTable;
    } else {
        // Default to the standard invoice table
        table = invoiceTable;
    }

    // Ensure the table exists and is visible before appending a new row
    if (table && table.style.display !== 'none') {
        const row = document.createElement('tr');
        
        // Add content for the row based on the table being used
        if (table === invoiceTable) {
            row.innerHTML = `
                <td><input type="number" value="1" class="quantity"></td>
                <td style="display:none;"><input type="hidden" class="product-id"></td>
                <td>
                    <div class="search-container" style="position: relative;">
                        <input type="text" placeholder="Search Item Code" class="item-code" id="item-code" oninput="searchItem(this, 'item-code')">
                        <div class="search-dropdown1" id="search-results-code"></div>
                    </div>
                </td>
                <td>
                    <div class="search-container" style="position: relative;">
                        <input type="text" placeholder="Search Description" class="description" id="description" oninput="searchItem(this, 'description')">
                        <div class="search-dropdown2" id="search-results-desc"></div>
                    </div>
                </td>
                <td><input type="text" value="R0.00" class="unit-price" oninput="removeR(this)" onblur="formatPrice(this)" onfocus="removeR(this)"></td>
                <td>
                    <select class="tax">
                        <option value="0">[None]</option>
                        <option value="10">10%</option>
                        <option value="15">15%</option>
                        <option value="20">20%</option>
                        <option value="25">25%</option>
                    </select>
                </td>
                <td class="total-container">
                    <span class="total">R0.00</span>
                    <button class="remove-row" onclick="removeItem(event)">✖</button>
                </td>
                <td class="stock" style="display:none;">0</td>
            `;
        } else if (table === vehicleTable) {
            row.innerHTML = `
                <td><input type="number" value="1" class="dealership-quantity"></td>
                            <td>
                                <div class="search-container2" style="position: relative;">
                                    <input type="text" placeholder="Search Part Name" class="dealership-part-name" id="dealership-part-name" oninput="searchParts(this)">
                                    <div class="search-dropdown3" id="search-results-part-name"></div>
                                </div>
                            </td>
                            <td>
                                <div class="search-container2" style="position: relative;">
                                    <input type="text" placeholder="Search Description" class="dealership-description" id="dealership-description" oninput="searchParts(this)">
                                    <div class="search-dropdown4" id="search-results-description"></div>
                                </div>
                            </td>
                            <td><input type="text" value="R0.00" class="dealership-unit-price"></td>
                            <td>
                                <select class="dealership-tax">
                                    <option value="0">[None]</option>
                                    <option value="15">15%</option>
                                    <option value="20">20%</option>
                                </select>
                            </td>
                            <td class="dealership-total">R0.00</td>
                            <td class="dealership-stock" style="display:none;">0</td>
            `;
        }

        // Append the newly created row to the respective table body
        table.querySelector('tbody').appendChild(row);
    } else {
        console.error('No visible table found to add the item.');
    }
}


function addDiscount(){
    const table = document.getElementById('invoice-table');
    if (!table){
        console.error('Table element not found')
    }
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="number" value="1" class="quantity" disabled></td>
        <td style="display:none;"><input type="hidden" class="product-id"></td>
        <td><input type="text" placeholder="Discount" class="item-code" id="item-code" oninput="searchitem(this)" value="Discount" disabled></td>
        <td><input type="text" placeholder="Discount" class="description" oninput="searchitem(this)" value="Discount" disabled></td>
        <td><input type="text" value="-R0.00" class="unit-price" oninput="removeR(this)" onblur="formatDiscount(this)" onfocus="removeR(this)"></td>
        <td>
            <select class="tax" disabled>
                <option value="0">[None]</option >
            </select>
        </td>
        <td class="total-container">
            <span class="total">R0.00</span>
            <button class="remove-row" onclick="removeItem(event)">✖</button>
        </td>
        <td class="stock" style="display:none;">0</td>
    `;
    table.appendChild(row);
}

function removeItem(event) {
    const button = event.target;
    const row = button.closest('tr');
    if (row) {
        row.remove();
        updateTotals();
    } else {
        console.error('Row element not found');
    }
}




function searchVehicle(inputElement) {
    const searchTerm = inputElement.value.trim();
    const field = inputElement.id; // 'dealership-vehicle-model'

    const row = inputElement.closest('tr'); // Get the parent table row
    const resultsContainer1 = row.querySelector('.search-dropdown5');
    const resultsContainer2 = row.querySelector('.search-dropdown6');

    // Determine which dropdown container to use based on the search field
    const resultsContainer = (field === 'description') ? resultsContainer2 : resultsContainer1;

    if (searchTerm) {
        console.log('Search term:', searchTerm);
        console.log('Field:', field);

        // Show the appropriate dropdown based on the field
        resultsContainer.style.display = 'block';

        const xhr = new XMLHttpRequest();
        let searchUrl = '';
        // Use search-vehicle.php for vehicle-related search
        searchUrl = 'php/searchVehicles.php'; // Adjust the URL as necessary

        xhr.open('GET', `${searchUrl}?query=${encodeURIComponent(searchTerm)}`, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        console.log('AJAX request successful');
                        console.log('Response:', xhr.responseText);

                        const results = JSON.parse(xhr.responseText);
                        resultsContainer.innerHTML = ''; // Clear previous results

                        if (results.length > 0) {
                            const ul = document.createElement('ul');
                            ul.classList.add('search-results-list');
                            results.forEach((result) => {
                                const li = document.createElement('li');
                                li.classList.add('search-result-item');

                                // Display only vehicle name in the dropdown
                                li.textContent = result.vehicle_name; // Display only the vehicle name

                                // Autofill on click
                                li.addEventListener('click', () => {
                                    autofillVehicleDetails(row, result); // Pass the full result object
                                    resultsContainer.style.display = 'none'; // Hide the dropdown
                                });

                                ul.appendChild(li);
                            });

                            resultsContainer.appendChild(ul);

                            // Add a keydown listener for Tab key autofill
                            inputElement.addEventListener('keydown', function (event) {
                                if (event.key === 'Tab' && results.length > 0) {
                                    event.preventDefault(); // Prevent default tab behavior
                                    autofillVehicleDetails(row, results[0]); // Autofill with the first result
                                    resultsContainer.style.display = 'none'; // Hide the dropdown
                                }
                            });
                        } else {
                            resultsContainer.innerHTML = "<p>No results found.</p>";
                        }
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        resultsContainer.innerHTML = '<p>Error processing results. Please try again.</p>';
                    }
                } else {
                    console.error('AJAX request failed with status:', xhr.status);
                    resultsContainer.innerHTML = '<p>Error fetching results. Please try again.</p>';
                }
            }
        };
        xhr.send();
    } else {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none'; // Hide dropdown when no search term
    }
}


// Function to autofill vehicle details when a result is selected
function autofillVehicleDetails(row, result) {
    row.querySelector('#dealership-vehicle-model').value = result.vehicle_name;
    row.querySelector('#dealership-vin').value = result.vin_number || '';  // Access vin_number here
    row.querySelector('#dealership-vehicle-description').value = result.description;
    row.querySelector('#dealership-vehicle-price').value = `R${parseFloat(result.unit_price).toFixed(2)}`; // Append "R" to the price
    row.querySelector('#dealership-vehicle-tax').value = result.tax_percentage;
    const totalPrice = calculateTotal(result.unit_price, result.tax_percentage);
    row.querySelector('#dealership-vehicle-total').textContent = `R${totalPrice.toFixed(2)}`;
    updatePartTotals(); // Update the totals after autofill
}



// Function to calculate the total price with tax
function calculateTotal(price, tax) {
    return parseFloat(price) * (1 + (parseFloat(tax) / 100));
}






function searchItem(inputElement) {
    const searchTerm = inputElement.value.trim();
    const field = inputElement.id; // 'item-code', 'description', etc.

    const row = inputElement.closest('tr'); // Get the parent table row
    const resultsContainer1 = row.querySelector('.search-dropdown1');
    const resultsContainer2 = row.querySelector('.search-dropdown2');

    // Determine which dropdown container to use based on the search field
    const resultsContainer = (field === 'description') ? resultsContainer2 : resultsContainer1;

    if (searchTerm) {
        console.log('Search term:', searchTerm);
        console.log('Field:', field);

        // Show the appropriate dropdown based on the field
        resultsContainer.style.display = 'block';

        const xhr = new XMLHttpRequest();
        let searchUrl = '';
        // Use search-item.php for parts-related search
        searchUrl = 'php/search-item.php'; 

        xhr.open('GET', `${searchUrl}?field=${encodeURIComponent(field)}&query=${encodeURIComponent(searchTerm)}`, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        console.log('AJAX request successful');
                        console.log('Response:', xhr.responseText);

                        const results = JSON.parse(xhr.responseText);
                        resultsContainer.innerHTML = ''; // Clear previous results

                        if (results.length > 0) {
                            const ul = document.createElement('ul');
                            ul.classList.add('search-results-list');
                            results.forEach((result) => {
                                const li = document.createElement('li');
                                li.classList.add('search-result-item');

                                // Display based on the search field
                                if (field === 'description') {
                                    li.textContent = result.description;
                                } else {
                                    li.textContent = result.item_code; // Default to item_code if no match
                                }

                                // Autofill on click
                                li.addEventListener('click', () => {
                                    autofillRow(row, result); // Autofill the row with the selected result
                                    resultsContainer.style.display = 'none'; // Hide the dropdown after selection
                                });
                                ul.appendChild(li);
                            });
                            resultsContainer.appendChild(ul);

                            // Add a keydown listener for Tab key autofill
                            inputElement.addEventListener('keydown', function (event) {
                                if (event.key === 'Tab' && results.length > 0) {
                                    event.preventDefault(); // Prevent default tab behavior
                                    autofillRow(row, results[0]); // Autofill with the first result
                                    resultsContainer.style.display = 'none'; // Hide the dropdown
                                }
                            });
                        } else {
                            resultsContainer.innerHTML = "<p>No results found.</p>";
                        }
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        resultsContainer.innerHTML = '<p>Error processing results. Please try again.</p>';
                    }
                } else {
                    console.error('AJAX request failed with status:', xhr.status);
                    resultsContainer.innerHTML = '<p>Error fetching results. Please try again.</p>';
                }
            }
        };
        xhr.send();
    } else {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none'; // Hide dropdown when no search term
    }
}



function searchParts(inputElement) {
    const searchTerm = inputElement.value.trim();
    const field = inputElement.id === 'dealership-part-name' ? 'part-name' : 'description';

    const row = inputElement.closest('tr');
    const resultsContainer3 = row.querySelector('.search-dropdown3');
    const resultsContainer4 = row.querySelector('.search-dropdown4');

    const resultsContainer = field === 'part-name' ? resultsContainer3 : resultsContainer4;

    if (searchTerm) {
        console.log('Search term:', searchTerm);
        console.log('Field:', field);

        resultsContainer.style.display = 'block';

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `php/search-parts.php?field=${encodeURIComponent(field)}&query=${encodeURIComponent(searchTerm)}`, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        console.log('AJAX request successful');
                        console.log('Response:', xhr.responseText);

                        const results = JSON.parse(xhr.responseText);
                        resultsContainer.innerHTML = '';

                        if (results.length > 0) {
                            const ul = document.createElement('ul');
                            ul.classList.add('search-results-list');
                            results.forEach((result) => {
                                const li = document.createElement('li');
                                li.classList.add('search-result-item');
                                li.textContent = field === 'part-name' ? result.part_name : result.description;

                                li.addEventListener('click', () => {
                                    autofillPartRow(row, result);
                                    calculatePartRowTotal(row);
                                    updatePartTotals();                                    
                                    resultsContainer.style.display = 'none';
                                });
                                ul.appendChild(li);
                            });
                            resultsContainer.appendChild(ul);

                            inputElement.addEventListener('keydown', function (event) {
                                if (event.key === 'Tab' && results.length > 0) {
                                    event.preventDefault();
                                    autofillPartRow(row, results[0]);
                                    resultsContainer.style.display = 'none';
                                    calculatePartRowTotal(row);
                                    updatePartTotals();
                                }
                            });
                        } else {
                            resultsContainer.innerHTML = "<p>No results found.</p>";
                        }
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        resultsContainer.innerHTML = '<p>Error processing results. Please try again.</p>';
                    }
                } else {
                    console.error('AJAX request failed with status:', xhr.status);
                    resultsContainer.innerHTML = '<p>Error fetching results. Please try again.</p>';
                }
            }
        };
        xhr.send();
    } else {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
    }
}

function autofillRow(row, result) {
    // Autofill the row with part or vehicle details
    row.querySelector('.item-code').value = result.item_code || result.vehicle_code; // Either part code or vehicle code
    row.querySelector('.description').value = result.description;

    if (result.unit_price) {
        row.querySelector('.unit-price').value = `R${result.unit_price}`;
    }

    row.querySelector('.product-id').value = result.product_id;

    if (result.vehicle_code) {
        // Autofill vehicle-specific fields
        row.querySelector('.vehicle-model').value = result.vehicle_model || ''; // Autofill the vehicle model if available
        row.querySelector('.vehicle-registration').value = result.vehicle_registration || ''; // Autofill vehicle registration
    }

    // Set the tax percentage (same logic as before)
    const taxDropdown = row.querySelector('.tax');
    const taxPercentage = parseFloat(result.tax_percentage) || 0;
    const taxValue = Math.round(taxPercentage);

    for (let option of taxDropdown.options) {
        if (parseInt(option.value) === taxValue) {
            taxDropdown.value = option.value;
            break;
        }
    }

    // Recalculate row total after autofill
    calculateRowTotal(row);
    updateTotals();
}


function autofillPartRow(row, result) {
    // Autofill part-related details
    const partNameField = row.querySelector('.dealership-part-name');
    const descriptionField = row.querySelector('.dealership-description');
    const unitPriceField = row.querySelector('.dealership-unit-price');
    const taxDropdown = row.querySelector('.dealership-tax');

    // Autofill part name and description
    if (partNameField) {
        partNameField.value = result.part_name || ''; // Set part name if available
        console.log("Part Name Set:", result.part_name);
    }

    if (descriptionField) {
        descriptionField.value = result.description || ''; // Set description if available
        console.log("Description Set:", result.description);
    }

    // Autofill unit price
    if (unitPriceField && result.unit_price) {
        const unitPrice = parseFloat(result.unit_price.replace('R', '').trim()); // Remove currency symbol and parse as float
        unitPriceField.value = `R${unitPrice.toFixed(2)}`; // Set the formatted unit price
        console.log("Unit Price Set:", `R${unitPrice.toFixed(2)}`);
    }

    // Autofill tax value based on tax percentage
    const taxPercentage = parseFloat(result.tax_percentage) || 0;
    const taxValue = Math.round(taxPercentage);

    if (taxDropdown) {
        for (let option of taxDropdown.options) {
            if (parseFloat(option.value) === taxValue) { // Compare as numbers
                taxDropdown.value = option.value; // Set tax dropdown value
                console.log("Tax Value Set:", option.value);
                break;
            }
        }
    }

    // Recalculate the row total after autofill (similar to the other table)
    calculatePartRowTotal(row);
    updatePartTotals();
}






function calculateRowTotal(row) {
    // Retrieve input values
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0; // Default to 0 if no quantity
    const unitPrice = parseFloat(row.querySelector('.unit-price').value.replace(/[^\d.-]/g, '')) || 0; // Remove "R" and non-numeric characters
    const taxPercentage = parseFloat(row.querySelector('.tax').value) || 0; // Default to 0 if no tax value

    console.log('Calculating Row Total - Quantity:', quantity);
    console.log('Calculating Row Total - Unit Price:', unitPrice);
    console.log('Calculating Row Total - Tax Percentage:', taxPercentage);

    // Calculate row total before tax
    const rowTotalBeforeTax = quantity * unitPrice;
    // Calculate row tax
    const rowTax = (rowTotalBeforeTax * taxPercentage) / 100;
    // Calculate the final row total (including tax)
    const rowTotal = rowTotalBeforeTax + rowTax;

    // Update the row total field
    const totalElement = row.querySelector('.total');
    if (totalElement) {
        console.log('Row Total (including tax):', rowTotal.toFixed(2));
        
        // Format with "-R" for negatives
        const formattedTotal = rowTotal < 0 
            ? `-R${Math.abs(rowTotal).toFixed(2)}` 
            : `R${rowTotal.toFixed(2)}`;
        
        totalElement.textContent = formattedTotal; // Update the total element
    } else {
        console.log('Total element not found for this row.');
    }

    return {
        rowTotalBeforeTax: rowTotalBeforeTax,
        rowTax: rowTax,
        rowTotal: rowTotal
    };
}


function updateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    let finalTotal = 0;

    const rows = document.querySelectorAll('tr'); // Select all rows

    rows.forEach(row => {
        if (row.querySelector('.quantity') && row.querySelector('.unit-price') && row.querySelector('.tax')) {
            const rowTotals = calculateRowTotal(row); // Calculate row totals

            // Accumulate totals for all rows
            subtotal += rowTotals.rowTotalBeforeTax;
            totalTax += rowTotals.rowTax;
            finalTotal += rowTotals.rowTotal;
        }
    });

    // Update the summary section
    console.log('Subtotal:', subtotal.toFixed(2));
    document.querySelector('.subtotal').textContent = subtotal.toFixed(2);
    console.log('Total Tax:', totalTax.toFixed(2));
    document.querySelector('.tax-total').textContent = totalTax.toFixed(2);
    console.log('Final Total:', finalTotal.toFixed(2));
    document.querySelector('.final-total').textContent = finalTotal.toFixed(2);
}

function setupRowListeners() {
    const table = document.querySelector('.modal-invoice-table'); // Select the parent table (or container)

    // Attach a single event listener to the table (event delegation)
    table.addEventListener('input', function(event) {
        const row = event.target.closest('tr'); // Find the closest row that was clicked

        // Check if the input element is within the fields we want to listen for changes
        if (row && (event.target.classList.contains('quantity') || 
                    event.target.classList.contains('unit-price') || 
                    event.target.classList.contains('tax'))) {

            console.log('Input detected in row:', row);  // Log for debugging

            // Recalculate row total when quantity, unit price, or tax inputs change
            calculateRowTotal(row);

            // Update the grand totals (e.g., subtotal, tax, and final total)
            updateTotals();
        }
    });

    // Optionally, add a change event listener for non-input elements (e.g., for selecting from dropdowns)
    table.addEventListener('change', function(event) {
        const row = event.target.closest('tr');

        if (row && (event.target.classList.contains('quantity') || 
                    event.target.classList.contains('unit-price') || 
                    event.target.classList.contains('tax'))) {

            console.log('Change detected in row:', row);  // Log for debugging

            // Recalculate row total and update grand totals
            calculateRowTotal(row);
            updateTotals();
        }
    });
}



// Set up listeners for rows when the modal opens or any new rows are added
document.addEventListener('DOMContentLoaded', function() {
    setupRowListeners(); // Call once the page is loaded
});
function calculatePartRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.dealership-quantity').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.dealership-unit-price').value.replace(/[^\d.-]/g, '')) || 0;
    const taxPercentage = parseFloat(row.querySelector('.dealership-tax').value) || 0;

    const rowTotalBeforeTax = quantity * unitPrice;
    const rowTax = (rowTotalBeforeTax * taxPercentage) / 100;
    const rowTotal = rowTotalBeforeTax + rowTax;

    const totalElement = row.querySelector('.dealership-total');
    if (totalElement) {
        const formattedTotal = rowTotal < 0 
            ? `-R${Math.abs(rowTotal).toFixed(2)}` 
            : `R${rowTotal.toFixed(2)}`;
        totalElement.textContent = formattedTotal;
    }

    return {
        rowTotalBeforeTax: rowTotalBeforeTax,
        rowTax: rowTax,
        rowTotal: rowTotal
    };
}




function updatePartTotals() {
    let subtotal = 0;
    let totalTax = 0;
    let finalTotal = 0;

    // Update part totals (from parts table)
    const partRows = document.querySelectorAll('#dealership-invoice-rows tr');
    partRows.forEach(row => {
        if (row.querySelector('.dealership-quantity') && row.querySelector('.dealership-unit-price') && row.querySelector('.dealership-tax')) {
            const rowTotals = calculatePartRowTotal(row);
            console.log('Part Row Totals:', rowTotals);

            subtotal += rowTotals.rowTotalBeforeTax;
            totalTax += rowTotals.rowTax;
            finalTotal += rowTotals.rowTotal;
        }
    });

    console.log('Subtotal (Parts):', subtotal);
    console.log('Total Tax (Parts):', totalTax);
    console.log('Final Total Before Vehicles:', finalTotal);

    // Directly select the single vehicle row and the vehicle total element
    const vehicleTotalElement = document.querySelector('#dealership-vehicle-total'); // Directly target the vehicle total element
    console.log('Vehicle Total Element:', vehicleTotalElement);  // Check if it's correctly found

    if (vehicleTotalElement) {
        const rawText = vehicleTotalElement.textContent.trim();
        console.log('Raw Vehicle Total Text:', rawText);

        const vehicleTotal = parseFloat(rawText.replace('R', '').trim() || 0);
        console.log('Parsed Vehicle Total:', vehicleTotal);

        finalTotal += vehicleTotal;  // Add vehicle total to the final total
    } else {
        console.log('Vehicle total element not found.');
    }

    console.log('Final Total After Vehicles:', finalTotal);

    // Update the totals in the invoice
    const subtotalElement = document.querySelector('.subtotal');
    const taxTotalElement = document.querySelector('.tax-total');
    const finalTotalElement = document.querySelector('.final-total');

    if (subtotalElement) {
        subtotalElement.textContent = `R${subtotal.toFixed(2)}`;
    } else {
        console.log('Subtotal element not found.');
    }

    if (taxTotalElement) {
        taxTotalElement.textContent = `R${totalTax.toFixed(2)}`;
    } else {
        console.log('Tax total element not found.');
    }

    if (finalTotalElement) {
        finalTotalElement.textContent = `R${finalTotal.toFixed(2)}`;
    } else {
        console.log('Final total element not found.');
    }
}














function setupPartRowListeners() {
    const table = document.querySelector('#dealership-invoice-rows');

    table.addEventListener('input', function(event) {
        const row = event.target.closest('tr'); // Get the closest row to the input element

        // Check if the input is one of the fields we want to listen for changes
        if (row && (event.target.classList.contains('dealership-quantity') || 
                    event.target.classList.contains('dealership-unit-price') || 
                    event.target.classList.contains('dealership-tax'))) {

            // Recalculate the row total when any of these fields are updated
            calculatePartRowTotal(row);

            // Update the final totals for the invoice
            updatePartTotals();
        }
    });

    // Optionally, you can add event listeners for 'change' if needed
    table.addEventListener('change', function(event) {
        const row = event.target.closest('tr');

        if (row && (event.target.classList.contains('dealership-quantity') || 
                    event.target.classList.contains('dealership-unit-price') || 
                    event.target.classList.contains('dealership-tax'))) {

            // Recalculate row total when the value changes (e.g., using arrows or manual change)
            calculatePartRowTotal(row);
            updatePartTotals();
        }
    });
}


document.addEventListener('DOMContentLoaded', function() {
    setupPartRowListeners();
});



function openInvoiceModal() {
    document.getElementById('invoice-modal').style.display = 'flex';
    // Set up event listeners for inputs inside the modal
    setupRowListeners();
    

    // Calculate totals when the modal is opened
    updateTotals();
}


function searchCustomer(inputElement) {
    console.log('Searching for customer...'); // Debug log
    const query = inputElement.value.trim(); // Get the input value

    // Clear results if input is too short
    if (query.length < 2) {
        const dropdowns = document.querySelectorAll('.search-customer-dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.style.display = 'none'; // Hide dropdown
        });
        document.getElementById('search-results-customer').innerHTML = '';
        return;
    }

    // Send AJAX request to the server
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/searchCustomer.php?query=' + encodeURIComponent(query), true);
    xhr.onreadystatechange = function () {
        console.log('AJAX request state:', xhr.readyState); // Debug log
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            console.log('AJAX response:', response); // Debug log 

            // Clear previous results
            const resultsContainer = document.getElementById('search-results-customer');
            resultsContainer.innerHTML = '';

            const dropdowns = document.querySelectorAll('.search-customer-dropdown');

            if (response && response.length > 0) {
                // Show dropdown when there are results
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'block';
                });

                // Populate dropdown with search results
                response.forEach(customer => {
                    const div = document.createElement('div');
                    div.classList.add('search-result-customer');
                    div.textContent = customer.customer_name; // Display customer name
                    div.onclick = function () {
                        // Autofill customer details when selected
                        document.getElementById('customer-name').value = customer.customer_name;
                        document.getElementById('Customer-adress-1').value = customer.address_line_1 || '';
                        document.getElementById('Customer-adress-2').value = customer.address_line_2 || ''; // Correct ID
                        resultsContainer.innerHTML = ''; // Clear dropdown
                        dropdowns.forEach(dropdown => {
                            dropdown.style.display = 'none'; // Hide dropdown after selection
                        });
                    };
                    resultsContainer.appendChild(div);
                });

                // Add Tab key functionality to autofill the top result
                inputElement.addEventListener('keydown', function (e) {
                    if (e.key === 'Tab' && response.length > 0) {
                        // Prevent default tab behavior to prevent moving to the next element
                        e.preventDefault();

                        // Autofill the top result
                        const firstCustomer = response[0];
                        document.getElementById('customer-name').value = firstCustomer.customer_name;
                        document.getElementById('Customer-adress-1').value = firstCustomer.address_line_1 || '';
                        document.getElementById('Customer-adress-2').value = firstCustomer.address_line_2 || ''; // Correct ID

                        // Clear the dropdown
                        resultsContainer.innerHTML = '';
                        dropdowns.forEach(dropdown => {
                            dropdown.style.display = 'none'; // Hide dropdown after autofill
                        });
                    }
                });
            } else {
                // Hide dropdown and show "No results" message if no results found
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'block'; // Still show the dropdown for "No results" message
                });
                const noResults = document.createElement('div');
                noResults.textContent = 'No customers found';
                resultsContainer.appendChild(noResults);
            }
        }
    };
    xhr.send();
}