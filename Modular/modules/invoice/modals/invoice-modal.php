<!-- Modal Invoice -->
<div class="modal-invoice-container" id="invoice-modal">
    <div class="modal-invoice-content">
        <div class="modal-invoice-header">
            <h2>New Invoice</h2>
            <span class="modal-invoice-close" onclick="closeInvoiceModal()">&times;</span>
        </div>

        <!-- Top Section: Input Fields and Table -->
        <div class="modal-invoice-top-section">
            <div class="modal-invoice-form">
                <div class="modal-invoice-left">
                    <select id="invoice-type">
                        <option value="quotation" onclick="switchModal('quote')">Quotation</option>
                        <option value="vehicle-quotation" onclick="switchModal('vquote')">Vehicle Quotation</option>
                        <option value="standard-invoice" onclick="switchModal('sinvoice')">Invoice</option>
                        <option value="vehicle-invoice" onclick="switchModal('vinvoice')">Vehicle Invoice</option>
                        <option value="recurring-invoice" onclick="switchModal('rinvoice')">Recurring Invoice</option>
                    </select>
                    <div class="search-customer-container" style="position: relative;">
                        <input type="text" placeholder="Customer Name" id="customer-name" oninput="searchCustomer(this)">
                        <div class="search-customer-dropdown" id="search-results-customer"></div>
                    </div>
                    <input type="text" placeholder="Adress line 1" id="Customer-adress-1">
                    <input type="text" placeholder="Adress line 2" id="Customer-adress-2">
                    <input type="hidden" id="invoice-status" value="Unpaid">
                </div>
                <div class="modal-invoice-right">
                    <input type="date" id="current-date" disabled>
                    <input type="text" id="invoice-number" value="" disabled placeholder="Invoice Number">
                    <div id="payment-terms">
                        <select id="pay-in-days">
                            <option>Pay in Days</option>
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="45">45</option>
                        </select>
                    </div>
                    <input type="text" placeholder="Purchase Order #">
                    <input type="text" placeholder="Salesperson">
                </div>
            </div>

            <!-- Invoice Table Section -->
            <div class="modal-invoice-table-container">
                <table class="modal-invoice-table" id="invoice-table">
                    <thead>
                        <tr>
                            <th>Qty</th>
                            <th style="display:none;">Product</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th style="display:none;">In Stock</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-rows">
                        <tr>
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
                            <td><span class="total">0.00</span></td>
                            <td class="stock" style="display:none;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-dealership-vehicle-details" style="display:none;">
                <h3>Vehicle Details</h3>
                <table class="modal-dealership-invoice-table">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>VIN</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="search-vehicle-container" style="position: relative;">
                                    <input type="text" placeholder="Vehicle Model" id="dealership-vehicle-model" oninput="searchVehicle(this)">
                                    <div class="search-dropdown5" id="search-results-vehicle-model"></div>
                                </div>
                            </td>
                            <td>
                                <div class="search-vehicle-container" style="position: relative;">
                                    <input type="text" placeholder="VIN" id="dealership-vin" oninput="searchVehicle(this)">
                                    <div class="search-dropdown6" id="search-results-vin"></div>
                                </div>
                            </td>
                            <td><input type="text" placeholder="Description" id="dealership-vehicle-description"></td>
                            <td><input type="text" placeholder="R0.00" id="dealership-vehicle-price"></td>
                            <td>
                                <select id="dealership-vehicle-tax">
                                    <option value="0">[None]</option>
                                    <option value="15">15%</option>
                                    <option value="20">20%</option>
                                </select>
                            </td>
                            <td id="dealership-vehicle-total">R0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Extras and Parts Section -->
            <div class="modal-dealership-extras" style="display:none;">
                <h3>Extras/Parts</h3>
                <table class="modal-dealership-invoice-table" id="dealership-vehicle-table">
                    <thead>
                        <tr>
                            <th>Qty</th>
                            <th>Part Name</th>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th style="display:none;">In Stock</th>
                        </tr>
                    </thead>
                    <tbody id="dealership-invoice-rows">
                        <tr>
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
                        </tr>
                    </tbody>
                </table>
        </div>

        <!-- Bottom Section: Totals and Actions -->
        <div class="modal-invoice-bottom-section">
            <div class="modal-invoice-totals">
                <div class="modal-invoice-buttons">
                    <button class="modal-invoice-add-item" id="add-item" onclick="addItem()">+ Add Item</button>
                    <button class="modal-invoice-add-discount" id="add-discount" onclick="addDiscount()">+ Add Discount</button>
                </div>
                <div class="invoice-totals">
                    <p>Subtotal:    <span class="subtotal" id="subtotal">0.00</span></p>
                    <p>Tax:    <span class="tax-total" id="tax-total">0.00</span></p>
                    <p>Total:   <span class="final-total" id="final-total">0.00</span></p>
                </div>
            </div>

            <div class="modal-invoice-footer">
                <div class="modal-invoice-notes">
                    <textarea placeholder="Public Note"></textarea>
                    <textarea placeholder="Private Note"></textarea>
                    <textarea placeholder="Foot Note"></textarea>
                </div>
                <div class="modal-invoice-actions">
                    <button class="modal-invoice-clear" id="clear-invoice" onclick="clear()">Clear Invoice</button>
                    <button class="modal-invoice-preview" id="preview-invoice" onclick="collectInvoiceData()" action="submit">Preview Invoice</button>
                    <button class="modal-invoice-create" id="create-invoice" onclick="Create()">Create</button>
                </div>
            </div>
        </div>
    </div>
</div>