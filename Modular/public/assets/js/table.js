/**
 * NOVA Table - Reusable Data Table Component
 * A premium, feature-rich data table with advanced interactions
 * 
 * @author Your Name
 * @version 1.0.0
 */

class NovaTable {
    constructor(containerId, options = {}) {
      this.containerId = containerId;
      this.container = document.getElementById(containerId);
      
      if (!this.container) {
        throw new Error(`Container with ID '${containerId}' not found`);
      }
      
      // Register instance globally
      if (!window.novaTableInstances) {
        window.novaTableInstances = {};
      }
      window.novaTableInstances[containerId] = this;
  
      // Default configuration
      this.config = {
        data: [],
        columns: [],
        rowsPerPage: 10,
        rowsPerPageOptions: [10, 25, 50, 100],
        searchable: true,
        sortable: true,
        filterable: true,
        selectable: true,
        exportable: true,
        pagination: true,
        stickyHeader: true,
        maxHeight: '70vh',
        onDoubleClick: null,
        onSelectionChange: null,
        onDataChange: null,
        cssPrefix: 'nova-table',
        ...options
      };
  
      // Internal state
      this.filteredData = [];
      this.selectedRows = new Set();
      this.currentPage = 1;
      this.currentSort = { column: null, asc: true };
      this.searchQuery = '';
      this.columnFilters = {};
      this.lastSelectedIndex = -1;
      this.clickTimer = null;
      this.clickDelay = 200;
  
      // Elements
      this.elements = {};
      
      this.init();
    }
  
    init() {
      this.render();
      this.bindEvents();
      this.loadData(this.config.data);
    }
  
    render() {
      const { cssPrefix } = this.config;
      
      this.container.className = `${cssPrefix}-container`;
      this.container.innerHTML = `
        ${this.config.searchable || this.config.filterable ? this.renderSearchSection() : ''}
        <div class="${cssPrefix}-wrapper">
          <table id="${this.containerId}-table">
            <thead>
              ${this.renderHeaders()}
            </thead>
            <tbody></tbody>
          </table>
        </div>
        ${this.config.pagination ? `<div class="${cssPrefix}-pagination" id="${this.containerId}-pagination"></div>` : ''}
        <div class="${cssPrefix}-stats" id="${this.containerId}-stats"></div>
      `;
  
      // Cache important elements
      this.elements = {
        table: this.container.querySelector('table'),
        tbody: this.container.querySelector('tbody'),
        pagination: this.container.querySelector(`.${cssPrefix}-pagination`),
        stats: this.container.querySelector(`.${cssPrefix}-stats`),
        searchInput: this.container.querySelector(`.${cssPrefix}-search-input`),
        wrapper: this.container.querySelector(`.${cssPrefix}-wrapper`)
      };
  
      // Apply max height if specified
      if (this.config.maxHeight) {
        this.elements.wrapper.style.maxHeight = this.config.maxHeight;
        this.elements.wrapper.style.overflowY = 'auto';
      } else {
        // Let CSS handle the height
        this.elements.wrapper.style.maxHeight = 'none';
        this.elements.wrapper.style.overflowY = 'auto';
      }
    }
  
