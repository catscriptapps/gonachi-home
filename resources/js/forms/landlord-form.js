// /resources/js/forms/landlord-form.js

/**
 * Modern, sleek shared form renderer for Landlords
 * Colors: primary (Primary Focus / Submits), Navy (Secondary Structural Gradients)
 */
export function landlordForm({
    mode = 'add',
    companyName = '',
    email = '',
    phone = '',
    addressLine1 = '',
    addressLine2 = '',
    city = '',
    countryId = 39, // Defaulting directly to Canada (ID = 39)
    regionId = 866, // Defaulting to Ontario (ID = 866)
    postalCode = '',
    taxId = '',
    buttonLabel = 'Save',
    formId = 'landlords-form',
    countries = [],
    regions = [],
    encodedId = null
}) {
    const idPrefix = mode === 'edit' ? 'landlords-edit' : 'landlords';
    const dataEncodedIdAttr = encodedId ? `data-encoded-id="${encodedId}"` : '';

    const inputClasses = `
        block w-full rounded-lg 
        border border-gray-300 dark:border-gray-600 
        bg-white dark:bg-gray-900 
        text-gray-900 dark:text-white 
        placeholder:text-gray-400 
        focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 
        sm:text-sm transition-all duration-150 py-1.5 px-3 font-sans
    `.replace(/\s+/g, ' ').trim();

    const disabledClasses = "bg-gray-100 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700";
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
        class="w-full max-w-5xl mx-auto space-y-3 p-0.5 font-sans animate-in fade-in duration-200"
        novalidate 
        ${dataEncodedIdAttr} 
        data-country-id="${countryId}">

        <div class="p-4 rounded-xl bg-gradient-to-br from-primary-100 to-gray-50 dark:from-gray-900 dark:to-gray-800/40 border border-gray-100 dark:border-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <label for="${idPrefix}-company-name" class="${labelClasses}">Company Name</label>
                    <input type="text" required id="${idPrefix}-company-name" name="companyName"
                        placeholder="Apex Asset Management" value="${companyName}" class="${inputClasses}" />
                </div>
                <div class="md:col-span-4">
                    <label for="${idPrefix}-email" class="${labelClasses}">Billing/Operations Email</label>
                    <input type="email" required id="${idPrefix}-email" name="email"
                        placeholder="billing@apexassets.ca" value="${email}" class="${inputClasses} ${mode === 'edit' ? disabledClasses : ''}" ${mode === 'edit' ? 'disabled' : ''} />
                </div>
                <div class="md:col-span-3">
                    <label for="${idPrefix}-phone" class="${labelClasses}">Contact Phone</label>
                    <input type="tel" id="${idPrefix}-phone" name="phone" required
                        placeholder="(705) 123-1234" value="${phone}" class="${inputClasses}" />
                </div>
                <div class="md:col-span-12">
                    <label for="${idPrefix}-tax-id" class="${labelClasses}">Tax ID / Business Number</label>
                    <input type="text" id="${idPrefix}-tax-id" name="taxId"
                        placeholder="e.g., 123456789RC0001" value="${taxId}" class="${inputClasses}" />
                </div>
            </div>
        </div>

        ${mode === 'add' ? `
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
        </div>` : ''}

        <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-3">
            <div class="flex items-center space-x-2 border-b border-gray-100 dark:border-gray-800 pb-1.5">
                <span class="w-1.5 h-3 bg-navy-600 dark:bg-primary-500 rounded-full"></span>
                <h3 class="text-[10px] font-black uppercase tracking-[0.15em] text-navy-900 dark:text-gray-400">Headquarters Location</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="${idPrefix}-address-line1" class="${labelClasses}">Address Line 1</label>
                    <input type="text" required id="${idPrefix}-address-line1" name="addressLine1"
                        placeholder="100 Front St West" value="${addressLine1}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-address-line2" class="${labelClasses}">Address Line 2 (Suite/Floor)</label>
                    <input type="text" id="${idPrefix}-address-line2" name="addressLine2"
                        placeholder="Suite 400" value="${addressLine2}" class="${inputClasses}" />
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label for="${idPrefix}-city" class="${labelClasses}">City</label>
                    <input type="text" id="${idPrefix}-city" name="city" required
                        placeholder="Toronto" value="${city}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-postal-code" class="${labelClasses}">Postal Code</label>
                    <input type="text" id="${idPrefix}-postal-code" name="postalCode" required
                        placeholder="M5J 1E3" value="${postalCode}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-country" class="${labelClasses}">Country</label>
                    <select id="${idPrefix}-country" name="countryId" disabled class="${inputClasses} ${disabledClasses}">
                        ${countries.map(c => `<option value="${c.id}" ${c.id == countryId ? 'selected' : ''}>${c.name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label for="${idPrefix}-region" class="${labelClasses}">Province</label>
                    <select id="${idPrefix}-region" name="regionId" required class="${inputClasses}">
                        <option value="">Select Province</option>
                        ${regions.map(r => `<option value="${r.id}" ${r.id == regionId ? 'selected' : ''}>${r.name}</option>`).join('')}
                    </select>
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