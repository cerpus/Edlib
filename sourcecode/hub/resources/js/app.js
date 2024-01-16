import { findIframeByWindow } from "./helpers";

import.meta.glob(['bootstrap-icons/bootstrap-icons.svg', '../images/**']);

import 'bootstrap';
import './bootstrap';
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

addEventListener('DOMContentLoaded', function () {
    const token = document.documentElement.getAttribute('data-session-scope');

    if (!token) {
        return;
    }

    if (!window.Livewire) {
        throw new Error('A session scope is present, but Livewire is not.');
    }

    window.Livewire.addHeaders({
       'Edlib-Session-Scope': token,
    });
});
