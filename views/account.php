<?php include 'views/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Account Settings - Macro Access</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }
        .toast-success {
            background: #27ae60;
            border-left: 5px solid #1e8449;
        }
        .toast-error {
            background: #e74c3c;
            border-left: 5px solid #c0392b;
        }
        .toast-info {
            background: #3498db;
            border-left: 5px solid #2471a3;
        }
        .toast-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .toast-close:hover {
            opacity: 1;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
        .request-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-delete-request {
            background: #dc2626;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
        }
        .btn-delete-request:hover {
            background: #b91c1c;
        }
        .btn-reset-request {
            background: #f39c12;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
        }
        .btn-reset-request:hover {
            background: #e67e22;
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="nav-logo">
            <a href="index.php?action=dashboard" style="text-decoration: none; color: inherit;">🔬 MACRO ACCESS</a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php?action=account" class="active" style="text-decoration: underline !important;">ACCOUNT</a></li>
            <li><a href="index.php?action=reports">REPORTS</a></li>
            <li><a href="index.php?action=analytics">ANALYTICS</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="index.php?action=records">RECORDS</a></li>
            <?php endif; ?>
            <li><a href="index.php?action=logout">LOG OUT</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <?php if (isAdmin()): ?>
            <!-- ADMIN VIEW: Two Column Layout -->
            <h2 class="page-title">ACCOUNT MANAGEMENT</h2>
            
            <div style="display: flex; gap: 30px; max-width: 1200px; margin: 0 auto;">
                <!-- LEFT COLUMN - Admin Info & Reset Requests -->
                <div style="flex: 0.4;">
                    <!-- Admin Info Card -->
                    <div class="white-card" style="padding: 25px; margin-bottom: 30px;">
                        <h3 style="color: #333; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">My Account</h3>
                        <div class="input-block" style="margin-bottom: 15px;">
                            <label style="font-size: 11px;">USERNAME</label>
                            <input type="text" class="thick-border-input" 
                                   value="<?= htmlspecialchars($user['username'] ?? $_SESSION['username'] ?? 'USER') ?>" 
                                   style="padding: 10px;" readonly>
                        </div>
                        <div class="input-block" style="margin-bottom: 15px;">
                            <label style="font-size: 11px;">FULL NAME</label>
                            <input type="text" class="thick-border-input" 
                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" 
                                   style="padding: 10px;" readonly>
                        </div>
                        <div class="input-block" style="margin-bottom: 15px;">
                            <label style="font-size: 11px;">POSITION</label>
                            <input type="text" class="thick-border-input" 
                                   value="<?= htmlspecialchars($_SESSION['position'] ?? 'ADMIN') ?>" 
                                   style="padding: 10px;" readonly>
                        </div>
                    </div>

                    <!-- Password Reset Requests -->
                    <?php 
                    $stmt = $db->prepare("SELECT * FROM users WHERE reset_token IS NOT NULL AND reset_approved = 0 AND position != 'ADMIN' ORDER BY reset_requested DESC");
                    $stmt->execute();
                    $resetRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="white-card" style="padding: 25px;">
                        <h3 style="color: #333; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            Password Reset Requests 
                            <?php if(count($resetRequests) > 0): ?>
                                <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 8px;"><?= count($resetRequests) ?></span>
                            <?php endif; ?>
                        </h3>
                        <?php if (empty($resetRequests)): ?>
                            <p style="color: #666; text-align: center; padding: 20px;">No pending password reset requests.</p>
                        <?php else: ?>
                            <?php foreach ($resetRequests as $request): ?>
                            <div style="background: #f8f9fa; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?= htmlspecialchars($request['username']) ?></strong><br>
                                    <span style="font-size: 11px; color: #666;"><?= htmlspecialchars($request['full_name'] ?? 'N/A') ?></span><br>
                                    <span style="font-size: 10px; color: #999;">Requested: <?= date('M d, Y h:i A', strtotime($request['reset_requested'])) ?></span>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button onclick="openResetModal(<?= $request['id'] ?>, '<?= htmlspecialchars($request['username']) ?>')" style="background: #f39c12; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                        Reset Password
                                    </button>
                                    <button onclick="deleteResetRequest(<?= $request['id'] ?>, '<?= htmlspecialchars($request['username']) ?>')" style="background: #dc2626; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                        Delete Request
                                    </button>
                                </div>
                            </div>  
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT COLUMN - Staff List -->
                <div style="flex: 0.6;">
                    <div class="white-card" style="padding: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            <h3 style="color: #333; margin: 0;">Staff Members</h3>
                            <button type="button" class="btn-add-staff" onclick="openModal('addUserModal')" style="background: #3498db; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                                ➕ ADD STAFF
                            </button>
                        </div>
                        
                        <?php 
                        $stmt = $db->prepare("SELECT id, username, full_name, position FROM users WHERE position != 'ADMIN' ORDER BY username ASC");
                        $stmt->execute();
                        $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if (empty($staffList)): ?>
                            <p style="color: #666; text-align: center; padding: 40px;">No staff members found.</p>
                        <?php else: ?>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #2c3e50;">
                                        <th style="padding: 10px; text-align: left; color: white; font-size: 12px;">Username</th>
                                        <th style="padding: 10px; text-align: left; color: white; font-size: 12px;">Full Name</th>
                                        <th style="padding: 10px; text-align: left; color: white; font-size: 12px;">Position</th>
                                        <th style="padding: 10px; text-align: center; color: white; font-size: 12px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staffList as $staff): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 10px; font-size: 12px;"><?= htmlspecialchars($staff['username']) ?>
                                        <td style="padding: 10px; font-size: 12px;"><?= htmlspecialchars($staff['full_name'] ?? 'N/A') ?>
                                        <td style="padding: 10px; font-size: 12px;"><?= htmlspecialchars($staff['position']) ?>
                                        <td style="padding: 10px; text-align: center;">
                                            <button onclick="openEditStaffModal(<?= $staff['id'] ?>, '<?= htmlspecialchars($staff['username']) ?>', '<?= htmlspecialchars($staff['full_name'] ?? '') ?>')" style="background: #3498db; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px; font-size: 11px;">
                                                Edit
                                            </button>
                                            <button onclick="deleteStaff(<?= $staff['id'] ?>, '<?= htmlspecialchars($staff['username']) ?>')" style="background: #dc2626; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                                Delete
                                            </button>
                                        
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Staff Modal -->
            <div id="editStaffModal" class="modal-overlay" style="display: none;">
                <div class="modal-content edit-blue-card">
                    <div class="modal-header">
                        <h3>EDIT STAFF</h3>
                        <hr>
                    </div>
                    <form action="index.php?action=edit_staff" method="POST">
                        <input type="hidden" name="staff_id" id="edit_staff_id">
                        <div class="modal-body">
                            <div class="input-group">
                                <label>USERNAME</label>
                                <input type="text" name="username" id="edit_staff_username" class="modal-input" required>
                            </div>
                            <div class="input-group">
                                <label>FULL NAME</label>
                                <input type="text" name="full_name" id="edit_staff_fullname" class="modal-input" required>
                            </div>
                            <div class="input-group">
                                <label>POSITION</label>
                                <select name="position" id="edit_staff_position" class="modal-input">
                                    <option value="STAFF">STAFF</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-modal-cancel" onclick="closeModal('editStaffModal')">CANCEL</button>
                            <button type="submit" class="btn-modal-save">SAVE CHANGES</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reset Password Modal -->
            <div id="resetPasswordModal" class="modal-overlay" style="display: none;">
                <div class="modal-content edit-blue-card">
                    <div class="modal-header">
                        <h3>RESET STAFF PASSWORD</h3>
                        <hr>
                    </div>
                    <form action="index.php?action=admin_reset_password" method="POST">
                        <input type="hidden" name="user_id" id="reset_user_id">
                        <div class="modal-body">
                            <div class="input-group">
                                <label>USERNAME</label>
                                <input type="text" id="reset_username" class="modal-input" readonly>
                            </div>
                            <div class="input-group">
                                <label>NEW PASSWORD</label>
                                <div class="modal-password-wrap">
                                    <input type="password" name="new_password" id="resetPassword" class="modal-input" required>
                                    <i class="fa-regular fa-eye toggle-password" data-target="resetPassword"></i>
                                </div>
                            </div>
                            <div class="input-group">
                                <label>CONFIRM PASSWORD</label>
                                <div class="modal-password-wrap">
                                    <input type="password" name="confirm_password" id="confirmPassword" class="modal-input" required>
                                    <i class="fa-regular fa-eye toggle-password" data-target="confirmPassword"></i>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-modal-cancel" onclick="closeModal('resetPasswordModal')">CANCEL</button>
                            <button type="submit" class="btn-modal-save">RESET PASSWORD</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            function openEditStaffModal(id, username, fullname) {
                document.getElementById('edit_staff_id').value = id;
                document.getElementById('edit_staff_username').value = username;
                document.getElementById('edit_staff_fullname').value = fullname;
                document.getElementById('editStaffModal').style.display = 'flex';
            }
            
            function deleteStaff(id, username) {
                if(confirm(`Are you sure you want to delete staff "${username}"? This action cannot be undone.`)) {
                    window.location.href = "index.php?action=delete_staff&id=" + id;
                }
            }
            
            function openResetModal(userId, username) {
                document.getElementById('reset_user_id').value = userId;
                document.getElementById('reset_username').value = username;
                document.getElementById('resetPasswordModal').style.display = 'flex';
            }
            
            function deleteResetRequest(userId, username) {
                if(confirm(`Are you sure you want to delete the password reset request for "${username}"?`)) {
                    window.location.href = "index.php?action=delete_reset_request&id=" + userId;
                }
            }
            </script>

        <?php else: ?>
            <!-- NON-ADMIN VIEW (STAFF) -->
            <h2 class="page-title">ACCOUNT</h2>
            <div class="white-card" style="max-width: 500px; margin: 0 auto; padding: 30px;">
                <div class="input-block">
                    <label>USERNAME</label>
                    <input type="text" class="thick-border-input" 
                           value="<?= htmlspecialchars($user['username'] ?? $_SESSION['username'] ?? 'USER') ?>" readonly>
                </div>
                <div class="input-block">
                    <label>FULL NAME</label>
                    <input type="text" class="thick-border-input" 
                           value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" readonly>
                </div>
                <div class="input-block">
                    <label>POSITION</label>
                    <input type="text" class="thick-border-input" 
                           value="<?= htmlspecialchars($_SESSION['position'] ?? 'STAFF') ?>" readonly>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'views/modals/edit_account.php'; ?>
    <?php include 'views/modals/add_user.php'; ?>

    <script>
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `<span>${message}</span><button class="toast-close" onclick="this.parentElement.remove()">✕</button>`;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                if (toast && toast.remove) toast.remove();
            }, 300);
        }, 3000);
    }

    // Check for messages from PHP session - FILTER OUT record-related messages
    <?php if(isset($_SESSION['toast_success'])): ?>
        <?php if(strpos($_SESSION['toast_success'], 'Record') === false && strpos($_SESSION['toast_success'], 'record') === false && strpos($_SESSION['toast_success'], 'archive') === false): ?>
            showToast("<?= addslashes($_SESSION['toast_success']) ?>", 'success');
        <?php endif; ?>
        <?php unset($_SESSION['toast_success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['toast_error'])): ?>
        showToast("<?= addslashes($_SESSION['toast_error']) ?>", 'error');
        <?php unset($_SESSION['toast_error']); ?>
    <?php endif; ?>

    <?php if(isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
        showToast("Account updated successfully!", 'success');
    <?php endif; ?>

    function openModal(id) { 
        const target = document.getElementById(id);
        if(target) target.style.display = 'flex'; 
    }
    
    function closeModal(id) { 
        const target = document.getElementById(id);
        if(target) target.style.display = 'none'; 
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
    </script>

    <?php include 'views/footer.php'; ?>
</body>
</html>