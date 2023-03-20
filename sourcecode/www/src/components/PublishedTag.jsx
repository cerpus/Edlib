import React from 'react';
import _ from 'lodash';
import useTranslation from '../hooks/useTranslation.js';
import { Chip } from '@mui/material';
import { Visibility, VisibilityOff, WarningAmber } from '@mui/icons-material';

const PublishedTag = ({ isPublished, isDraft }) => {
    const { t } = useTranslation();

    return (
        <div>
            <Chip
                icon={
                    isDraft ? (
                        <WarningAmber />
                    ) : isPublished ? (
                        <Visibility />
                    ) : (
                        <VisibilityOff />
                    )
                }
                label={_.capitalize(
                    isDraft
                        ? t('draft')
                        : isPublished
                        ? t('published')
                        : t('unpublished')
                )}
                size="small"
                sx={{
                    backgroundColor: isDraft
                        ? '#FFECB3'
                        : isPublished
                        ? '#A8E994'
                        : null,
                    fontSize: '0.875rem !important',
                    fontWeight: '400',
                    fontFamily: "'Lato', sans-serif",
                }}
            />
        </div>
    );
};

export default PublishedTag;
