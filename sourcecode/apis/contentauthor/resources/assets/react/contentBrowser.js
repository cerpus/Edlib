import React from 'react';
import ReactDOM from 'react-dom';
import { IntlProvider } from 'react-intl';
import i18nDefault, { addLanguage } from './components/languageSetup';
import { ImageBrowserContainer } from './components/ImageBrowser';
import { VideoBrowserContainer } from './components/VideoBrowser';
import { AudioBrowserContainer } from './components/AudioBrowser';

if (!window.Intl) {
    require('intl');
    require('intl/locale-data/jsonp/en-US.js');
    require('intl/locale-data/jsonp/en-GB.js');
    require('intl/locale-data/jsonp/nb-NO.js');
    require('intl/locale-data/jsonp/sv-SE.js');
}

function initImageBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
    } = settings;

    const i18nData = (locale !== null ? addLanguage(locale) : i18nDefault);

    ReactDOM.render(
        <IntlProvider {...i18nData}>
            <ImageBrowserContainer
                onSelect={onSelectCallback}
                locale={locale}
                onToggle={onToggleCallback}
            />
        </IntlProvider>,
        element
    );
}

function initVideoBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
    } = settings;

    const i18nData = (locale !== null ? addLanguage(locale) : i18nDefault);

    ReactDOM.render(
        <IntlProvider {...i18nData}>
            <VideoBrowserContainer
                onSelect={onSelectCallback}
                locale={locale}
                onToggle={onToggleCallback}
            />
        </IntlProvider>,
        element
    );
}

function initAudioBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
    } = settings;

    const i18nData = (locale !== null ? addLanguage(locale) : i18nDefault);

    ReactDOM.render(
        <IntlProvider {...i18nData}>
            <AudioBrowserContainer
                onSelect={onSelectCallback}
                locale={locale}
                onToggle={onToggleCallback}
            />
        </IntlProvider>,
        element
    );
}

window.initImageBrowser = initImageBrowser;
window.initVideoBrowser = initVideoBrowser;
window.initAudioBrowser = initAudioBrowser;
