// /resources/js/utils/review-criteria-form.js
//
// Shared wiring for the "Review A Landlord"/"Review A Tenant" forms
// (resources/views/pages/review-landlord.php / review-tenant.php) — both
// forms share the identical shape: a star-picker + N/A toggle per
// [data-criterion-row], plus a tag-chip picker. This wires the interaction
// and exposes collectCriteria()/collectTags() for the submit handler.

/**
 * @param {HTMLFormElement} form
 */
export function wireReviewCriteriaForm(form) {
  form.querySelectorAll('[data-criterion-row]').forEach((row) => {
    const picker = row.querySelector('.criterion-star-picker');
    const naCheckbox = row.querySelector('.criterion-na-checkbox');
    let selected = 0;

    function renderStars() {
      picker.querySelectorAll('.star-btn').forEach((btn) => {
        const isFilled = Number(btn.dataset.star) <= selected;
        btn.classList.toggle('text-amber-400', isFilled);
        btn.classList.toggle('text-gray-300', !isFilled);
        btn.classList.toggle('dark:text-gray-600', !isFilled);
      });
      picker.classList.toggle('opacity-40', naCheckbox.checked);
      picker.classList.toggle('pointer-events-none', naCheckbox.checked);
    }

    picker.addEventListener('click', (e) => {
      const btn = e.target.closest('.star-btn');
      if (!btn || naCheckbox.checked) return;
      selected = Number(btn.dataset.star) === selected ? 0 : Number(btn.dataset.star);
      renderStars();
      row.dataset.selectedStars = String(selected);
    });

    naCheckbox.addEventListener('change', () => {
      if (naCheckbox.checked) {
        selected = 0;
        row.dataset.selectedStars = '0';
      }
      renderStars();
    });

    row.dataset.selectedStars = '0';
  });

  form.querySelectorAll('.tag-chip input[type="checkbox"]').forEach((checkbox) => {
    const chip = checkbox.closest('.tag-chip');
    const sync = () => {
      chip.classList.toggle('bg-indigo-600', checkbox.checked);
      chip.classList.toggle('border-indigo-600', checkbox.checked);
      chip.classList.toggle('text-white', checkbox.checked);
    };
    checkbox.addEventListener('change', sync);
    sync();
  });
}

/**
 * @param {HTMLFormElement} form
 * @returns {{criteria: Array<{criterion_id:number, stars:?number, is_na:boolean}>, allAnswered: boolean}}
 */
export function collectCriteria(form) {
  const criteria = [];
  let allAnswered = true;

  form.querySelectorAll('[data-criterion-row]').forEach((row) => {
    const criterionId = Number(row.dataset.criterionId);
    const naCheckbox = row.querySelector('.criterion-na-checkbox');
    const stars = Number(row.dataset.selectedStars || 0);
    const isNa = naCheckbox.checked;

    if (!isNa && stars < 1) {
      allAnswered = false;
    }

    criteria.push({ criterion_id: criterionId, stars: isNa ? null : stars, is_na: isNa });
  });

  return { criteria, allAnswered };
}

/**
 * @param {HTMLFormElement} form
 * @returns {string[]}
 */
export function collectTags(form) {
  return Array.from(form.querySelectorAll('.tag-chip input[type="checkbox"]:checked')).map((cb) => cb.value);
}
