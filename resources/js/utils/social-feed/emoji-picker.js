// /resources/js/utils/social-feed/emoji-picker.js
//
// A lightweight, curated emoji grid — not a full emoji library. Attaches
// to a trigger button and inserts the picked emoji into a target
// textarea at the current cursor position. Reused by both the create-post
// composer and the view-post modal's comment box.

const EMOJIS = [
  '😀', '😂', '😍', '🥰', '😎', '🤔', '😢', '😡',
  '👍', '👎', '🙏', '👏', '🎉', '🔥', '💯', '❤️',
  '🏠', '🏢', '🔑', '📸', '📍', '✅', '⭐', '💬',
];

export function initEmojiPicker(triggerBtn, targetTextarea) {
  if (!triggerBtn || !targetTextarea || triggerBtn.dataset.emojiInitialized) return;
  triggerBtn.dataset.emojiInitialized = 'true';

  let popover = null;

  function closePopover() {
    popover?.remove();
    popover = null;
    document.removeEventListener('click', onDocumentClick, true);
  }

  function onDocumentClick(e) {
    if (popover && !popover.contains(e.target) && e.target !== triggerBtn) {
      closePopover();
    }
  }

  function openPopover() {
    if (popover) {
      closePopover();
      return;
    }

    popover = document.createElement('div');
    popover.className = 'emoji-picker-popover absolute z-50 mt-2 right-0 grid grid-cols-8 gap-1 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl w-64';
    popover.innerHTML = EMOJIS.map((e) => `<button type="button" class="emoji-option text-lg hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg p-1" data-emoji="${e}">${e}</button>`).join('');

    const anchor = triggerBtn.closest('.relative') || triggerBtn.parentElement;
    anchor.appendChild(popover);

    popover.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-emoji]');
      if (!btn) return;

      const start = targetTextarea.selectionStart ?? targetTextarea.value.length;
      const end = targetTextarea.selectionEnd ?? targetTextarea.value.length;
      const emoji = btn.dataset.emoji;

      targetTextarea.value = targetTextarea.value.slice(0, start) + emoji + targetTextarea.value.slice(end);
      targetTextarea.focus();
      targetTextarea.selectionStart = targetTextarea.selectionEnd = start + emoji.length;
      targetTextarea.dispatchEvent(new Event('input', { bubbles: true }));

      closePopover();
    });

    setTimeout(() => document.addEventListener('click', onDocumentClick, true), 0);
  }

  triggerBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    openPopover();
  });
}
