import React from 'react';
import InfoBox from '../InfoBox';
import { FormattedMessage } from 'react-intl';

const MillionaireHeader = ({}) => {
    return (
        <InfoBox
            infoText={<FormattedMessage id="MILLIONAIRE.TITLE" />}
            iconUrl="/graphical/MillionaireIcon.png"
            type="header"
        />
    );
}

export default MillionaireHeader;
