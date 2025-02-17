<!-- Modal Container -->
<div id="sales-rep-modal" class="sales-rep-modal hidden">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Sales Representative Details</h2>
      <button class="close-modal-btn" id="close-modal-btn">✕</button>
    </div>
    <div class="modal-body">
      <!-- Basic Information -->
      <div class="rep-info">
        <h3>Basic Information</h3>
        <form id="rep-form">
          <div class="form-group">
            <label for="rep-name">Name:</label>
            <input type="text" id="rep-name" name="rep-name" value="John Doe" />
          </div>
          <div class="form-group">
            <label for="rep-email">Email:</label>
            <input type="email" id="rep-email" name="rep-email" value="john.doe@example.com" />
          </div>
          <div class="form-group">
            <label for="rep-phone">Phone:</label>
            <input type="text" id="rep-phone" name="rep-phone" value="(123) 456-7890" />
          </div>
        </form>
      </div>

      <!-- Sales Performance -->
      <div class="sales-performance">
        <h3>Performance Overview</h3>
        <div class="performance-stats">
          <p><strong>Target:</strong> $50,000</p>
          <p><strong>Targets Reached:</strong> 8 out of 10</p>
          <p><strong>Commission Rate:</strong> 10%</p>
          <p><strong>Commission Earned:</strong> $5,000</p>
        </div>
      </div>

      <!-- Recent Deals -->
      <div class="recent-deals">
        <h3>Recent Deals</h3>
        <ul>
          <li><strong>Deal 1:</strong> $10,000 (Closed on Jan 1, 2025)</li>
          <li><strong>Deal 2:</strong> $5,000 (Closed on Jan 15, 2025)</li>
          <li><strong>Deal 3:</strong> $15,000 (Closed on Jan 20, 2025)</li>
        </ul>
      </div>
    </div>
    <div class="modal-footer">
      <button class="save-btn">Save Changes</button>
    </div>
  </div>
</div>
