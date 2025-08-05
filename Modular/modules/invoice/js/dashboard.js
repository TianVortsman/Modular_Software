// Dashboard functionality - buildQueryParams is now available globally from helpers.js

// Add custom context menu HTML to the page
const contextMenu = document.createElement('div');
contextMenu.className = 'custom-context-menu';
contextMenu.innerHTML = `
  <ul id="context-menu-list"></ul>
`;
document.body.appendChild(contextMenu);

function hideContextMenu() {
  contextMenu.style.display = 'none';
}
document.addEventListener('click', hideContextMenu);
document.addEventListener('scroll', hideContextMenu, true);

document.addEventListener('DOMContentLoaded', async function() {
    const rangeSelector = document.getElementById('dashboard-range-selector');
    const chartMonthsSelector = document.getElementById('dashboard-chart-months');
    const chartTypeSelector = document.getElementById('dashboard-chart-type');
    let currentRange = rangeSelector ? rangeSelector.value : 'this_month';
    let currentChartMonths = chartMonthsSelector ? parseInt(chartMonthsSelector.value, 10) : 6;
    let currentChartType = chartTypeSelector ? chartTypeSelector.value : 'bar';

    // Chart.js instance
    let chartInstance = null;

    function showContextMenu(e, invoice) {
      e.preventDefault();
      const menuList = document.getElementById('context-menu-list');
      menuList.innerHTML = '';
      // Always available options
      const options = [
        { label: 'Edit', action: async (invoice) => {
          // Fetch full document data, open modal in edit mode, and fill form
          try {
            const res = await fetch(`../api/document_modal.php?action=fetch_document&document_id=${encodeURIComponent(invoice.document_id)}`);
            const result = await res.json();
            window.handleApiResponse(result);
          } catch (err) {
            showResponseModal('Error loading document: ' + (err.message || err), 'error');
          }
        } },
        { label: 'Finalize', action: () => {/* TODO: implement */} },
        { label: 'Approve', action: () => {/* TODO: implement */} },
        { label: 'Email to client', action: () => {/* TODO: implement */} },
        { label: 'Send payment reminder', action: () => {/* TODO: implement */} },
        { label: 'Show Client', action: () => {/* TODO: implement */} },
        { label: 'View Invoices for this client', action: () => {/* TODO: implement */} },
        { label: 'Add Credit note', action: () => {/* TODO: implement */} },
        { label: 'Refund', action: () => {/* TODO: implement */} },
        { label: 'See Payment terms', action: () => {/* TODO: implement */} },
      ];
      // Only show Delete if status is Draft
      if ((invoice.status_name || '').toLowerCase() === 'draft') {
        options.splice(2, 0, { label: 'Delete', action: () => {/* TODO: implement */}, className: 'danger' });
      }
      options.forEach(opt => {
        const li = document.createElement('li');
        li.textContent = opt.label;
        if (opt.className) li.className = opt.className;
        li.onclick = (ev) => {
          ev.stopPropagation();
          hideContextMenu();
          opt.action(invoice);
        };
        menuList.appendChild(li);
      });
      contextMenu.style.display = 'block';
      contextMenu.style.left = e.pageX + 'px';
      contextMenu.style.top = e.pageY + 'px';
    }

    async function updateDashboard() {
        // Cards (use range selector) - Re-enabled with global functions
        try {
            const cards = await window.fetchDashboardCards ? window.fetchDashboardCards(currentRange) : { total_invoices: 0, total_revenue: 0, unpaid_invoices: 0, pending_payments: 0, expenses_this_month: 0, taxes_due: 0, recurring_invoices: 0 };
            
            const totalInvoicesWidget = document.getElementById('total-invoices-widget');
            const totalRevenueWidget = document.getElementById('total-revenue-widget');
            const totalUnpaidInvoicesWidget = document.getElementById('total-unpaid-invoices-widget');
            const pendingPaymentsWidget = document.getElementById('pending-payments-widget');
            const monthExpensesWidget = document.getElementById('month-expenses-widget');
            const taxesDueWidget = document.getElementById('taxes-due-widget');
            const totalRecurringInvoicesWidget = document.getElementById('total-recurring-invoices-widget');
            
            if (totalInvoicesWidget) totalInvoicesWidget.querySelector('p').textContent = cards.total_invoices || 0;
            if (totalRevenueWidget) totalRevenueWidget.querySelector('p').textContent = 'R' + (cards.total_revenue || 0);
            if (totalUnpaidInvoicesWidget) totalUnpaidInvoicesWidget.querySelector('p').textContent = cards.unpaid_invoices || 0;
            if (pendingPaymentsWidget) pendingPaymentsWidget.querySelector('p').textContent = 'R' + (cards.pending_payments || 0);
            if (monthExpensesWidget) monthExpensesWidget.querySelector('p').textContent = 'R' + (cards.expenses_this_month || 0);
            if (taxesDueWidget) taxesDueWidget.querySelector('p').textContent = 'R' + (cards.taxes_due || 0);
            if (totalRecurringInvoicesWidget) totalRecurringInvoicesWidget.querySelector('p').textContent = (cards.recurring_invoices || 0) + ' Active';
        } catch (error) {
            console.log('Dashboard cards not available:', error.message);
        }

        // Recent Invoices Table - Re-enabled with global functions
        try {
            const response = await window.fetchInvoices ? window.fetchInvoices(currentRange) : { success: false, data: [] };
            const tbody = document.getElementById('recent-invoices-tbody');
            if (tbody) {
                tbody.innerHTML = '';
                
                if (!response.success) {
                    tbody.innerHTML = '<tr><td colspan="7">Failed to load invoices.</td></tr>';
                } else {
                    const invoices = response.data || [];
                    
                    if (invoices.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7">No invoices found.</td></tr>';
                    } else {
                        invoices.forEach(inv => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${inv.invoice_number}</td>
                                <td>${inv.client_name || ''}</td>
                                <td>${inv.invoice_date}</td>
                                <td><span class="status">${inv.status_name || inv.status_id}</span></td>
                                <td>R${inv.total_amount}</td>
                                <td>${inv.due_date || ''}</td>
                                <td><button class="action-button">View</button></td>
                            `;
                            // Add right-click event for context menu
                            tr.addEventListener('contextmenu', function(e) {
                                showContextMenu(e, inv);
                            });
                            tbody.appendChild(tr);
                        });
                    }
                }
            }
        } catch (error) {
            console.log('Recent invoices not available:', error.message);
        }
        

        // Recurring Invoices Table - Re-enabled with global functions
        try {
            const recs = await window.fetchRecurringInvoices ? window.fetchRecurringInvoices(currentRange) : [];
            const recTbody = document.getElementById('recurring-invoices-tbody');
            if (recTbody) {
                recTbody.innerHTML = '';
                recs.forEach(rec => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${rec.invoice_number || ''}</td>
                        <td>${rec.company_id || rec.customer_id || ''}</td>
                        <td>${rec.start_date || ''}</td>
                        <td>${rec.next_generation || ''}</td>
                        <td>${rec.frequency || ''}</td>
                        <td><button class="action-button">Edit</button></td>
                    `;
                    recTbody.appendChild(tr);
                });
            }
        } catch (error) {
            console.log('Recurring invoices not available:', error.message);
        }

        // Chart (use chartMonthsSelector, not rangeSelector) - Re-enabled with global functions
        try {
            const chartData = await window.fetchInvoiceChartData ? window.fetchInvoiceChartData('6months') : [];
            const chartCanvas = document.getElementById('invoiceChart');
            if (chartCanvas && typeof Chart !== 'undefined') {
                const ctx = chartCanvas.getContext('2d');
                // Always show last N months as groups, ending with current month
                const now = new Date();
                const months = [];
                for (let i = currentChartMonths - 1; i >= 0; i--) {
                    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                    months.push(d.toISOString().slice(0, 7)); // 'YYYY-MM'
                }
                // Map chartData to a lookup by YYYY-MM
                const dataMap = {};
                chartData.forEach(row => {
                    const key = row.month ? row.month.slice(0, 7) : '';
                    dataMap[key] = row;
                });
                // Build arrays for each dataset, filling missing months with 0
                const labels = months;
                const paid = months.map(m => dataMap[m]?.paid || 0);
                const unpaid = months.map(m => dataMap[m]?.unpaid || 0);
                const recurring = months.map(m => dataMap[m]?.recurring || 0);
                // Set canvas height for compact look
                chartCanvas.height = 220;
                const chartConfig = {
                    type: 'bar', // Always grouped bar chart
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Paid',
                                data: paid,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Unpaid',
                                data: unpaid,
                                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Recurring',
                                data: recurring,
                                backgroundColor: 'rgba(255, 206, 86, 0.7)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: true }
                        }
                    }
                };
                if (chartInstance) chartInstance.destroy();
                chartInstance = new Chart(ctx, chartConfig);
            }
        } catch (error) {
            console.log('Chart not available:', error.message);
        }
    }

    if (rangeSelector) {
        rangeSelector.addEventListener('change', function() {
            currentRange = this.value;
            updateDashboard();
        });
    }
    if (chartMonthsSelector) {
        chartMonthsSelector.addEventListener('change', function() {
            currentChartMonths = parseInt(this.value, 10);
            updateDashboard();
        });
    }
    if (chartTypeSelector) {
        chartTypeSelector.addEventListener('change', function() {
            currentChartType = this.value;
            updateDashboard();
        });
    }

    const openDocumentBtn = document.getElementById('open-invoice-modal-btn');
    if (openDocumentBtn) {
        openDocumentBtn.addEventListener('click', () => window.openDocumentModal('create'));
    }

    const closeDocumentBtn = document.getElementById('modal-invoice-close-btn');
    if (closeDocumentBtn) {
        closeDocumentBtn.addEventListener('click', window.closeDocumentModal);
    }

    // Initial load
    updateDashboard();
});