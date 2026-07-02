// /resources/js/utils/home/tenant-token-gateway.js

/**
 * Wires the "Tenant Portal Gateway" access token form on the home page.
 */
export function initTenantTokenGateway() {
    const form = document.getElementById('tenant-token-form');
    if (!form) return;

    const input = document.getElementById('access_token');
    const submitBtn = document.getElementById('tenant-token-submit');
    const messageEl = document.getElementById('tenant-token-message');

    const showMessage = (text, type) => {
        if (!messageEl) return;
        messageEl.textContent = text;
        messageEl.className = `text-[10px] font-bold uppercase tracking-wide ${type === 'error' ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400'}`;
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const tokenCode = input?.value.trim();
        if (!tokenCode) return;

        submitBtn.disabled = true;
        const originalLabel = submitBtn.textContent;
        submitBtn.textContent = 'Verifying...';
        if (messageEl) messageEl.classList.add('hidden');

        try {
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const res = await fetch(`${baseUrl}api/tenant-access`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ access_token: tokenCode })
            });

            const result = await res.json();

            if (result.success && result.redirect_url) {
                showMessage(result.messages?.[0] || 'Access token verified.', 'success');
                messageEl?.classList.remove('hidden');
                window.location.href = `${baseUrl}${result.redirect_url.replace(/^\//, '')}`;
                return;
            }

            showMessage(result.messages?.[0] || 'Unable to verify this access token.', 'error');
            messageEl?.classList.remove('hidden');
        } catch (err) {
            console.error('Tenant token verification error:', err);
            showMessage('A network error occurred. Please try again.', 'error');
            messageEl?.classList.remove('hidden');
        }

        submitBtn.disabled = false;
        submitBtn.textContent = originalLabel;
    });
}
