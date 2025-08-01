<!-- Modal Document -->
<div class="modal-document-overlay" id="document-modal" data-mode="create" style="display:none;">
    <div class="modal-document">
        <div class="modal-document-header">
            <h2 class="modal-document-title">New Document</h2>
            <span class="modal-document-close" id="modal-document-close-btn">&times;</span>
        </div>
        <!-- Top Section -->
        <div class="modal-document-top-section">
            <div class="modal-document-form">
                <div class="modal-document-left">
                    <select id="document-type">
                        <option value="quotation">Quotation</option>
                        <option value="vehicle-quotation">Vehicle Quotation</option>
                        <option value="standard-invoice">Invoice</option>
                        <option value="vehicle-invoice">Vehicle Invoice</option>
                        <option value="recurring-invoice">Recurring Invoice</option>
                        <option value="credit-note">Credit Note</option>
                        <option value="refund">Refund</option>
                        <option value="pro-forma">Pro Forma</option>
                    </select>
                    <div class="search-client-container" style="position: relative;">
                        <input type="text" placeholder="Client Name" id="client-name" oninput="searchClient(this)" autocomplete="off">
                        <input type="hidden" id="client-id">
                        <input type="hidden" id="issuer-id">
                        <div class="search-client-dropdown" id="search-results-client"></div>
                    </div>
                    <input type="email" placeholder="Client Email" id="client-email" autocomplete="off">
                    <input type="text" placeholder="Client Phone" id="client-phone" autocomplete="off">
                    <input type="text" placeholder="Client VAT Number" id="client-vat-number" autocomplete="off">
                    <input type="text" placeholder="Client Registration Number" id="client-reg-number" autocomplete="off">
                    <input type="text" placeholder="Address Line 1" id="client-address-1">
                    <input type="text" placeholder="Address Line 2" id="client-address-2">
                    <input type="hidden" id="document-status" value="Unpaid">
                    <input type="hidden" id="document-id">
                </div>
                <div class="modal-document-right">
                    <input type="date" id="current-date" disabled>
                    <input type="text" id="document-number" value="" disabled placeholder="Document Number">
                    <div class="payment-terms" data-show-on="standard-invoice,recurring-invoice">
                        <label for="pay-in-days">Pay in Days</label>
                        <select id="pay-in-days">
                            <option value="15">15</option>
                            <option value="30" selected>30</option>
                            <option value="45">45</option>
                        </select>
                    </div>
                    <input type="text" placeholder="Purchase Order #" id="purchase-order-number">
                    <div class="search-salesperson-container" style="position: relative;">
                        <input type="text" placeholder="Search Salesperson" id="salesperson" autocomplete="off">
                        <div class="search-salesperson-dropdown" id="search-results-salesperson"></div>
                    </div>
                    <!-- Issuer Info -->
                    <div class="issuer-info-section" id="issuer-info-section">
                        <strong>Issuer:</strong> <span id="issuer-name-display"></span><br>
                        <span id="issuer-address-display"></span><br>
                        <span id="issuer-email-display"></span><br>
                        <span id="issuer-phone-display"></span><br>
                        <span id="issuer-vat-display"></span><br>
                        <span id="issuer-reg-display"></span>
                    </div>
                    <!-- Recurring -->
                    <div class="recurring-section" data-show-on="recurring-invoice">
                        <h4>Recurring Details</h4>
                        <label for="recurring-frequency">Frequency</label>
                        <select id="recurring-frequency">
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="bi-weekly">Bi-Weekly</option>
                        </select>
                        <label for="recurring-start-date">Start Date</label>
                        <input type="date" id="recurring-start-date">
                        <label for="recurring-end-date">End Date</label>
                        <input type="date" id="recurring-end-date">
                    </div>
                </div>
            </div>
            <!-- Document Table -->
            <div class="modal-document-table-container">
                <table class="modal-document-table" id="document-table">
                    <thead>
                        <tr>
                            <th>Qty</th>
                            <th style="display:none;">Product</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Tax</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th style="display:none;">Stock</th>
                        </tr>
                    </thead>
                    <tbody id="document-rows">
                        <tr class="document-item-row">
                            <td><input type="number" value="1" class="quantity"></td>
                            <td style="display:none;"><input type="hidden" class="product-id"></td>
                            <td>
                                <div class="search-container" style="position: relative;">
                                    <input type="text" placeholder="Search Item Code" class="item-code" autocomplete="off">
                                    <div class="search-dropdown1"></div>
                                </div>
                            </td>
                            <td>
                                <div class="search-container" style="position: relative;">
                                    <input type="text" placeholder="Search Description" class="description" autocomplete="off">
                                    <div class="search-dropdown2"></div>
                                </div>
                            </td>
                            <td><input type="text" value="R0.00" class="unit-price"></td>
                            <td>
                                <select class="tax">
                                    <option value="0">[None]</option>
                                    <option value="10">10%</option>
                                    <option value="15">15%</option>
                                    <option value="20">20%</option>
                                    <option value="25">25%</option>
                                </select>
                            </td>
                            <td></td>
                            <td class="total-cell">
                                <span class="total">0.00</span>
                                <button type="button" class="remove-row-btn" title="Remove Line" style="display:none;">&#10006;</button>
                            </td>
                            <td class="stock" style="display:none;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Vehicle Section -->
            <div class="vehicle-section" data-show-on="vehicle-invoice,vehicle-quotation">
                <h3>Vehicle Details</h3>
                <table class="modal-vehicle-table">
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
                                <input type="text" placeholder="Vehicle Model" id="vehicle-model" oninput="searchVehicle(this)">
                                <div class="search-dropdown5" id="search-results-vehicle-model"></div>
                            </td>
                            <td>
                                <input type="text" placeholder="VIN" id="vehicle-vin" oninput="searchVehicle(this)">
                                <div class="search-dropdown6" id="search-results-vin"></div>
                            </td>
                            <td><input type="text" placeholder="Description" id="vehicle-description"></td>
                            <td><input type="text" placeholder="R0.00" id="vehicle-price"></td>
                            <td>
                                <select id="vehicle-tax">
                                    <option value="0">[None]</option>
                                    <option value="15">15%</option>
                                    <option value="20">20%</option>
                                </select>
                            </td>
                            <td id="vehicle-total">R0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Vehicle Parts Section -->
            <div class="vehicle-extras-section" data-show-on="vehicle-invoice,vehicle-quotation">
                <h3>Extras/Parts</h3>
                <table class="modal-extras-table" id="vehicle-parts-table">
                    <thead>
                        <tr>
                            <th>Qty</th>
                            <th>Part Name</th>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th style="display:none;">Stock</th>
                        </tr>
                    </thead>
                    <tbody id="vehicle-parts-rows">
                        <tr>
                            <td><input type="number" value="1" class="extras-quantity"></td>
                            <td>
                                <input type="text" placeholder="Part Name" class="extras-name" oninput="searchParts(this)">
                                <div class="search-dropdown3" id="search-results-part-name"></div>
                            </td>
                            <td>
                                <input type="text" placeholder="Description" class="extras-description" oninput="searchParts(this)">
                                <div class="search-dropdown4" id="search-results-description"></div>
                            </td>
                            <td><input type="text" value="R0.00" class="extras-unit-price"></td>
                            <td>
                                <select class="extras-tax">
                                    <option value="0">[None]</option>
                                    <option value="15">15%</option>
                                    <option value="20">20%</option>
                                </select>
                            </td>
                            <td class="extras-total">R0.00</td>
                            <td class="extras-stock" style="display:none;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Bottom Section -->
        <div class="modal-document-bottom-section">
            <div class="modal-document-totals">
                <div class="modal-document-buttons">
                    <button type="button" id="add-item-btn">+ Add Item</button>
                    <button type="button" id="add-discount-btn">+ Add Discount</button>
                </div>
                <div class="document-totals">
                    <p>Subtotal: <span id="subtotal">0.00</span></p>
                    <p>Tax: <span id="tax-total">0.00</span></p>
                    <p>Total: <span id="final-total">0.00</span></p>
                </div>
            </div>
            <div class="modal-document-footer">
                <div class="modal-document-notes">
                    <textarea placeholder="Public Note" id="public-note"></textarea>
                    <textarea placeholder="Private Note" id="private-note"></textarea>
                    <textarea placeholder="Foot Note" id="foot-note"></textarea>
                </div>
                <div class="modal-document-actions">
                    <button type="button" onclick="clearDocument()">Clear</button>
                    <button type="button" id="preview-pdf-btn">Preview</button>
                    <button type="button" onclick="createOrUpdateDocument()">Create</button>
                    <button type="button" id="save-document">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- example item row 
<tr class="document-item-row">
    <td><input type="number" value="1" class="quantity"></td>
    <td style="display:none;"><input type="hidden" class="product-id"></td>
    <td>
        <div class="search-container" style="position: relative;">
            <input type="text" placeholder="Search Item Code" class="item-code" autocomplete="off">
            <div class="search-dropdown1"></div>
        </div>
    </td>
    <td>
        <div class="search-container" style="position: relative;">
            <input type="text" placeholder="Search Description" class="description" autocomplete="off">
            <div class="search-dropdown2"></div>
        </div>

         Inline Discount Toggle
        <div class="discount-toggle" onclick="toggleLineDiscount(this)" title="Add Discount 🤑" style="cursor:pointer;font-size:0.85em;color:#007bff;">+ Discount</div>
        <div class="line-discount-input" style="display:none;margin-top:4px;">
            <input type="text" class="line-discount" placeholder="10% or R50">
        </div>
    </td>
    <td><input type="text" value="R0.00" class="unit-price"></td>
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
-->
