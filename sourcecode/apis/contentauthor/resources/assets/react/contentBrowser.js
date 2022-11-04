import React from 'react';
import ReactDOM from 'react-dom';
import { IntlProvider } from 'react-intl';
import i18nDefault, { addLanguage } from './components/languageSetup';
import { ImageBrowserContainer } from './components/ImageBrowser';
import { VideoBrowserContainer } from './components/VideoBrowser';
import { AudioBrowserContainer } from './components/AudioBrowser';

function initImageBrowser(element, settings) {
    const {
        onSelectCallback,
        locale,
        onToggleCallback,
        getCurrentLanguage,
    } = settings;

    const i18nData = (locale !== null ? addLanguage(locale) : i18nDefault);

    ReactDOM.render(
        <IntlProvider {...i18nData} textComponent="span">
            <ImageBrowserContainer
                onSelect={onSelectCallback}
                locale={locale}
                onToggle={onToggleCallback}
                getCurrentLanguage={getCurrentLanguage}
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
        <IntlProvider {...i18nData} textComponent="span">
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
        getCurrentLanguage,
    } = settings;

    const i18nData = (locale !== null ? addLanguage(locale) : i18nDefault);

    ReactDOM.render(
        <IntlProvider {...i18nData} textComponent="span">
            <AudioBrowserContainer
                onSelect={onSelectCallback}
                locale={locale}
                onToggle={onToggleCallback}
                getCurrentLanguage={getCurrentLanguage}
            />
        </IntlProvider>,
        element
    );
}

window.initImageBrowser = initImageBrowser;
window.initVideoBrowser = initVideoBrowser;
window.initAudioBrowser = initAudioBrowser;
