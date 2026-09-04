// /resources/js/utils/adverts/country-targeting.js
//
// Country targeting widget: a <select> of countries + an "All" checkbox.
// Picking a country adds it to an in-memory array rendered as removable
// badge chips; checking "All" clears the array and serializes
// selected_countries as the ['ALL'] sentinel (added by form-submit.js, not
// here — this only ever writes [] to the hidden input when "All" is
// checked). Ported near-verbatim from the legacy gonachi/ platform.

export function initCountryTargeting({ idPrefix, initialSelection }) {
  const selector = document.getElementById(`${idPrefix}-country-selector`);
  const allCheckbox = document.getElementById(`${idPrefix}-all-countries`);
  const bucket = document.getElementById(`${idPrefix}-selected-bucket`);
  const hiddenInput = document.getElementById(`${idPrefix}-countries-hidden-json`);
  const errorMsg = document.getElementById(`${idPrefix}-country-error`);

  if (!selector || !allCheckbox || !bucket || !hiddenInput) {
    console.warn('Country targeting elements missing for prefix:', idPrefix);
    return;
  }

  let selected = Array.isArray(initialSelection) ? initialSelection.map(String) : [];

  const render = () => {
    const isValid = allCheckbox.checked || selected.length > 0;

    if (isValid) {
      errorMsg?.classList.add('hidden');
      bucket.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/10');
    }

    if (allCheckbox.checked) {
      bucket.innerHTML = `
        <div class="flex items-center gap-2 px-4 py-2 bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-teal-200 dark:border-teal-800">
          All Countries Selected
        </div>`;
      hiddenInput.value = JSON.stringify([]);
      selector.disabled = true;
      return;
    }

    selector.disabled = false;
    if (selected.length === 0) {
      bucket.innerHTML = '<span class="text-gray-400 text-[11px] font-bold uppercase italic p-2 tracking-tight">No countries selected</span>';
      hiddenInput.value = JSON.stringify([]);
      return;
    }

    bucket.innerHTML = selected
      .map((id) => {
        const option = selector.querySelector(`option[value="${id}"]`);
        const name = option ? option.dataset.name : `ID: ${id}`;
        return `
        <div class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
          <span class="text-[11px] font-black text-gray-900 dark:text-gray-200 uppercase tracking-tighter">${name.trim()}</span>
          <button type="button" class="remove-country-btn text-red-400 hover:text-red-600 transition-colors flex items-center justify-center p-0.5" data-id="${id}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 pointer-events-none">
              <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
          </button>
        </div>`;
      })
      .join('');

    hiddenInput.value = JSON.stringify(selected);
  };

  window.validateCountryTargeting = () => {
    const isValid = allCheckbox.checked || selected.length > 0;
    if (!isValid) {
      errorMsg?.classList.remove('hidden');
      bucket.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/10');
      bucket.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return isValid;
  };

  selector.addEventListener('change', (e) => {
    const val = e.target.value;
    if (val && !selected.includes(val)) {
      selected.push(val);
      render();
    }
    e.target.value = '';
  });

  bucket.addEventListener('click', (e) => {
    const btn = e.target.closest('.remove-country-btn');
    if (!btn) return;
    e.preventDefault();
    const idToRemove = btn.getAttribute('data-id');
    selected = selected.filter((id) => id !== idToRemove);
    render();
  });

  allCheckbox.addEventListener('change', () => {
    if (allCheckbox.checked) selected = [];
    render();
  });

  render();
}
