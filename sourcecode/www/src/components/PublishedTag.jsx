import React from 'react';
import useTranslation from '../hooks/useTranslation.js';
import { Chip } from '@mui/material';
import { Visibility, VisibilityOff } from '@mui/icons-material';

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
