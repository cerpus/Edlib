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
        searchUrl,
        detailsUrl,
        searchParams,
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
                    detailsUrl={detailsUrl}
                    searchUrl={searchUrl}
                    searchParams={searchParams}
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
        searchUrl,
        detailsUrl,
        searchParams,
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
                    detailsUrl={detailsUrl}
                    searchUrl={searchUrl}
                    searchParams={searchParams}
                />
            </IntlProvider>,
            element
        );
    })();
}

export {
    initAudioBrowser,
    initImageBrowser,
    initVideoBrowser,
};
