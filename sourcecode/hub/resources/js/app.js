import 'bootstrap';
import 'htmx.org';
import { findIframeByWindow } from './helpers';
import './clipboard';
import './resize';

import.meta.glob(['bootstrap-icons/bootstrap-icons.svg', '../images/**']);

document.body.addEventListener('htmx:configRequest', (event) => {
    event.detail.headers['X-Requested-With'] = 'XMLHttpRequest';
    event.detail.headers['X-XSRF-Token'] = decodeURIComponent(
        document.cookie
            .split('; ')
            .find(cookie => cookie.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? ''
    );
});

/**
 * Log messages from iframes.
 */
addEventListener('message', (event) => {
    const messageBoxQuery = findIframeByWindow(event.source)
        ?.getAttribute('data-log-to');

    if (!messageBoxQuery) {
        return;
    }

    const messageBox = document.querySelector(messageBoxQuery);

    if (!messageBox) {
        return
    }

    messageBox.appendChild(new Text(JSON.stringify(event.data, null, '  ') + "\n"));
});
