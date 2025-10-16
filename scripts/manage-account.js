function updateProfile() {
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;

    fetch(`../apis/users-api.php?action=updateProfile`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, phone: phone })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showToast('Profile updated successfully!', 'success');
        } else {
            showToast('Error updating profile.', 'error');
        }
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function changePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        showToast('New passwords do not match.', 'error');
        return;
    }

    if (newPassword.length < 6) { 
        showToast('Password must be at least 6 characters.', 'error');
        return;
    }

    fetch(`../apis/users-api.php?action=changePassword`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_password: currentPassword, new_password: newPassword })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showToast('Password updated successfully!', 'success');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            showToast(result.message || 'Error changing password.', 'error');
        }
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function lockAccount() {
    if (!confirm("Are you sure you want to lock your account? You will be logged out.")) {
        return;
    }

    fetch(`../apis/users-api.php?action=lockAccount`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showToast('Account locked successfully. Logging out...', 'success');
            setTimeout(() => {
                window.location.href = '../auth/logout.php';
            }, 1500);
        } else {
            showToast('Error locking account.', 'error');
        }
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}