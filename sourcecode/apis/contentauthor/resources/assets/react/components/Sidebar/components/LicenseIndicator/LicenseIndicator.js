

import React, { Component } from 'react';
import PropTypes from 'prop-types';

/**
 * @external "react-intl"
 * @see {@link https://github.com/yahoo/react-intl/wiki/API#injectintl|React Intl Wiki: injectIntl}
 */
import { injectIntl, FormattedMessage } from 'react-intl';
import { Button } from 'react-bootstrap';

import ModalWindow from 'components/ModalWindow';
import LicenseChooser from 'components/LicenseChooser';
import LicenseIcon from 'components/LicenseIcon';
import LicenseText from 'components/LicenseText';

/**
 * @property {string} license                       License string
 * @property {number} [size=3]                      Size of the icons, 1-5
 * @property {string} allowedLicenses               (Not in use) String of allowed licenses
 * @property {bool} [useAttribution=false]          Show the attribution fields
 * @property {function} [onChange=null]             Function to call when user selects a license
 *                                                  If undefined or null, the license cannot be changed i.e. read-only
 * @property {bool} [useOldCopyrightName=false]     Returns 'PRIVATE' for 'COPYRIGHT' license
 */
class LicenseIndicator extends Component {
    static propTypes = {
        license: PropTypes.string,
        size: PropTypes.number,
        allowedLicenses: PropTypes.string,
        useAttribution: PropTypes.bool,
        onChange: PropTypes.func,
        useOldCopyrightName: PropTypes.bool,
    };

    constructor(props) {
        super(props);

        this.state = {
            license: this.props.license,
            newLicense: this.props.license,
            show: false,
            attributionName: '',
            attributionTitle: '',
            attributionUrl: '',
        };

        this.handleSetLicense = this.handleSetLicense.bind(this);
        this.handleLicenseChange = this.handleLicenseChange.bind(this);
        this.handleShowLicenseChooser = this.handleShowLicenseChooser.bind(this);
        this.handleHideLicenseChooser = this.handleHideLicenseChooser.bind(this);
    }

    static defaultProps = {
        license: 'EDLL',
        size: 1,
        allowedLicenses: 'COPYRIGHT,CC0,PDM,BY,BY-SA,BY-NC,BY-ND,BY-NC-SA,BY-NC-ND,EDLL',
        useAttribution: false,
        onChange: null,
        useOldCopyrightName: false,
    };

    handleShowLicenseChooser() {
        this.setState({
            show: true,
        });
    }

    handleHideLicenseChooser() {
        this.setState({
            show: false,
        });
    }

    handleSetLicense() {
        this.handleHideLicenseChooser();
        if (this.props.onChange) {
            let license = this.state.newLicense;
            if (this.props.useOldCopyrightName && license === 'COPYRIGHT') {
                license = 'PRIVATE';
            }
            this.props.onChange(
                license,
                this.state.attributionName,
                this.state.attributionTitle,
                this.state.attributionUrl
            );
        }
    }

    handleLicenseChange(data) {
        this.setState({
            newLicense: data.license,
            attributionName: data.attributionName,
            attributionTitle: data.attributionTitle,
            attributionUrl: data.attributionUrl,
        });
    }

    render() {
        let selectLicense = null;

        if (this.props.onChange) {
            selectLicense = (
                <div>
                    <Button onClick={this.handleShowLicenseChooser} bsStyle="success" block={true}>
                        <i className="fa fa-plus licenseindicator-button-choose-icon" />
                        <FormattedMessage id="LICENSEINDICATOR.BUTTON-TEXT" />
                    </Button>

                    <ModalWindow
                        show={this.state.show}
                        onHide={this.handleHideLicenseChooser}
                        header={
                            <div>
                                <FormattedMessage id="LICENSEINDICATOR.BUTTON-TEXT" />
                                <LicenseIcon
                                    license={this.state.newLicense}
                                    size={this.props.size}
                                    className="pull-right"
                                    addCCIcon={true}
                                />
                            </div>
                        }
                        footer={
                            <Button onClick={this.handleSetLicense} bsStyle="primary">
                                <FormattedMessage id="LICENSEINDICATOR.USE-LICENSE" />
                            </Button>
                        }
                    >
                        <LicenseChooser
                            license={this.state.newLicense}
                            allowedLicenses={this.props.allowedLicenses}
                            onChange={this.handleLicenseChange}
                            useAttribution={this.props.useAttribution}
                            attributionName={this.state.attributionName}
                            attributionTitle={this.state.attributionTitle}
                            attributionUrl={this.state.attributionUrl}
                        />
                    </ModalWindow>
                </div>
            );
        }

        return (
            <>
                <div className="text-center">
                    <LicenseIcon license={this.props.license} size={this.props.size} addCCIcon={true} />
                    <LicenseText license={this.props.license} />
                </div>
                {selectLicense}
                <input type="hidden" name="license" value={this.state.newLicense} />
            </>
        );
    }
}

export default injectIntl(LicenseIndicator);