    renderSearchSection() {
      const { cssPrefix } = this.config;
      return `
        <div class="${cssPrefix}-search-container">
          <div class="${cssPrefix}-search-section">
            ${this.config.searchable ? `
              <div class="${cssPrefix}-search-wrapper">
                <input type="text" class="${cssPrefix}-search-input" placeholder="Search all columns...">
                <span class="${cssPrefix}-search-icon">🔍</span>
              </div>
            ` : ''}
            
            ${this.config.pagination ? `
              <div class="${cssPrefix}-rows-per-page">
                <label>Show:</label>
                <select class="${cssPrefix}-rows-select">
                  ${this.config.rowsPerPageOptions.map(num => 
                    `<option value="${num}" ${num === this.config.rowsPerPage ? 'selected' : ''}>${num}</option>`
                  ).join('')}
                </select>
                <span>rows</span>
              </div>
            ` : ''}
  
            ${this.config.filterable ? `
              <button class="${cssPrefix}-filter-toggle">
                <span class="${cssPrefix}-filter-icon">⚙</span>
                Filters
              </button>
            ` : ''}
          </div>
          
          ${this.config.filterable ? `
            <div class="${cssPrefix}-advanced-filters hidden">
              <div class="${cssPrefix}-filter-row">
                ${this.config.columns.map(col => {
                  if (col.filterable === false) return '';
                  
                  if (col.filterType === 'select' && col.filterOptions) {
                    return `
                      <select data-column="${col.key}" class="${cssPrefix}-column-filter">
                        <option value="">All ${col.label}</option>
                        ${col.filterOptions.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                      </select>
                    `;
                  } else {
                    return `<input type="text" placeholder="Filter ${col.label}..." data-column="${col.key}" class="${cssPrefix}-column-filter">`;
                  }
                }).join('')}
              </div>
            </div>
          ` : ''}
        </div>
      `;
    }
  
    renderHeaders() {
      return `
        <tr>
          ${this.config.columns.map(col => `
            <th data-column="${col.key}" class="${col.sortable !== false && this.config.sortable ? 'sortable' : ''}">
              <div class="header-content">
                <span class="header-text">${col.label}</span>
                ${col.sortable !== false && this.config.sortable ? '<span class="sort-icon"></span>' : ''}
              </div>
            </th>
          `).join('')}
        </tr>
      `;
    }
  
    renderBulkActionBar() {
      if (!this.config.selectable || this.selectedRows.size === 0) {
        this.hideBulkActionBar();
        return;
      }
  
      const { cssPrefix } = this.config;
      let bulkActionBar = this.container.querySelector(`.${cssPrefix}-bulk-action-bar`);
      
      if (!bulkActionBar) {
        bulkActionBar = document.createElement('div');
        bulkActionBar.className = `${cssPrefix}-bulk-action-bar hidden`;
        bulkActionBar.innerHTML = `
          <div class="${cssPrefix}-bulk-info">
            <span class="${cssPrefix}-bulk-selected-count"></span>
            <button class="${cssPrefix}-clear-selection">Clear</button>
          </div>
          <div class="${cssPrefix}-bulk-actions">
            ${this.config.exportable ? `
              <button class="${cssPrefix}-bulk-btn export">
                <span class="btn-icon">📊</span>
                Export CSV
              </button>
            ` : ''}
            <button class="${cssPrefix}-bulk-btn delete danger">
              <span class="btn-icon">🗑</span>
              Delete Selected
            </button>
          </div>
        `;
        
        this.elements.wrapper.parentNode.insertBefore(bulkActionBar, this.elements.wrapper);
      }
  
      const selectedText = bulkActionBar.querySelector(`.${cssPrefix}-bulk-selected-count`);
      selectedText.textContent = `${this.selectedRows.size} item${this.selectedRows.size > 1 ? 's' : ''} selected`;
      
      bulkActionBar.classList.remove('hidden');
      bulkActionBar.classList.add('visible');
    }
  
    hideBulkActionBar() {
      const { cssPrefix } = this.config;
      const bulkActionBar = this.container.querySelector(`.${cssPrefix}-bulk-action-bar`);
      if (bulkActionBar) {
        bulkActionBar.classList.remove('visible');
        bulkActionBar.classList.add('hidden');
      }
    }
  
    loadData(data) {
      this.config.data = data;
      this.renderTable();
    }
  
