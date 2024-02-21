import { findIframeByWindow } from "./helpers";

import.meta.glob(['bootstrap-icons/bootstrap-icons.svg', '../images/**']);

import 'bootstrap';
import './bootstrap';
import './clipboard';
import './resize';

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
