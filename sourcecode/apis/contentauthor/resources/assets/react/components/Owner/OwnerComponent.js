import React from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';

import OwnerLayout from './OwnerLayout';

class OwnerComponent extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            userName : props.userName,
            userEmail: props.userEmail,
        }
    }

    render() {
        return <OwnerLayout
            userName={this.state.userName}
            userEmail={this.state.userEmail}
            name={<FormattedMessage id="OWNERCOMPONENT.NAME"/>}
            email={<FormattedMessage id="OWNERCOMPONENT.EMAIL"/>}
        />
    }
}

export default injectIntl(OwnerComponent);
