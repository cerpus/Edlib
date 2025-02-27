/*
 * Launch a Bootstrap modal after the content is loaded via htmx and the request
 * has been settled. This solves issues with multiple modals and needing a
 * skeleton modal.
 *
 * For this to work, links should look like this:
 *
 *   <a hx-get="..."
 *      hx-target="#modal-container"
 *      hx-swap="beforeend"
 *      data-modal="true">...</a>
 */

import { Modal } from 'bootstrap';

document.body.addEventListener('htmx:beforeRequest', (event) => {
    if (event.detail.elt.hasAttribute('data-modal')) {
        event.detail.requestConfig.modalRequest = true;
    }
});

document.body.addEventListener('htmx:afterSettle', (event) => {
    if (event.detail.requestConfig.modalRequest) {
        new Modal(event.detail.target.querySelector('.modal:last-child')).show({
            focus: true,
            keyboard: true,
        });
    }
});