    renderTable() {
      this.filteredData = [...this.config.data];
  
      // Apply search filter
      if (this.searchQuery) {
        this.filteredData = this.filteredData.filter(row => 
          this.config.columns.some(col => {
            const value = this.getNestedValue(row, col.key);
            return value && value.toString().toLowerCase().includes(this.searchQuery.toLowerCase());
          })
        );
      }
  
      // Apply column filters
      Object.entries(this.columnFilters).forEach(([column, filterValue]) => {
        if (filterValue) {
          this.filteredData = this.filteredData.filter(row => {
            const value = this.getNestedValue(row, column);
            return value && value.toString().toLowerCase().includes(filterValue.toLowerCase());
          });
        }
      });
  
      // Apply sorting
      if (this.currentSort.column) {
        this.filteredData.sort((a, b) => {
          const valA = this.getNestedValue(a, this.currentSort.column);
          const valB = this.getNestedValue(b, this.currentSort.column);
          
          if (valA < valB) return this.currentSort.asc ? -1 : 1;
          if (valA > valB) return this.currentSort.asc ? 1 : -1;
          return 0;
        });
      }
  
      const totalFilteredRows = this.filteredData.length;
      const start = (this.currentPage - 1) * this.config.rowsPerPage;
      const end = start + this.config.rowsPerPage;
      const paginatedData = this.filteredData.slice(start, end);
  
      const htmlContent = paginatedData.map((row, index) => {
        const globalIndex = start + index;
        const isSelected = this.selectedRows.has(this.getRowId(row));
        const rowId = this.getRowId(row);
        const selectedClass = isSelected ? `${this.config.cssPrefix}-selected` : '';
        
        if (isSelected) {
          console.log(`Row ${rowId} is selected, applying class: ${selectedClass}`);
        }
        
        return `
          <tr data-id="${rowId}" data-index="${globalIndex}" class="${selectedClass}">
            ${this.config.columns.map(col => `
              <td data-field="${col.key}">
                ${this.renderCell(row, col)}
              </td>
            `).join('')}
          </tr>
        `;
      }).join('');
      
      this.elements.tbody.innerHTML = htmlContent;
      
      // Verify classes were applied
      setTimeout(() => {
        const selectedRows = this.elements.tbody.querySelectorAll('.nova-table-selected');
        console.log(`Found ${selectedRows.length} rows with nova-table-selected class in DOM`);
        selectedRows.forEach((row, i) => {
          console.log(`Selected row ${i}:`, row.classList.toString(), row.style.backgroundColor);
        });
      }, 100);
      
      // Add event listeners to rows after rendering
      this.addRowEventListeners();
  
      this.renderPagination(Math.ceil(totalFilteredRows / this.config.rowsPerPage));
      this.updateSortIcons();
      this.renderBulkActionBar();
      this.renderStats(totalFilteredRows);
    }
  
    renderCell(row, column) {
      const value = this.getNestedValue(row, column.key);
      
      if (column.render && typeof column.render === 'function') {
        return column.render(value, row);
      }
      
      if (column.type === 'badge') {
        const badgeClass = column.badgeClass ? column.badgeClass(value) : '';
        return `<span class="badge ${badgeClass}">${this.escapeHtml(value)}</span>`;
      }
      
      return this.escapeHtml(value);
    }
  
    renderPagination(totalPages) {
      if (!this.config.pagination || !this.elements.pagination) return;
  
      const buttons = [];
      
      // Previous button
      buttons.push(`
        <button ${this.currentPage === 1 ? 'disabled' : ''} 
                onclick="window.novaTableInstances['${this.containerId}'].goToPage(${this.currentPage - 1})">
          ←
        </button>
      `);
  
      // Page numbers
      for (let i = 1; i <= totalPages; i++) {
        buttons.push(`
          <button class="${i === this.currentPage ? 'active' : ''}"
                  onclick="window.novaTableInstances['${this.containerId}'].goToPage(${i})">
            ${i}
          </button>
        `);
      }
  
      // Next button
      buttons.push(`
        <button ${this.currentPage === totalPages ? 'disabled' : ''} 
                onclick="window.novaTableInstances['${this.containerId}'].goToPage(${this.currentPage + 1})">
          →
        </button>
      `);
  
      this.elements.pagination.innerHTML = buttons.join('');
    }
  
