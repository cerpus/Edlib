import {
    messagesEnGb as DisplayOptionsEnGb,
    messagesNbNo as DisplayOptionsNbNo,
    messagesNnNo as DisplayOptionsNnNo,
} from './DisplayOptions/language';

import {
    messagesEnGb as H5PContentUpgradeEnGb,
    messagesNbNo as H5PContentUpgradeNbNo,
    messagesNnNo as H5PContentUpgradeNnNo,
} from './H5PContentUpgrade/language';

import {
    messagesEnGb as LicenseIndicatorEnGb,
    messagesNbNo as LicenseIndicatorNbNo,
    messagesNnNo as LicenseIndicatorNnNo,
} from './LicenseIndicator/language';

import {
    messagesEnGb as SharingEnGb,
    messagesNbNo as SharingNbNo,
    messagesNnNo as SharingNnNo,
} from './Sharing/language';

import {
    messagesEnGb as ContentPropertiesEnGb,
    messagesNbNo as ContentPropertiesNbNo,
    messagesNnNo as ContentPropertiesNnNo,
} from './ContentProperties/language';

import {
    messagesEnGb as SaveBoxEnGb,
    messagesNbNo as SaveBoxNbNo,
    messagesNnNo as SaveBoxNnNo,
} from './SaveBox/language';

import {
    messagesEnGb as LockEnGb,
    messagesNbNo as LockNbNo,
    messagesNnNo as LockNnNo,
} from './Lock/language';

const messagesEnGb = Object.assign(
    {},
    DisplayOptionsEnGb,
    H5PContentUpgradeEnGb,
    LicenseIndicatorEnGb,
    SharingEnGb,
    ContentPropertiesEnGb,
    SaveBoxEnGb,
    LockEnGb
);
const messagesNbNo = Object.assign(
    {},
    DisplayOptionsNbNo,
    H5PContentUpgradeNbNo,
    LicenseIndicatorNbNo,
    SharingNbNo,
    ContentPropertiesNbNo,
    SaveBoxNbNo,
    LockNbNo
);

const messagesNnNo = Object.assign(
    {},
    DisplayOptionsNnNo,
    H5PContentUpgradeNnNo,
    LicenseIndicatorNnNo,
    SharingNnNo,
    ContentPropertiesNnNo,
    SaveBoxNnNo,
    LockNnNo
);

export {
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
