<div id="vehicleDetailsModal" class="modal vehicle-modal hidden">
    <div class="modal-content">
    <h2>Vehicle Details</h2>
        <span class="close-btn" onclick="closeModal()">&times;</span>
        
        <!-- Images Section - Positioned at the top -->
        <div class="car-images">
                <div class="vehicle-details">
                    <div class="vehicle-details-container" >
                    <h3>BMW M4 Softtop</h3>
                    <p>6-cylinder,Twin Turbo, inline, petrol engine</p>
                    <p>390 kW (530 hp)</p>
                    <p>650 Nm of torque</p>
                    </div>
                </div>
            <img id="mainImage" class="image-large" src="../img/BMW1.avif" alt="Car image" />
            <div class="image-thumbnails">
                <img src="../img/BMW1.avif" alt="Thumbnail 1" onclick="changeImage('../img/BMW1.avif')" />
                <img src="../img/BMW2.avif" alt="Thumbnail 2" onclick="changeImage('../img/BMW2.avif')" />
                <img src="../img/BMW3.avif" alt="Thumbnail 3" onclick="changeImage('../img/BMW3.avif')" />
                <img src="../img/BMW4.avif" alt="Thumbnail 4" onclick="changeImage('../img/BMW4.avif')" />
                <img src="../img/BMW5.avif" alt="Thumbnail 5" onclick="changeImage('../img/BMW5.avif')" />
            </div>
        </div>

        <!-- Tabs -->
        <div class="vehicle-sections">
            <ul>
                <li class="section-link section-link-specifications active" onclick="showSection('vehicle-specifications')">Specifications</li>
                <li class="section-link section-link-service-history" onclick="showSection('vehicle-service-history')">Service History</li>
                <li class="section-link section-link-mileage" onclick="showSection('vehicle-mileage')">Mileage</li>
                <li class="section-link section-link-maintenance" onclick="showSection('vehicle-maintenance')">Maintenance</li>
            </ul>
        </div>

        <!-- Section Content -->
        <div id="vehicle-specifications" class="section-content vehicle-section">
            <h3>Specifications</h3>
            <div class="detail-box">
                <p><strong>Car Model:</strong> XYZ</p>
                <p><strong>Engine:</strong> V8</p>
                <p><strong>Color:</strong> Red</p>
                <p><strong>Year:</strong> 2020</p>
                <p><strong>Transmission:</strong> Automatic</p>
                <p><strong>Fuel Type:</strong> Petrol</p>
            </div>
        </div>

        <div id="vehicle-service-history" class="section-content vehicle-section hidden">
            <h3>Service History</h3>
            <div class="detail-box">
                <p><strong>Last Service:</strong> 12/10/2024</p>
                <p><strong>Next Service:</strong> 12/10/2025</p>
                <p><strong>Service Center:</strong> ABC Auto Repair</p>
                <p><strong>Services Performed:</strong> Oil Change, Brake Fluid Check, Tire Rotation</p>
                <p><strong>Notes:</strong> No issues reported, vehicle running smoothly.</p>
            </div>
        </div>

        <div id="vehicle-mileage" class="section-content vehicle-section hidden">
            <h3>Mileage</h3>
            <div class="detail-box">
                <p><strong>Current Mileage:</strong> 45,000 miles</p>
                <p><strong>Last Recorded Mileage:</strong> 40,000 miles</p>
                <p><strong>Average Annual Mileage:</strong> 5,000 miles</p>
                <p><strong>Next Mileage Service:</strong> 50,000 miles</p>
            </div>
        </div>

        <div id="vehicle-maintenance" class="section-content vehicle-section hidden">
            <h3>Maintenance</h3>
            <div class="detail-box">
                <p><strong>Next Maintenance:</strong> 01/12/2025</p>
                <p><strong>Planned Maintenance:</strong> Timing Belt Change, Tire Replacement</p>
                <p><strong>Last Maintenance:</strong> 12/12/2024</p>
                <p><strong>Next Oil Change:</strong> 05/12/2025</p>
            </div>
        </div>
    </div>
</div>
