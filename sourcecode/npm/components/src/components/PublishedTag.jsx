import React from 'react';
import useTranslation from '../hooks/useTranslation.js';
import { Chip } from '@material-ui/core';
import { Visibility, VisibilityOff } from '@material-ui/icons';

const PublishedTag = ({ isPublished }) => {
    const { t } = useTranslation();
    return (
        <div>
            <Chip
                icon={isPublished ? <Visibility /> : <VisibilityOff />}
                label={isPublished ? t('Publisert') : t('Avpublisert')}
                size="small"
            />
        </div>
    );
};

export default PublishedTag;
