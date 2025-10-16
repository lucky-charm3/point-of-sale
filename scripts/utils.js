//general modal handling
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "block";
        modal.classList.remove('hidden');
    } else {
        console.error(`Modal with id ${id} not found`);
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "none";
        modal.classList.add('hidden');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
});

document.querySelectorAll(".modal .close").forEach(btn => {
    btn.addEventListener("click", function() {
        const modal = this.closest(".modal");
        if (modal) closeModal(modal.id);
    });
});

//toast notification
function showToast(message, type = "success") {
    const toast = document.getElementById("toast");
    if (!toast) {
        console.error('Toast element not found');
        return;
    }

    const toastMessage = document.getElementById("toastMessage");
    if (toastMessage) {
        toastMessage.innerText = message;
    }
    
    toast.className = `toast ${type}`;
    toast.style.display = "block";

    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}

//navigation and sidebar
function navigateToSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
        section.style.display = 'none';
    });
    
    // Show target section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
        targetSection.style.display = 'block';
    }
    
    // Update active menu item
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-target') === sectionId) {
            item.classList.add('active');
        }
    });

    // Special handling for logout
    if (sectionId === 'logout') {
        logout();
    }
}

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

//logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../auth/logout.php';
    }
}

//delete confirmation
let confirmDeleteAction = null;

function confirmDelete(id, name, deleteFunction) {
    confirmDeleteAction = () => {
        deleteFunction(id);
    };

    const deleteModalMessage = document.querySelector('#deleteModal .modal-message');
    if (deleteModalMessage) {
        deleteModalMessage.innerText = `Are you sure you want to delete "${name}"?`;
    }

    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.onclick = null;
        confirmBtn.onclick = confirmDeleteAction;
    }

    openModal('deleteModal');
}

//form handling utilities
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }
}

//date and format utilities
function formatCurrency(amount) {
    return `Tshs ${Number(amount).toLocaleString()}`;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString();
}

//DOM ready initilization
document.addEventListener('DOMContentLoaded', () => {
    // Initialize sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Initialize navigation
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            navigateToSection(target);
        });
    });

    

    // Initialize form validation on submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
                showToast('Please fill all required fields', 'error');
            }
        });
    });
    navigateToSection('dashboard');
});

// Handle sidebar on window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 576) {
        sidebar.classList.remove('open');
    }
});


window.openModal = openModal;
window.closeModal = closeModal;
window.showToast = showToast;
window.navigateToSection = navigateToSection;
window.toggleSidebar = toggleSidebar;
window.logout = logout;
window.confirmDelete = confirmDelete;
window.validateForm = validateForm;
window.clearForm = clearForm;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;