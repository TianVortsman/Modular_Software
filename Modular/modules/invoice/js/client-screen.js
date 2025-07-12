import { showAddCompanyModal, showAddCustomerModal, openCompanyModal, openCustomerModal, closeModal, updateModalForms  } from './client-modal.js';
import { fetchClientData } from './client-api.js';

window.showAddCustomerModal = showAddCustomerModal;
window.showAddCompanyModal = showAddCompanyModal;

let currentPage = 1;
let rowsPerPage = 10;
let currentSection = 'private';
let customers = [];
let companies = [];


function renderTable() {
    const data = currentSection === 'private' ? customers : companies;
    const filteredData = filterData(data);
    const paginatedData = paginateData(filteredData);
    const tbody = document.querySelector(`#client-section${currentSection === 'private' ? '1' : '2'} tbody`);
    if (!tbody) return;
    tbody.innerHTML = '';
    paginatedData.forEach(item => {
        const row = document.createElement('tr');
        row.setAttribute('data-type', currentSection === 'private' ? 'customer' : 'company');
        row.setAttribute('data-id', currentSection === 'private' ? item.customer_id : item.company_id);
        row.classList.add('clickable-row');
        row.innerHTML = currentSection === 'private'
            ? `<td>${item.customer_id}</td><td>${item.first_name} ${item.last_name}</td><td>${item.email || '-'}</td><td>${item.phone || '-'}</td><td>${item.last_invoice_date || '-'}</td><td>R${parseFloat(item.outstanding_balance || 0).toFixed(2)}</td><td>${item.total_invoices || 0}</td>`
            : `<td>${item.company_id}</td><td>${item.company_name}</td><td>${item.contact_email || '-'}</td><td>${item.contact_phone || '-'}</td><td>${item.last_invoice_date || '-'}</td><td>R${parseFloat(item.outstanding_balance || 0).toFixed(2)}</td><td>${item.total_invoices || 0}</td>`;
        row.addEventListener('dblclick', function() {
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');
            if (type === 'customer') openCustomerModal(id);
            else if (type === 'company') openCompanyModal(id);
        });
        tbody.appendChild(row);
    });
    renderPagination(filteredData.length);
}


async function loadData() {
    showLoadingModal('Loading...');
    try {
        const result = await fetchClientData(currentSection);
        if (result.success) {
            if (currentSection === 'private') {
                customers = result.data;
            } else {
                companies = result.data;
            }
            renderTable();
        } else {
            showResponseModal('Failed to load data: ' + result.error);
        }
    } catch (error) {
        showResponseModal('Failed to load data: ' + error.message);
    } finally {
        hideLoadingModal();
    }
}

window.loadData = loadData;

function setupEventListeners() {
    const btn1 = document.getElementById('clientSectionButton1');
    const btn2 = document.getElementById('clientSectionButton2');
    if (btn1) btn1.addEventListener('click', () => switchSection('private'));
    if (btn2) btn2.addEventListener('click', () => switchSection('business'));
    const search = document.getElementById('client-search');
    if (search) search.addEventListener('input', handleSearch);
    document.querySelectorAll('.rows-per-page').forEach(select => {
        select.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });
    });
    // Only event listeners for opening modals
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    if (addCustomerBtn) addCustomerBtn.addEventListener('click', showAddCustomerModal);
    const addCompanyBtn = document.getElementById('addCompanyBtn');
    if (addCompanyBtn) addCompanyBtn.addEventListener('click', showAddCompanyModal);
    // Modal close/cancel buttons
    ['closeCustomerModal', 'closeCompanyModal', 'cancelCustomer', 'cancelCompany'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', () => closeModal(id.includes('Customer') ? 'customerModal' : 'companyModal'));
    });
    window.addEventListener('click', function(event) {
        const customerModal = document.getElementById('customerModal');
        const companyModal = document.getElementById('companyModal');
        if (event.target === customerModal) customerModal.style.display = 'none';
        if (event.target === companyModal) companyModal.style.display = 'none';
    });
}

function switchSection(section) {
    currentSection = section;
    currentPage = 1;
    document.getElementById('clientSectionButton1').classList.toggle('active', section === 'private');
    document.getElementById('clientSectionButton2').classList.toggle('active', section === 'business');
    document.getElementById('client-section1').classList.toggle('active', section === 'private');
    document.getElementById('client-section2').classList.toggle('active', section === 'business');
    updateModalForms();
    loadData();
}

function filterData(data) {
    const searchTerm = document.getElementById('client-search').value.toLowerCase();
    if (!searchTerm) return data;
    return data.filter(item => {
        if (currentSection === 'private') {
            return `${item.first_name} ${item.last_name}`.toLowerCase().includes(searchTerm) || item.customer_id.toString().includes(searchTerm);
        } else {
            return item.company_name.toLowerCase().includes(searchTerm) || item.company_id.toString().includes(searchTerm);
        }
    });
}

function paginateData(data) {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    return data.slice(start, end);
}

function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / rowsPerPage);
    const container = document.getElementById(`pagination-container${currentSection === 'private' ? '1' : '2'}`);
    if (!container) return;
    let html = '';
    html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
    }
    html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    container.innerHTML = html;
}

window.changePage = function(page) {
    const totalPages = Math.ceil((currentSection === 'private' ? customers : companies).length / rowsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
    }
};

function handleSearch() {
    currentPage = 1;
    renderTable();
}

document.addEventListener('DOMContentLoaded', () => {
    setupEventListeners();
    loadData();
});