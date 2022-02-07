import React from 'react';
import styled from 'styled-components';
import useTranslation from '../../hooks/useTranslation';
import by from '!file-loader!./icons/by.svg';
import byNc from '!file-loader!./icons/by-nc.svg';
import byNcNd from '!file-loader!./icons/by-nc-nd.svg';
import byNcSa from '!file-loader!./icons/by-nc-sa.svg';
import byNd from '!file-loader!./icons/by-nd.svg';
import bySa from '!file-loader!./icons/by-sa.svg';
import cc0 from '!file-loader!./icons/cc0.svg';
import pdm from '!file-loader!./icons/pdm.svg';
import zero from '!file-loader!./icons/zero.svg';
import pd from '!file-loader!./icons/pd.svg';
import nd from '!file-loader!./icons/nd.svg';
import sa from '!file-loader!./icons/sa.svg';
import ncJp from '!file-loader!./icons/nc-jp.svg';
import ncEu from '!file-loader!./icons/nc-eu.svg';
import nc from '!file-loader!./icons/nc.svg';
import cc from '!file-loader!./icons/cc.svg';
import remix from '!file-loader!./icons/remix.svg';
import share from '!file-loader!./icons/share.svg';

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
