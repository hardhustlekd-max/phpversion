/**
 * Centralized AJAX & API Handler
 * Handles JSON payload serialization, multipart upload, toasts, and errors
 */

const AppAPI = (() => {
  function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `p-3 rounded-lg shadow-lg border text-sm font-medium flex items-center gap-2 pointer-events-auto transition-all duration-300 transform translate-y-2 opacity-0 ${
      type === 'success'
        ? 'bg-emerald-50 text-emerald-900 border-emerald-200'
        : type === 'error'
        ? 'bg-rose-50 text-rose-900 border-rose-200'
        : 'bg-slate-900 text-white border-slate-800'
    }`;

    const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
    toast.innerHTML = `
      <span class="material-symbols-outlined text-[18px] shrink-0">${icon}</span>
      <span class="flex-1 text-xs leading-snug">${message}</span>
      <button type="button" class="text-xs opacity-60 hover:opacity-100 cursor-pointer p-0.5 ml-1" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => {
      toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
      toast.classList.add('opacity-0', 'translate-y-2');
      setTimeout(() => toast.remove(), 300);
    }, 4500);
  }

  async function request(endpoint, options = {}) {
    const url = endpoint.startsWith('http') || endpoint.startsWith('ajax/') || endpoint.startsWith('/') ? endpoint : `ajax/${endpoint}`;
    try {
      const res = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          ...(options.headers || {})
        },
        ...options
      });

      const data = await res.json();
      if (!res.ok || data.success === false) {
        throw new Error(data.error || `HTTP error ${res.status}`);
      }
      return data;
    } catch (err) {
      console.error(`API Error [${endpoint}]:`, err);
      showToast(err.message, 'error');
      throw err;
    }
  }

  return {
    showToast,
    get: (endpoint, params = {}) => {
      const query = new URLSearchParams(params).toString();
      const url = query ? `${endpoint}?${query}` : endpoint;
      return request(url, { method: 'GET' });
    },
    post: (endpoint, body = {}) => {
      return request(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
    },
    upload: async (fileOrFormData, fieldName = 'file') => {
      let fd;
      if (fileOrFormData instanceof FormData) {
        fd = fileOrFormData;
      } else {
        fd = new FormData();
        fd.append(fieldName, fileOrFormData);
      }
      return request('ajax/upload.php', {
        method: 'POST',
        body: fd
      });
    }
  };
})();

// Global convenience
window.showToast = AppAPI.showToast;