    renderStats(filteredCount) {
      if (!this.elements.stats) return;
  
      const start = ((this.currentPage - 1) * this.config.rowsPerPage) + 1;
      const end = Math.min(this.currentPage * this.config.rowsPerPage, filteredCount);
      
      this.elements.stats.innerHTML = `
        <div class="stats-info">
          Showing ${start}-${end} of ${filteredCount} entries
          ${filteredCount !== this.config.data.length ? `(filtered from ${this.config.data.length} total)` : ''}
        </div>
        ${this.config.selectable ? `
          <div class="stats-actions">
            <button onclick="window.novaTableInstances['${this.containerId}'].selectAllVisible()">Select All Visible</button>
            <button onclick="window.novaTableInstances['${this.containerId}'].selectAllFiltered()">Select All Filtered</button>
          </div>
        ` : ''}
      `;
    }
  
    bindEvents() {
      const { cssPrefix } = this.config;
  
      // Search functionality
      if (this.elements.searchInput) {
        this.elements.searchInput.addEventListener('input', this.debounce((e) => {
          this.searchQuery = e.target.value;
          this.currentPage = 1;
          this.renderTable();
        }, 300));
      }
  
      // Rows per page
      const rowsSelect = this.container.querySelector(`.${cssPrefix}-rows-select`);
      if (rowsSelect) {
        rowsSelect.addEventListener('change', (e) => {
          this.config.rowsPerPage = parseInt(e.target.value);
          this.currentPage = 1;
          this.renderTable();
        });
      }
  
      // Column filters
      this.container.querySelectorAll(`.${cssPrefix}-column-filter`).forEach(filter => {
        filter.addEventListener('input', this.debounce((e) => {
          const column = e.target.dataset.column;
          this.columnFilters[column] = e.target.value;
          this.currentPage = 1;
          this.renderTable();
        }, 300));
      });
  
      // Filter toggle
      const filterToggle = this.container.querySelector(`.${cssPrefix}-filter-toggle`);
      const advancedFilters = this.container.querySelector(`.${cssPrefix}-advanced-filters`);
      
      if (filterToggle && advancedFilters) {
        filterToggle.addEventListener('click', () => {
          advancedFilters.classList.toggle('hidden');
        });
      }
  
      // Sort headers
      this.container.querySelectorAll('th.sortable').forEach(header => {
        header.addEventListener('click', () => {
          const column = header.dataset.column;
          if (this.currentSort.column === column) {
            this.currentSort.asc = !this.currentSort.asc;
          } else {
            this.currentSort = { column, asc: true };
          }
          this.renderTable();
        });
      });
  
      // Bulk actions and row events
      this.container.addEventListener('click', (e) => {
        if (e.target.matches(`.${cssPrefix}-clear-selection`)) {
          this.clearSelection();
        } else if (e.target.matches(`.${cssPrefix}-bulk-btn.export`)) {
          this.exportSelected();
        } else if (e.target.matches(`.${cssPrefix}-bulk-btn.delete`)) {
          this.deleteSelected();
        }
      });
  
      // Global keyboard shortcuts
      document.addEventListener('keydown', (e) => {
        if (!this.container.contains(document.activeElement)) return;
        
        if (e.ctrlKey && e.key === 'a' && this.config.selectable) {
          e.preventDefault();
          this.selectAllVisible();
        } else if (e.key === 'Escape') {
          this.clearSelection();
        }
      });
    }
  
    // Event handlers
    handleRowClick(event, rowId, index) {
      console.log('handleRowClick called:', rowId, index, 'selectable:', this.config.selectable);
      if (!this.config.selectable) return;
      
      event.preventDefault();
      
      if (event.ctrlKey || event.metaKey) {
        // Multi-select
        if (this.selectedRows.has(rowId)) {
          this.selectedRows.delete(rowId);
        } else {
          this.selectedRows.add(rowId);
        }
      } else if (event.shiftKey && this.lastSelectedIndex !== -1) {
        // Range select
        const start = Math.min(this.lastSelectedIndex, index);
        const end = Math.max(this.lastSelectedIndex, index);
        const visibleRows = this.elements.tbody.querySelectorAll('tr');
        
        for (let i = start; i <= end && i < visibleRows.length; i++) {
          const row = visibleRows[i];
          const id = row.dataset.id;
          this.selectedRows.add(id);
        }
      } else {
        // Single select
        this.selectedRows.clear();
        this.selectedRows.add(rowId);
      }
      
      this.lastSelectedIndex = index;
      
      console.log('Selection state:', Array.from(this.selectedRows));
      console.log('Re-rendering table...');
      
      this.renderTable();
      
      if (this.config.onSelectionChange) {
        this.config.onSelectionChange(Array.from(this.selectedRows));
      }
    }
  
