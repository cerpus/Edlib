import React from 'react';
import PropTypes from 'prop-types';
import {FormattedMessage} from "react-intl";
import Switch from "@material-ui/core/Switch";
import Sharing from "../Sharing";

const ModifyCss = () => {
   <div className="modifyCss-container">
        <FormattedMessage id="SHARINGCOMPONENT.SHOWINSHAREDCONTENT" />
        <Switch
            checked={modifyCss}
            onChange={() => onChange(!modifyCss)}
            color="primary"
        />
    </div>
};

ModifyCss.propTypes = {
    modifyCss: PropTypes.bool,
    onChange: PropTypes.func,
};

export default ModifyCss;
