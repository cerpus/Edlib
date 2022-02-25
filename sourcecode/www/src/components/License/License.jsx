import React from 'react';
import styled from 'styled-components';
import useTranslation from '../../hooks/useTranslation';
import by from './icons/by.svg';
import byNc from './icons/by-nc.svg';
import byNcNd from './icons/by-nc-nd.svg';
import byNcSa from './icons/by-nc-sa.svg';
import byNd from './icons/by-nd.svg';
import bySa from './icons/by-sa.svg';
import cc0 from './icons/cc0.svg';
import pdm from './icons/pdm.svg';
import zero from './icons/zero.svg';
import pd from './icons/pd.svg';
import nd from './icons/nd.svg';
import sa from './icons/sa.svg';
import ncJp from './icons/nc-jp.svg';
import ncEu from './icons/nc-eu.svg';
import nc from './icons/nc.svg';
import cc from './icons/cc.svg';
import remix from './icons/remix.svg';
import share from './icons/share.svg';
import edll from './icons/edll.svg';

const licenseFileMap = {
    'by': by,
    'by-nc': byNc,
    'by-nc-nd': byNcNd,
    'by-nc-sa': byNcSa,
    'by-nd': byNd,
    'by-sa': bySa,
    'cc0': cc0,
    'pdm': pdm,
    zero,
    pd,
    nd,
    sa,
    ncJp,
    ncEu,
    nc,
    cc,
    remix,
    share,
    edll,
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
