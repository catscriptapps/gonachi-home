// /resources/js/forms/tenant-form.js

/**
 * Modern, sleek shared form renderer for Tenants
 * Colors: primary (Primary Focus / Submits)
 */
export function tenantForm({
    firstName = '',
    lastName = '',
    email = '',
    phone = '',
    buttonLabel = 'Create Account',
    formId = 'tenants-form',
}) {
    const idPrefix = 'tenants';

    const inputClasses = `
        block w-full rounded-lg
        border border-gray-300 dark:border-gray-600
        bg-white dark:bg-gray-900
        text-gray-900 dark:text-white
        placeholder:text-gray-400
        focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20
        sm:text-sm transition-all duration-150 py-1.5 px-3 font-sans
    `.replace(/\s+/g, ' ').trim();

    const labelClasses = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-0.5 ml-0.5 font-sans";

    const eyeIcon = `
        <button type="button" onclick="const p = this.parentElement.querySelector('input'); p.type = p.type === 'password' ? 'text' : 'password';"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary-500">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>`;

    return `
    <form
        id="${formId}"
        class="w-full max-w-lg mx-auto space-y-3 p-0.5 font-sans animate-in fade-in duration-200"
        novalidate>

        <div class="p-4 rounded-xl bg-gradient-to-br from-primary-100 to-gray-50 dark:from-gray-900 dark:to-gray-800/40 border border-gray-100 dark:border-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="${idPrefix}-first-name" class="${labelClasses}">First Name</label>
                    <input type="text" required id="${idPrefix}-first-name" name="firstName"
                        placeholder="Jane" value="${firstName}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-last-name" class="${labelClasses}">Last Name</label>
                    <input type="text" required id="${idPrefix}-last-name" name="lastName"
                        placeholder="Doe" value="${lastName}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-email" class="${labelClasses}">Email</label>
                    <input type="email" required id="${idPrefix}-email" name="email"
                        placeholder="jane.doe@example.com" value="${email}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-phone" class="${labelClasses}">Phone (optional)</label>
                    <input type="tel" id="${idPrefix}-phone" name="phone"
                        placeholder="(705) 123-1234" value="${phone}" class="${inputClasses}" />
                </div>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gradient-to-br from-primary-100 to-gray-50 dark:from-gray-900 dark:to-gray-800/40 border border-gray-100 dark:border-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="relative">
                    <label for="${idPrefix}-password" class="${labelClasses}">Password</label>
                    <input type="password" required id="${idPrefix}-password" name="password"
                        placeholder="••••••••" class="${inputClasses}" />
                    ${eyeIcon}
                </div>
                <div class="relative">
                    <label for="${idPrefix}-confirm-password" class="${labelClasses}">Confirm Password</label>
                    <input type="password" required id="${idPrefix}-confirm-password" name="password_confirmation"
                        placeholder="••••••••" class="${inputClasses}" />
                    ${eyeIcon}
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-8 py-2 text-sm font-bold text-white shadow-md shadow-primary-600/10 hover:bg-primary-700 transition-all active:scale-98 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}
