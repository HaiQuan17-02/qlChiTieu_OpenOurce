// main.js - JavaScript cho ứng dụng

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Format currency inputs
    const currencyInputs = document.querySelectorAll('input[type="number"][step="1000"]');
    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = Math.round(this.value / 1000) * 1000;
            }
        });
    });
    
    // Confirm before delete
    const deleteButtons = document.querySelectorAll('[onclick*="confirmDelete"]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const onclick = this.getAttribute('onclick');
            const match = onclick.match(/\d+/);
            if (match) {
                const id = match[0];
                if (!confirm('Bạn có chắc chắn muốn xóa?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
});

// Helper function to format numbers
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Helper function to show toast notifications
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