    handleRowDoubleClick(event, rowId) {
      console.log('handleRowDoubleClick called with rowId:', rowId);
      console.log('Available data rows:', this.config.data.map(r => ({ id: this.getRowId(r), document_id: r.document_id })));
      
      event.preventDefault();
      event.stopPropagation();
      
      const row = this.config.data.find(r => this.getRowId(r) === rowId);
      if (!row) {
        console.log('Row not found for double-click. Looking for rowId:', rowId);
        console.log('Available rowIds:', this.config.data.map(r => this.getRowId(r)));
        return;
      }
      
      console.log('Found row for double-click:', row);
      console.log('Executing onDoubleClick callback');
      if (this.config.onDoubleClick && typeof this.config.onDoubleClick === 'function') {
        this.config.onDoubleClick(row);
      }
    }
  
    handleRightClick(event, rowId) {
      console.log('handleRightClick called with rowId:', rowId);
      event.preventDefault();
      event.stopPropagation();
      
      // Remove any existing context menu
      this.removeContextMenu();
      
      // Get row data
      const row = this.config.data.find(r => this.getRowId(r) === rowId);
      if (!row) {
        console.log('Row not found for right-click. Looking for rowId:', rowId);
        console.log('Available rowIds:', this.config.data.map(r => this.getRowId(r)));
        return;
      }
      
      console.log('Found row for right-click:', row);
      
      // Create context menu
      const contextMenu = document.createElement('div');
      contextMenu.className = 'nova-context-menu';
      
      // Use dynamic actions if provided, otherwise use static actions, otherwise use defaults
      let actions;
      if (this.config.getContextMenuActions && typeof this.config.getContextMenuActions === 'function') {
        actions = this.config.getContextMenuActions(row);
      } else if (this.config.contextMenuActions) {
        actions = this.config.contextMenuActions;
      } else {
        actions = [
          { action: 'edit', label: 'Edit', icon: '✏️' },
          { action: 'view', label: 'View Details', icon: '👁️' },
          { action: 'duplicate', label: 'Duplicate', icon: '📋' },
          { action: 'export', label: 'Export', icon: '📤' },
          { action: 'delete', label: 'Delete', icon: '🗑️' }
        ];
      }
      
      const menuItems = actions.map((item, index) => {
        if (item.action === 'separator') {
          return '<div class="context-menu-separator"></div>';
        }
        return `
          <div class="context-menu-item" data-action="${item.action}">
            <span class="context-icon">${item.icon}</span>
            ${item.label}
          </div>
        `;
      }).join('');
      
      contextMenu.innerHTML = menuItems;
      
      // Position the menu
      contextMenu.style.left = event.pageX + 'px';
      contextMenu.style.top = event.pageY + 'px';
      
      // Add to document
      document.body.appendChild(contextMenu);
      
      // Store reference
      this.currentContextMenu = contextMenu;
      this.currentContextRow = row;
      
      // Add click handlers
      contextMenu.addEventListener('click', (e) => {
        const action = e.target.closest('.context-menu-item')?.dataset.action;
        if (action) {
          this.handleContextMenuAction(action, row);
        }
        this.removeContextMenu();
      });
      
      // Close menu when clicking outside
      setTimeout(() => {
        document.addEventListener('click', this.closeContextMenuHandler = () => {
          this.removeContextMenu();
        }, { once: true });
      }, 0);
    }
    
    removeContextMenu() {
      if (this.currentContextMenu) {
        this.currentContextMenu.remove();
        this.currentContextMenu = null;
        this.currentContextRow = null;
      }
      if (this.closeContextMenuHandler) {
        document.removeEventListener('click', this.closeContextMenuHandler);
        this.closeContextMenuHandler = null;
      }
    }
    
