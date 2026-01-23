import { getById, getOne } from "../helpers";

(function () {
  const DURATION = 200;
  let urlModalId = null;
  const getModal = (id) => getById(document, id);

  function openModal(modal) {
    if (!modal) return;

    // show container
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');

    // animate in (next frame so transitions apply)
    requestAnimationFrame(() => {
      getOne(modal, '.ui-modal-backdrop')?.classList.remove('opacity-0');
      getOne(modal, '.ui-modal-panel')?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
    });

    // document.documentElement.classList.add('overflow-hidden');
  }

  function closeModal(modal) {
    if (!modal) return;

    // animate out
    getOne(modal, '.ui-modal-backdrop')?.classList.add('opacity-0');
    getOne(modal, '.ui-modal-panel')?.classList.add('opacity-0', 'scale-95', 'translate-y-2');

    // hide after transition
    window.setTimeout(() => {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');

      const anyOpen = getOne(document, '.ui-modal[data-modal]:not(.hidden)');
      // if (!anyOpen) document.documentElement.classList.remove('overflow-hidden');
    }, DURATION);

    if (modal.id && modal.id === urlModalId) {
      clearModalFromUrl();
    }
  }

  // Click handlers (open / close / outside)
  document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-modal-open]');
    if (openBtn) {
      const id = openBtn.getAttribute('data-modal-open');
      openModal(getModal(id));
      return;
    }

    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
      const modal = closeBtn.closest('[data-modal]');
      closeModal(modal);
      return;
    }

    // outside click
    const modal = e.target.closest('[data-modal]');
    if (!modal || modal.classList.contains('hidden')) return;

    const closeOutside = modal.getAttribute('data-close-outside') === 'true';
    const clickedInsidePanel = !!e.target.closest('.ui-modal-panel');

    if (closeOutside && !clickedInsidePanel) {
      closeModal(modal);
    }
  });

  // ESC handler
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;

    const modal = getOne(document, '.ui-modal[data-modal]:not(.hidden)');
    if (!modal) return;

    const closeEsc = modal.getAttribute('data-close-esc') === 'true';
    if (closeEsc) closeModal(modal);
  });

  // URL trigger: ?modal=id  OR  #modal=id
  function openFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const queryModal = params.get('modal');

    const hash = window.location.hash || '';
    const hashMatch = hash.match(/modal=([^&]+)/);
    const hashModal = hashMatch ? decodeURIComponent(hashMatch[1]) : null;

    const id = queryModal || hashModal;
    if (id) {
      urlModalId = id;
      openModal(getModal(id));
    } else {
      urlModalId = null;
    }
  }

  function clearModalFromUrl() {
    const url = new URL(window.location.href);
    url.searchParams.delete('modal');

    if (url.hash.includes('modal=')) {
      url.hash = '';
    }

    history.replaceState({}, document.title, url.toString());
    urlModalId = null;
  }

  window.addEventListener('hashchange', openFromUrl);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', openFromUrl);
  } else {
    openFromUrl();
  }

  // Optional API
  window.UIModal = {
    open: (id) => openModal(getModal(id)),
    close: (id) => closeModal(getModal(id)),
  };
})();
