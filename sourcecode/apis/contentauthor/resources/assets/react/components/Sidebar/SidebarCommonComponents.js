import { FormActions } from '../../contexts/FormContext';
import { ContentPropertiesContainer, LicenseIndicator, Sharing } from './components';
import React from 'react';

const SidebarCommonComponents = (settings, dispatch, state, intl) => {
    const { license, isShared } = state;
    const { contentProperties, canList, useLicense } = settings;

    const components = [];

    if (useLicense === true) {
        const {
            useAttribution = false,
            size = 1,
            useOldCopyrightName = true,
        } = settings;

        components.push({
            id: 'license',
            info: <div>({license})</div>,
            title: intl.formatMessage({ id: 'LICENSEINDICATOR.TITLE' }),
            component: (
                <LicenseIndicator
                    license={license || ''}
                    useAttribution={useAttribution || false}
                    onChange={newLicense => dispatch(FormActions.setLicense, { license: newLicense })}
                    useOldCopyrightName={useOldCopyrightName}
                    size={size}
                />
            ),
        });
    }

    if (canList === true) {
        components.push({
            id: 'sharing',
            title: intl.formatMessage({ id: 'SHARINGCOMPONENT.SHARING' }),
            component: (
                <Sharing
                    shared={isShared}
                    onChange={isShared => dispatch(FormActions.setSharing, { isShared })}
                />
            ),
        });
    }

    if (contentProperties) {
        components.push({
            id: 'contentProperties',
            title: intl.formatMessage({ id: 'CONTENTPROPERTIES.PROPERTIES' }),
            component: (
                <ContentPropertiesContainer
                    id={contentProperties.id}
                    createdAt={contentProperties.createdAt}
                    maxScore={contentProperties.maxScore}
                    type={contentProperties.type}
                    customFields={contentProperties.customFields}
                    ownerName={contentProperties.ownerName}
                />
            ),
        });
    }

    return components;
};

export default SidebarCommonComponents;