    handleContextMenuAction(action, row) {
      console.log(`Context menu action: ${action} for row:`, row);
      
      switch (action) {
        case 'edit':
          if (this.config.onDoubleClick) {
            this.config.onDoubleClick(row);
          }
          break;
        case 'view':
          // Trigger view action - could be customized per table
          console.log('View action triggered for:', row);
          break;
        case 'invoices':
          // Custom action for viewing invoices
          console.log('View invoices action triggered for:', row);
          break;
        case 'duplicate':
          // Trigger duplicate action
          console.log('Duplicate action triggered for:', row);
          break;
        case 'export':
          this.exportToCSV([row]);
          break;
        case 'delete':
          if (confirm('Are you sure you want to delete this item?')) {
            this.config.data = this.config.data.filter(r => this.getRowId(r) !== this.getRowId(row));
            this.renderTable();
            if (this.config.onDataChange) {
              this.config.onDataChange(this.config.data);
            }
          }
          break;
        default:
          // Allow custom actions to be handled by configuration
          if (this.config.onContextMenuAction) {
            this.config.onContextMenuAction(action, row);
          }
          break;
      }
    }
  
    // Navigation methods
    goToPage(page) {
      const totalPages = Math.ceil(this.filteredData.length / this.config.rowsPerPage);
      if (page >= 1 && page <= totalPages) {
        this.currentPage = page;
        this.renderTable();
      }
    }
  
    // Selection methods
    selectAllVisible() {
      const visibleRows = this.elements.tbody.querySelectorAll('tr');
      visibleRows.forEach(row => {
        this.selectedRows.add(row.dataset.id);
      });
      this.renderTable();
    }
  
    selectAllFiltered() {
      this.filteredData.forEach(row => {
        this.selectedRows.add(this.getRowId(row));
      });
      this.renderTable();
    }
  
    clearSelection() {
      this.selectedRows.clear();
      this.renderTable();
    }
  
    // Export methods
    exportSelected() {
      const selectedData = this.config.data.filter(row => 
        this.selectedRows.has(this.getRowId(row))
      );
      this.exportToCSV(selectedData);
    }
  
    exportToCSV(data) {
      const headers = this.config.columns.map(col => col.label);
      const rows = data.map(row => 
        this.config.columns.map(col => {
          const value = this.getNestedValue(row, col.key);
          return `"${value || ''}"`;
        }).join(',')
      );
      
      const csvContent = [headers.join(','), ...rows].join('\n');
      this.downloadFile(csvContent, 'export.csv', 'text/csv');
    }
  
    // Utility methods
    getRowId(row) {
      // For document data, use document_id as the primary identifier
      if (row.document_id) {
        return String(row.document_id);
      }
      // Fallback to other common ID fields
      const id = row.id || row._id || row.document_id || row.invoice_id || row.quotation_id;
      if (id) {
        return String(id);
      }
      // Last resort: use a hash of the row data
      const rowString = JSON.stringify(row);
      return String(rowString.length) + '_' + rowString.substring(0, 50);
    }
  
    getNestedValue(obj, path) {
      return path.split('.').reduce((current, key) => current?.[key], obj);
    }
  
    escapeHtml(unsafe) {
      if (unsafe === null || unsafe === undefined) return '';
      return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }
  
    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
  
    downloadFile(content, filename, contentType) {
      const blob = new Blob([content], { type: contentType });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }
  
