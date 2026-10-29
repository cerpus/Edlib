import React from 'react';
import PropTypes from 'prop-types';
import {FormattedMessage} from "react-intl";
import Switch from "@material-ui/core/Switch";

const ModifyCss = ({modifyCss, onChange}) => {
    return (
        <div className="modifyCss-container">
            <FormattedMessage id="SIDEBAR.MODIFY_CSS_OF_SUB_H5PS_EXPLANATION" />
            <Switch
                checked={modifyCss}
                onChange={() => onChange(!modifyCss)}
                color="primary"
            />
        </div>
    );
};

ModifyCss.propTypes = {
    modifyCss: PropTypes.bool,
    onChange: PropTypes.func,
};

ModifyCss.defaultProps = {
    modifyCss: false,
    onChange: () => {},
};

export default ModifyCss;
