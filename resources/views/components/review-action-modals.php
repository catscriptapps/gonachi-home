<?php
// /resources/views/components/review-action-modals.php
//
// No markup needed here — the "Report Review" and "Respond" modals are
// built lazily in JS (resources/js/components/review-actions.js, wired
// globally in app.js) the first time a [data-report-review]/
// [data-respond-review] button is clicked, same lazy-modal convention as
// resources/js/modals/reset-modal.js. This include point exists so the two
// profile pages that need the feature (landlords/detail.php,
// tenants/detail.php) stay self-documenting about the dependency.