    addRowEventListeners() {
      console.log('Adding row event listeners for', this.containerId);
      
      // Remove existing listeners first
      if (this.rowClickHandler) {
        this.elements.tbody.removeEventListener('click', this.rowClickHandler);
        this.elements.tbody.removeEventListener('dblclick', this.rowDoubleClickHandler);
        this.elements.tbody.removeEventListener('contextmenu', this.rowContextMenuHandler);
      }
      
      // Create bound handlers
      this.rowClickHandler = (e) => {
        console.log('Row click handler called', e.target);
        const row = e.target.closest('tr');
        if (!row || !row.dataset.id) {
          console.log('No valid row found', row);
          return;
        }
        
        const rowId = row.dataset.id;
        const index = parseInt(row.dataset.index);
        
        // Handle click with delay to distinguish from double-click
        if (this.clickTimer) {
          clearTimeout(this.clickTimer);
          this.clickTimer = null;
          return; // This is part of a double-click, don't handle as single click
        }
        
        this.clickTimer = setTimeout(() => {
          console.log('Processing delayed click:', rowId, index);
          this.handleRowClick(e, rowId, index);
          this.clickTimer = null;
        }, this.clickDelay);
      };
      
      this.rowDoubleClickHandler = (e) => {
        // Clear any pending single click
        if (this.clickTimer) {
          clearTimeout(this.clickTimer);
          this.clickTimer = null;
        }
        
        const row = e.target.closest('tr');
        if (!row || !row.dataset.id) return;
        
        const rowId = row.dataset.id;
        console.log('Processing double-click:', rowId);
        this.handleRowDoubleClick(e, rowId);
      };
      
      this.rowContextMenuHandler = (e) => {
        const row = e.target.closest('tr');
        if (!row || !row.dataset.id) return;
        
        const rowId = row.dataset.id;
        this.handleRightClick(e, rowId);
      };
      
      // Add listeners
      this.elements.tbody.addEventListener('click', this.rowClickHandler);
      this.elements.tbody.addEventListener('dblclick', this.rowDoubleClickHandler);
      this.elements.tbody.addEventListener('contextmenu', this.rowContextMenuHandler);
      
      console.log('Event listeners added successfully for', this.containerId);
    }
  
    updateSortIcons() {
      this.container.querySelectorAll('th.sortable').forEach(header => {
        header.classList.remove('sorted-asc', 'sorted-desc');
        const column = header.dataset.column;
        if (column === this.currentSort.column) {
          header.classList.add(this.currentSort.asc ? 'sorted-asc' : 'sorted-desc');
        }
      });
    }
  
    deleteSelected() {
      if (this.selectedRows.size === 0) return;
      
      if (confirm(`Delete ${this.selectedRows.size} selected item(s)?`)) {
        this.config.data = this.config.data.filter(row => 
          !this.selectedRows.has(this.getRowId(row))
        );
        this.selectedRows.clear();
        this.renderTable();
        
        if (this.config.onDataChange) {
          this.config.onDataChange(this.config.data);
        }
      }
    }
  
    // Public API methods
    refresh() {
      this.renderTable();
    }
    
    // Debug method to force visual selection
    debugForceSelection(rowId) {
      console.log('Force selecting row:', rowId);
      this.selectedRows.add(String(rowId));
      this.renderTable();
      
      // Also manually apply styles for testing
      setTimeout(() => {
        const row = this.elements.tbody.querySelector(`tr[data-id="${rowId}"]`);
        if (row) {
          row.classList.add('nova-table-selected');
          row.style.background = 'rgba(59, 130, 246, 0.15)';
          row.style.boxShadow = 'inset 4px 0 0 #3b82f6';
          console.log('Manually applied styles to row:', row);
        } else {
          console.log('Could not find row with data-id:', rowId);
        }
      }, 100);
    }
  
    updateData(newData) {
      this.loadData(newData);
    }
  
    getSelectedRows() {
      return this.config.data.filter(row => 
        this.selectedRows.has(this.getRowId(row))
      );
    }
  
    destroy() {
      // Remove event listeners
      if (this.elements.tbody) {
        this.elements.tbody.removeEventListener('click', this.rowClickHandler);
        this.elements.tbody.removeEventListener('dblclick', this.rowDoubleClickHandler);
        this.elements.tbody.removeEventListener('contextmenu', this.rowContextMenuHandler);
      }
      
      // Clean up context menu
      this.removeContextMenu();
      
      if (window.novaTableInstances) {
        delete window.novaTableInstances[this.containerId];
      }
      this.container.innerHTML = '';
    }
  }

// Make NovaTable globally available
window.NovaTable = NovaTable;

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NovaTable;
}