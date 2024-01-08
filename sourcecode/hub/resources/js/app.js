import.meta.glob(['bootstrap-icons/bootstrap-icons.svg', '../images/**']);

import 'bootstrap';
import './bootstrap';
import './resize';

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
