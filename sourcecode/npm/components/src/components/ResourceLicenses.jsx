import React from 'react';
import License from './License';

const ResourceLicenses = ({ licenses }) => {
    return (
        <div style={{ display: 'inline-flex' }}>
            {licenses
                .reduce(
                    (licenses, licenseId) => [
                        ...licenses,
                        ...licenseId.split('-'),
                    ],
                    []
                )
                .map((licenseId) => (
                    <License key={licenseId} license={licenseId} />
                ))}
        </div>
    );
};

export default ResourceLicenses;
