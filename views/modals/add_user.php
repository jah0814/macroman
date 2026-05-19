<div id="addUserModal" class="modal-overlay" style="display: none;">
    <div class="modal-content edit-blue-card">
        <div class="modal-header">
            <h3>ADD NEW USER</h3>
            <hr>
        </div>
        <form action="index.php?action=add_user" method="POST">
            <div class="modal-body">
                <div class="input-group">
                    <label>NEW USERNAME</label>
                    <input type="text" name="new_username" class="modal-input" placeholder="Enter username" required>
                </div>
                <div class="input-group">
                    <label>FULL NAME</label>
                    <input type="text" name="full_name" class="modal-input" placeholder="Enter full name" required>
                </div>

                <div class="input-group">
                    <label>POSITION</label>
                    <select name="new_position" class="modal-input">
                        <option value="STAFF">STAFF</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>PASSWORD</label>
                    <div style="position: relative; width: 100%;">
                        <input type="password" name="password" id="addUserPassword" required style="width: 100%; padding: 10px 44px 10px 10px; border: 1px solid #ddd; border-radius: 6px; background: #fff; box-sizing: border-box;">
                        <i class="fa-regular fa-eye" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; font-size: 16px; z-index: 2;" onclick="togglePassword('addUserPassword', this)"></i>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('addUserModal')">CANCEL</button>
                <button type="submit" class="btn-modal-save">CREATE</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}
</script>