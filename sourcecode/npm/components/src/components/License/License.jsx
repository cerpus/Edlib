import React from 'react';
import styled from 'styled-components';
import useTranslation from '../../hooks/useTranslation';
import byUrl, { ReactComponent as test } from './icons/by.svg';
import byNcUrl from './icons/by-nc.svg';
import byNcNdUrl from './icons/by-nc-nd.svg';
import byNcSaUrl from './icons/by-nc-sa.svg';
import byNdUrl from './icons/by-nd.svg';
import bySaUrl from './icons/by-sa.svg';
import cc0Url from './icons/cc0.svg';
import pdmUrl from './icons/pdm.svg';
import zeroUrl from './icons/zero.svg';
import pdUrl from './icons/pd.svg';
import ndUrl from './icons/nd.svg';
import saUrl from './icons/sa.svg';
import ncJpUrl from './icons/nc-jp.svg';
import ncEuUrl from './icons/nc-eu.svg';
import ncUrl from './icons/nc.svg';
import ccUrl from './icons/cc.svg';
import remixUrl from './icons/remix.svg';
import shareUrl from './icons/share.svg';

const licenseFileMap = {
    'by': byUrl,
    'by-nc': byNcUrl,
    'by-nc-nd': byNcNdUrl,
    'by-nc-sa': byNcSaUrl,
    'by-nd': byNdUrl,
    'by-sa': bySaUrl,
    'cc0': cc0Url,
    'pdm': pdmUrl,
    'zero': zeroUrl,
    'pd': pdUrl,
    'nd': ndUrl,
    'sa': saUrl,
    'ncJp': ncJpUrl,
    'ncEu': ncEuUrl,
    'nc': ncUrl,
    'cc': ccUrl,
    'remix': remixUrl,
    'share': shareUrl,
};

const StyledLicense = styled.div`
    display: inline-flex;

    & > img {
        max-height: 25px;
    }
`;

const formatLicenseText = (license) => {
    if (!license) {
        return license;
    }

    const lowerCaseLicense = license.toLowerCase();

    const prefixToRemove = 'cc-';
    if (lowerCaseLicense.startsWith(prefixToRemove)) {
        return lowerCaseLicense.substring(prefixToRemove.length);
    }

    return lowerCaseLicense;
};

const License = ({ license }) => {
    const { t } = useTranslation();
    const formattedLicenseText = formatLicenseText(license);
    const licenseBadges = !formattedLicenseText
        ? []
        : formattedLicenseText.split('-');
    const src =
        formattedLicenseText &&
        licenseFileMap[formattedLicenseText.toLowerCase()];

    return (
        <StyledLicense
            title={licenseBadges
                .map((part) => t(`licenses.${part.toUpperCase()}`))
                .join(' - ')}
        >
            {src && <img src={src} alt="" />}
            {!formattedLicenseText && t('Ingen')}
            {!src && formattedLicenseText ? license : ''}
        </StyledLicense>
    );
};

export default License;
