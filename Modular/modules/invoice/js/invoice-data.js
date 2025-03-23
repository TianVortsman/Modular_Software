function collectInvoiceData() {
    // Collect data from select elements
    const invoiceType = document.getElementById('invoice-type').value;
    const customerName = document.getElementById('customer-name').value;
    const customerAddress = document.getElementById('Customer-adress').value;
    const billingAddress = document.getElementById('billing-adress').value;
    const currentDate = document.getElementById('current-date').value;
    const invoiceNumber = document.getElementById('invoice-number').value;
    const payInDays = document.getElementById('pay-in-days').value;
    const purchaseOrder = document.querySelector('[placeholder="Purchase Order #"]').value;
    const salesperson = document.querySelector('[placeholder="Salesperson"]').value;

    // Collect table rows data (invoice items)
    const invoiceRows = document.querySelectorAll('#invoice-rows tr');
    const items = [];
    invoiceRows.forEach(row => {
        const quantity = row.querySelector('.quantity').value;
        const itemCode = row.querySelector('.item-code').value;
        const description = row.querySelector('.description').value;
        const unitPrice = row.querySelector('.unit-price').value;
        const tax = row.querySelector('.tax').value;
        const total = row.querySelector('.total').innerText;

        items.push({ quantity, itemCode, description, unitPrice, tax, total });
    });

    // Collect totals
    const subtotal = document.getElementById('subtotal').innerText;
    const taxTotal = document.getElementById('tax-total').innerText;
    const finalTotal = document.getElementById('final-total').innerText;

    // Collect dealership (vehicle) details if visible
    let vehicleDetails = {};
    if (document.querySelector('.modal-dealership-vehicle-details').style.display !== 'none') {
        vehicleDetails = {
            model: document.getElementById('dealership-vehicle-model').value,
            vin: document.getElementById('dealership-vin').value,
            description: document.getElementById('dealership-vehicle-description').value,
            price: document.getElementById('dealership-vehicle-price').value,
            tax: document.getElementById('dealership-vehicle-tax').value,
            total: document.getElementById('dealership-vehicle-total').innerText
        };
    }

    // Collect extras/parts details if visible
    let extraParts = [];
    if (document.querySelector('.modal-dealership-extras').style.display !== 'none') {
        const extraRows = document.querySelectorAll('#dealership-invoice-rows tr');
        extraRows.forEach(row => {
            const partName = row.querySelector('.dealership-part-name').value;
            const description = row.querySelector('.dealership-description').value;
            const unitPrice = row.querySelector('.dealership-unit-price').value;
            const tax = row.querySelector('.dealership-tax').value;
            const total = row.querySelector('.dealership-total').innerText;

            extraParts.push({ partName, description, unitPrice, tax, total });
        });
    }

    // Compile all collected data into an object
    const invoiceData = {
        invoiceType,
        customerName,
        customerAddress,
        billingAddress,
        currentDate,
        invoiceNumber,
        payInDays,
        purchaseOrder,
        salesperson,
        items,
        subtotal,
        taxTotal,
        finalTotal,
        vehicleDetails,
        extraParts
    };

    console.log(invoiceData); // You can now send this data to your server

    return invoiceData;
}


