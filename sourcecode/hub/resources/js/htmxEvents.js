import { Modal } from 'bootstrap';

document.body.addEventListener('htmx:configRequest', (event) => {
    event.detail.headers['X-Requested-With'] = 'XMLHttpRequest';
    event.detail.headers['X-XSRF-Token'] = decodeURIComponent(
        document.cookie
            .split('; ')
            .find(cookie => cookie.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? ''
    );

    const token = document.documentElement.getAttribute('data-session-scope');
    const path = event.detail.path;

    if (token && !/[?&]session_scope=/.test(path)) {
        event.detail.path = `${path.includes('?') ? '&' : '?'}session_scope=${token}`;
    }
});

/**
 * Override 'htmx:confirm' event to use a Bootstrap modal for confirmation
 *
 * Attributes that are used from the triggering element:
 *  - data-confirm-id: Id of the Bootstrap modal to use, default is 'htmxConfirmModal'
 *  - data-confirm-title: Modal title, replaces text of element with id '{data-confirm-id}-Title'
 *  - data-confirm-ok: Ok/Confirm button label, replaces text of element with id '{data-confirm-id}-Ok'
 *  - data-confirm-cancel: Cancel button label, replaces text of element with id '{data-confirm-id}-Cancel'
 *
 * hx-confirm is required and will replace text of element with id '{data-confirm-id}-Body'. The modal is
 * required to have a clickable element with id '{data-confirm-id}-Ok', this is the element the user clicks to
 * confirm that the request should be issued.
 */
document.body.addEventListener('htmx:confirm', event => {
    // The 'htmx:confirm' event is fired on all requests done with htmx, so check if 'hx-confirm' is set on this request
    // Since 'hx-confirm' can be inherited we get it from the event
    if (event.detail?.question) {
        event.preventDefault();

        const modalId = event.target.hasAttribute('data-confirm-id') ? event.target.getAttribute('data-confirm-id') : 'htmxConfirmModal';

        if (!document.getElementById(modalId)) {
            console.error(`Confirm modal with id '${modalId}' was not found, request was blocked`, [event.target]);
            return;
        }

        if (!document.getElementById(`${modalId}-Ok`)) {
            console.error(`Confirm modal is missing element with id '${modalId}-Ok', request was blocked`, [event.target]);
            return;
        }

        if (event.target.hasAttribute('data-confirm-title') && document.getElementById(`${modalId}-Title`)) {
            document.getElementById(`${modalId}-Title`).innerText = event.target.getAttribute('data-confirm-title');
        }
        if (document.getElementById(`${modalId}-Body`)) {
            document.getElementById(`${modalId}-Body`).innerText = event.detail.question;
        }
        if (event.target.hasAttribute('data-confirm-ok')) {
            document.getElementById(`${modalId}-Ok`).innerText = event.target.getAttribute('data-confirm-ok');
        }
        if (event.target.hasAttribute('data-confirm-ok-class')) {
            document.getElementById(`${modalId}-Ok`).classList.add(event.target.getAttribute('data-confirm-ok-class'));
        }
        if (event.target.hasAttribute('data-confirm-cancel') && document.getElementById(`${modalId}-Cancel`)) {
            document.getElementById(`${modalId}-Cancel`).innerText = event.target.getAttribute('data-confirm-cancel');
        }

        // User confirms that the request should be issued
        document.getElementById(`${modalId}-Ok`).addEventListener('click', (e) => {
            e.preventDefault();
            e.target.toggleAttribute('disabled', true);
            e.target.setAttribute('aria-disabled', 'true');
            event.detail.issueRequest(true); // true will prevent window.confirm and issue the request
        });

        (new Modal(`#${modalId}`, {
            focus: true,
            keyboard: true,
        })).show();
    }
});
