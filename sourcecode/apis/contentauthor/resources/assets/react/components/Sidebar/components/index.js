import {
    messagesEnGb as DisplayOptionsEnGb,
    messagesNbNo as DisplayOptionsNbNo,
    messagesNnNo as DisplayOptionsNnNo,
} from './DisplayOptions';

import {
    messagesEnGb as H5PContentUpgradeEnGb,
    messagesNbNo as H5PContentUpgradeNbNo,
    messagesNnNo as H5PContentUpgradeNnNo,
} from './H5PContentUpgrade';

import {
    messagesEnGb as LicenseIndicatorEnGb,
    messagesNbNo as LicenseIndicatorNbNo,
    messagesNnNo as LicenseIndicatorNnNo,
} from './LicenseIndicator';

import {
    messagesEnGb as SharingEnGb,
    messagesNbNo as SharingNbNo,
    messagesNnNo as SharingNnNo,
} from './Sharing';

import {
    messagesEnGb as ContentPropertiesEnGb,
    messagesNbNo as ContentPropertiesNbNo,
    messagesNnNo as ContentPropertiesNnNo,
} from './ContentProperties';

import {
    messagesEnGb as SaveBoxEnGb,
    messagesNbNo as SaveBoxNbNo,
    messagesNnNo as SaveBoxNnNo,
} from './SaveBox';

import {
    messagesEnGb as LockEnGb,
    messagesNbNo as LockNbNo,
    messagesNnNo as LockNnNo,
} from './Lock';

const messagesEnGb = Object.assign(
    {},
    DisplayOptionsEnGb,
    H5PContentUpgradeEnGb,
    LicenseIndicatorEnGb,
    SharingEnGb,
    ContentPropertiesEnGb,
    SaveBoxEnGb,
    LockEnGb,
);

const messagesNbNo = Object.assign(
    {},
    DisplayOptionsNbNo,
    H5PContentUpgradeNbNo,
    LicenseIndicatorNbNo,
    SharingNbNo,
    ContentPropertiesNbNo,
    SaveBoxNbNo,
    LockNbNo,
);

const messagesNnNo = Object.assign(
    {},
    DisplayOptionsNnNo,
    H5PContentUpgradeNnNo,
    LicenseIndicatorNnNo,
    SharingNnNo,
    ContentPropertiesNnNo,
    SaveBoxNnNo,
    LockNnNo,
);

export { default as AdapterSelector } from './AdapterSelector';
export { default as AlertBox } from './AlertBox';
export { default as DisplayOptions } from './DisplayOptions';
export { default as ContentUpgradeLayout, ContentUpgradeContainer, ContentNoUpgrades } from './H5PContentUpgrade';
export { default as LicenseIndicator } from './LicenseIndicator';
export { default as Publish } from './Publish';
export { default as SaveBox } from './SaveBox';
export { default as Lock } from './Lock';
export { default as Sharing } from './Sharing';
export { default as ContentProperties, ContentPropertiesContainer } from './ContentProperties';

export {
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
