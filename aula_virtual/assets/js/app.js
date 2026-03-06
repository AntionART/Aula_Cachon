const App = {
    toastContainer: null,

    init() {
        this.toastContainer = document.getElementById('toast-container');
        this.bindSearch();
        this.bindModals();
    },

    showToast(message, type = 'success') {
        if (!this.toastContainer) return;
        const icons = { success: '✓', error: '✕', info: 'ℹ', warn: '⚠' };
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span style="font-size:16px">${icons[type] || icons.info}</span><span>${message}</span>`;
        this.toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.25s ease forwards';
            setTimeout(() => toast.remove(), 260);
        }, 3200);
    },

    bindSearch() {
        document.querySelectorAll('[data-search-target]').forEach(input => {
            input.addEventListener('input', e => {
                const target = document.querySelector(e.target.dataset.searchTarget);
                if (!target) return;
                const q = e.target.value.toLowerCase();
                target.querySelectorAll('tbody tr').forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        });
    },

    bindModals() {
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', () => {
                const overlay = btn.closest('.modal-overlay');
                if (overlay) this.closeModal(overlay.id);
            });
        });
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) this.closeModal(overlay.id);
            });
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => this.closeModal(m.id));
            }
        });
    },

    openModal(id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.add('active'); modal.querySelector('input, textarea, select')?.focus(); }
    },

    closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('active');
    },

    confirmDelete(message, callback) {
        if (confirm(message || '¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.')) {
            callback();
        }
    },

    submitForm(formId, url, callback) {
        const form = document.getElementById(formId);
        if (!form) return;
        const data = new FormData(form);
        fetch(url, { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                this.showToast(res.message, res.success ? 'success' : 'error');
                if (res.success && callback) callback(res);
            })
            .catch(() => this.showToast('Error de conexión.', 'error'));
    },

    fetchAndRender(url, callback) {
        fetch(url)
            .then(r => r.json())
            .then(data => callback(data))
            .catch(() => this.showToast('Error al cargar datos.', 'error'));
    },

    getDueBadge(dueDate) {
        const now = new Date();
        const due = new Date(dueDate);
        const diff = (due - now) / 3600000;
        if (diff < 0) return '<span class="due-badge due-overdue">Vencida</span>';
        if (diff < 48) return '<span class="due-badge due-soon">Pronto</span>';
        return '<span class="due-badge due-ok">Activa</span>';
    },

    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
    },

    formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleString('es-CO', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    },

    initials(name) {
        return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());
