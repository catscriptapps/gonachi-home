// /resources/js/utils/sidebar-utils.js

/**
 * Sidebar toggle for mobile/tablet screens.
 * Shows the desktop sidebar below lg screens when toggled.
 */
export function initSidebarToggle() {
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.lg\\:flex'); // desktop sidebar element
  if (!toggleBtn || !sidebar) return;

  let isOpen = false;

  const openSidebar = () => {
    sidebar.classList.remove('hidden');
    sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', 'h-full', 'flex', 'flex-col'); 
    isOpen = true;
  };

  const closeSidebar = () => {
    sidebar.classList.add('hidden');
    sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-50', 'h-full', 'flex', 'flex-col'); 
    isOpen = false;
  };

  toggleBtn.addEventListener('click', () => {
    isOpen ? closeSidebar() : openSidebar();
  });

  // Close sidebar when clicking outside on mobile/tablet
  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && isOpen && window.innerWidth < 1024) {
      closeSidebar();
    }
  });

  // Close sidebar if clicking on nav link or auth button
  sidebar.addEventListener('click', (e) => {
    if (window.innerWidth >= 1024) return; // only for mobile/tablet

    const linkOrButton = e.target.closest('a[data-partial], a[data-login-button], a[data-logout-button]');
    if (linkOrButton) closeSidebar();
  });

  // Close sidebar automatically if resized to desktop
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024 && isOpen) closeSidebar();
  });
}