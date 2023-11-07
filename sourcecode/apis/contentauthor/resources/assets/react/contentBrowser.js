import React from 'react';
import ReactDOM from 'react-dom';
import { IntlProvider } from 'react-intl';
import { loadLocale } from './components/languageSetup';
import { ImageBrowserContainer } from './components/ImageBrowser';
import { VideoBrowserContainer } from './components/VideoBrowser';
import { AudioBrowserContainer } from './components/AudioBrowser';

function initImageBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
        getCurrentLanguage,
        apiDetailsUrl,
    } = settings;

    (async () => {
        const localeData = await loadLocale(locale);

        ReactDOM.render(
            <IntlProvider {...localeData} textComponent="span">
                <ImageBrowserContainer
                    onSelect={onSelectCallback}
                    locale={locale}
                    onToggle={onToggleCallback}
                    getCurrentLanguage={getCurrentLanguage}
                    apiDetailsUrl={apiDetailsUrl}
                />
            </IntlProvider>,
            element
        );
    })();
}

function initVideoBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
    } = settings;

    (async () => {
        const localeData = await loadLocale(locale);

        ReactDOM.render(
            <IntlProvider {...localeData} textComponent="span">
                <VideoBrowserContainer
                    onSelect={onSelectCallback}
                    locale={locale}
                    onToggle={onToggleCallback}
                />
            </IntlProvider>,
            element
        );
    })();
}

function initAudioBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
        getCurrentLanguage,
        apiDetailsUrl,
    } = settings;

    (async () => {
        const localeData = await loadLocale(locale);

        ReactDOM.render(
            <IntlProvider {...localeData} textComponent="span">
                <AudioBrowserContainer
                    onSelect={onSelectCallback}
                    locale={locale}
                    onToggle={onToggleCallback}
                    getCurrentLanguage={getCurrentLanguage}
                    apiDetailsUrl={apiDetailsUrl}
                />
            </IntlProvider>,
            element
        );
    })();
}

window.initImageBrowser = initImageBrowser;
window.initVideoBrowser = initVideoBrowser;
window.initAudioBrowser = initAudioBrowser;
