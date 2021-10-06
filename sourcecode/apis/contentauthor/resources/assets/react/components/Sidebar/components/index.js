import { messagesEnGb as DisplayOptionsEnGb, messagesNbNo as DisplayOptionsNbNo } from './DisplayOptions';
import { messagesEnGb as H5PContentUpgradeEnGb, messagesNbNo as H5PContentUpgradeNbNo } from './H5PContentUpgrade';
import { messagesEnGb as LicenseIndicatorEnGb, messagesNbNo as LicenseIndicatorNbNo } from './LicenseIndicator';
import { messagesEnGb as SharingEnGb, messagesNbNo as SharingNbNo } from './Sharing';
import { messagesEnGb as ContentPropertiesEnGb, messagesNbNo as ContentPropertiesNbNo } from './ContentProperties';
import { messagesEnGb as SaveBoxEnGb, messagesNbNo as SaveBoxNbNo } from './SaveBox';
import { messagesEnGb as LockEnGb, messagesNbNo as LockNbNo } from './Lock';

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
};
