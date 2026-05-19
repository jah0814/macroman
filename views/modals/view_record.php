<div id="viewRecordModal" class="modal-overlay" style="display: none;">
    <div class="modal-content view-record-card">
        <div class="view-header-row">
            <div class="profile-preview-box">
                <img src="assets/img/placeholder-user.png" alt="Profile" id="view-profile-img">
            </div>
            
            <div class="client-info-summary">
                <h2 id="view-client-name">MANGUSIN, NORMAN</h2>
                <div class="info-grid-mini">
                    <span>AGE: <strong id="view-age">45</strong></span>
                    <span>BIRTH DATE: <strong id="view-dob">2026-02-05</strong></span>
                    <span>SEX: <strong id="view-sex">M</strong></span>
                </div>
            </div>
        </div>  

        <div class="company-section">
            <label>COMPANY NAME</label>
            <h3 id="view-company">TERAVERA CORP.</h3>
        </div>

        <table class="modal-results-table">
            <thead>
                <tr>
                    <th>DRUG TYPE</th>
                    <th>DATE TESTED</th>
                    <th>RESULT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>METHAMPHETAMINE</td>
                    <td id="view-date-meth">2026-02-25</td>
                    <td id="view-result-meth" class="status-pos">POSITIVE</td>
                </tr>
                <tr>
                    <td>TETRAHYDROCANNABINOL</td>
                    <td id="view-date-thc">2026-02-25</td>
                    <td id="view-result-thc" class="status-neg">NEGATIVE</td>
                </tr>
            </tbody>
        </table>

        <div class="modal-footer-actions">
            <button type="button" class="btn-modal-blue-light" onclick="closeModal('viewRecordModal')">BACK</button>
        </div>
    </div>
</div>