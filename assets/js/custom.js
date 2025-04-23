/**
 * Ürün Takip Sistemi - Custom JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const main = document.querySelector('.main');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            main.classList.toggle('active');
        });
    }
    
    // DataTable Türkçe dil desteği
    if ($.fn.dataTable) {
        $('.datatable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json",
            },
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']]
        });
    }
    
    // Stock In/Out sayfalarında ürün seçildiğinde sayfa yenileme
    const productSelect = document.getElementById('urun_id');
    if (productSelect && window.location.href.includes('stock_')) {
        productSelect.addEventListener('change', function() {
            if (this.value) {
                window.location.href = window.location.pathname + '?id=' + this.value;
            }
        });
    }
    
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Confirmation dialog
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Bu işlemi gerçekleştirmek istediğinize emin misiniz?')) {
                e.preventDefault();
            }
        });
    });
    
    // Filter form in stock report
    const filterSelect = document.getElementById('filter');
    const categoryContainer = document.getElementById('categorySelectContainer');
    
    if (filterSelect && categoryContainer) {
        filterSelect.addEventListener('change', function() {
            if (this.value === 'category') {
                categoryContainer.style.display = 'block';
            } else {
                categoryContainer.style.display = 'none';
            }
        });
    }
});
