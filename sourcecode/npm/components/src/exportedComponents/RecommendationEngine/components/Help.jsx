import React from 'react';
import Tooltip from '@material-ui/core/Tooltip';
import { HelpOutline } from '@material-ui/icons';
import useTranslation from '../../../hooks/useTranslation';
import { withStyles } from '@material-ui/core/styles';

const HtmlTooltip = withStyles((theme) => ({
    tooltip: {
        maxWidth: 400,
        fontSize: theme.typography.pxToRem(16),
        border: '1px solid #dadde9',
    },
}))(Tooltip);

const Help = () => {
    const { t } = useTranslation();

    return (
        <span>
            <HtmlTooltip
                disableFocusListener
                disableTouchListener
                title={
                    <div style={{ fontSize: '16px', lineHeight: '20px' }}>
                        <p>
                            <strong>{t('Foreslått innhold')} (beta)</strong>
                        </p>
                        <p>{t('recommendedContentInfo')}</p>
                    </div>
                }
            >
                <HelpOutline fontSize="inherit" style={{ marginBottom: -3 }} />
            </HtmlTooltip>
        </span>
    );
};

export default Help;
