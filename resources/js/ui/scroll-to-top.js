/**
 * /resources/js/ui/scroll-to-top.js
 * Initializes and manages the scroll-to-top button functionality.
 */

export function setupScrollToTop() {
    const scrollBtn = document.getElementById('scroll-top');
    const chatWidget = document.getElementById('chat-widget'); // floating chat bubble, see components/chat-widget.php
    const mainContent = document.getElementById('main-content'); // Assuming this is the main scrollable area

    if (scrollBtn && mainContent) {
        const handleScroll = e => {
            // Determine scroll position based on which element is scrolling
            const scrollTopPosition = (e.target === document || e.target === window)
                ? window.scrollY
                : mainContent.scrollTop;

            const visible = scrollTopPosition > 200;
            scrollBtn.style.opacity = visible ? '1' : '0';
            scrollBtn.style.pointerEvents = visible ? 'auto' : 'none';
            scrollBtn.style.display = visible ? 'flex' : 'none';

            // Chat bubble sits at the page's exact bottom-right corner until
            // this button appears, then slides up to rest directly above it
            // (scroll-top.php's own button is `bottom: 6rem` + ~2.75rem
            // tall, so 9.5rem clears it with a small gap) — same scroll
            // threshold as the button itself so they always move together.
            if (chatWidget) {
                chatWidget.style.bottom = visible ? '9.5rem' : '1.5rem';
            }
        };

        // Attach listeners
        window.addEventListener('scroll', handleScroll);
        mainContent.addEventListener('scroll', handleScroll);

        // Click handler
        scrollBtn.addEventListener('click', () => {
            // Check if main content is scrolled or if the window is scrolled
            if (mainContent.scrollTop > 0) mainContent.scrollTo({ top: 0, behavior: 'smooth' });
            else window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Initial check
        handleScroll({ target: mainContent });
    }
}