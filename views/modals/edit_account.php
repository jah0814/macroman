<?php $isAdmin = isset($_SESSION['position']) && strtoupper(trim($_SESSION['position'])) === 'ADMIN'; ?>

<div id="editModal" class="modal-overlay" style="display: none;">
    <div class="modal-content edit-blue-card">
        <div class="modal-header">
            <h3>EDIT ACCOUNT</h3>
            <hr>
        </div>

        <form action="index.php?action=update_account" method="POST">
            <div class="modal-body">
                <div class="input-group">
                    <label>USERNAME</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" class="modal-input" required>
                </div>
                 <div class="input-group">
                    <label>FULL NAME</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" class="modal-input" required>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="input-group">
                        <label>POSITION</label>
                        <select name="position" class="modal-input" required>
                            <?php $currentPos = strtoupper(trim($user['position'] ?? '')); ?>
                            <option value="STAFF" <?= $currentPos === 'STAFF' ? 'selected' : '' ?>>STAFF</option>
                            <option value="ADMIN" <?= $currentPos === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <label>CURRENT PASSWORD</label>
                    <div class="modal-password-wrap">
                        <input type="password" name="current_password" id="currentPassword" placeholder="Current Password" required>
                        <i class="fa-regular fa-eye toggle-password" data-target="currentPassword"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label>NEW PASSWORD</label>
                    <div class="modal-password-wrap">
                        <input type="password" name="new_password" id="newPassword" placeholder="New Password (Optional)">
                        <i class="fa-regular fa-eye toggle-password" data-target="newPassword"></i>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editModal')">CANCEL</button>
                <button type="submit" class="btn-modal-save">SAVE</button>
            </div>
        </form>
    </div>
</div>