// resources/js/ui/toast.js

/**
 * Simple toast utility (auto-removes after 3s)
 */
export function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `
    fixed bottom-4 left-1/2 transform -translate-x-1/2
    px-4 py-2 rounded shadow-lg z-[10050]
    text-white text-sm font-medium
    ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}
  `;
  toast.textContent = message;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('opacity-0');
    setTimeout(() => toast.remove(), 500);
  }, 3000);
}
