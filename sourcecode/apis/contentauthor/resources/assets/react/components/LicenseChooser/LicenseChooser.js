'use strict';

import React, { Component } from 'react';
import PropTypes from 'prop-types';

/**
 * @external "react-intl"
 * @see {@link https://github.com/yahoo/react-intl/wiki/API#injectintl|React Intl Wiki: injectIntl}
 */
import { injectIntl, FormattedMessage } from 'react-intl';
import { FormGroup, ControlLabel, FormControl } from 'react-bootstrap';
import { RadioGroup, Radio } from 'react-radio-group';

import HelpIcon from '../HelpIcon';

class LicenseChooser extends Component {
    static propTypes = {
        license: PropTypes.string,
        allowedLicenses: PropTypes.string,
        onChange: PropTypes.func,
        useAttribution: PropTypes.bool,
        attributionName: PropTypes.string,
        attributionTitle: PropTypes.string,
        attributionUrl: PropTypes.string,
    };

    static defaultProps = {
        license: 'BY',
        licenseType: 'CC',
        allowedLicenses: 'COPYRIGHT,CC0,PDM,BY,BY-SA,BY-NC,BY-ND,BY-NC-SA,BY-NC-ND,EDLL',
        useAttribution: true,
        attributionName: '',
        attributionTitle: '',
        attributionUrl: ''
    };


    constructor(props, context) {
        super(props, context);

        let licenseType = '';
        let publicDomainLicense = 'CC0';

        switch (this.props.license) {
            case 'CC0':
            case 'PDM':
                publicDomainLicense = this.props.license;
                licenseType = 'PUBLICDOMAIN';
                break;

            case 'BY':
            case 'BY-SA':
            case 'BY-ND':
            case 'BY-NC':
            case 'BY-NC-SA':
            case 'BY-NC-ND':
                licenseType = 'CC';
                break;

            case 'PRIVATE':
            case 'EDLL':
            case 'COPYRIGHT':
                licenseType = 'EDLL';
                break;
        }

        let sharing = this.getSharingPart(this.props.license);
        let commercial = this.getCommercialPart(this.props.license);

        this.allowedLicenses = this.props.allowedLicenses.split(',');

        this.state = {
            licenseType: licenseType,
            sharing: sharing,
            commercial: commercial,
            publicDomainLicense: publicDomainLicense,
        };

        this.handleLicenseTypeChange = this.handleLicenseTypeChange.bind(this);
        this.handleSharingChange = this.handleSharingChange.bind(this);
        this.handleCommercialChange = this.handleCommercialChange.bind(this);
        this.handleAttributionTitle = this.handleAttributionTitle.bind(this);
        this.handleAttributionName = this.handleAttributionName.bind(this);
        this.handleAttributionUrl = this.handleAttributionUrl.bind(this);
        this.handlePublicDomainLicenseChange = this.handlePublicDomainLicenseChange.bind(this);
    };

    allowedLicenses = null;

    getCommercialPart(license) {
        if (license.includes('NC')) {
            return 'NC';
        }

        return '-';
    }

    handleCommercialChange(value) {
        this.setState({
            commercial: value
        }, this.callOnChange);
    };

    handleSharingChange(value) {
        this.setState({
            sharing: value
        }, this.callOnChange);
    };

    handlePublicDomainLicenseChange(value) {
        this.setState({
            license: value,
            publicDomainLicense: value,
        }, this.callOnChange);
    }

    getSharingPart(license) {
        if (license.includes('SA')) {
            return 'SA';
        }

        if (license.includes('ND')) {
            return 'ND';
        }

        return '-';
    };

    getLicenseString() {
        if (this.state.licenseType === 'PUBLICDOMAIN') { // Public Domain License
            if (['CC0', 'PDM'].indexOf(this.state.license)) {
                return this.state.license;
            }

            return this.state.publicDomainLicense;
        }

        if (this.state.licenseType === 'EDLL') { // Copyright
            return this.state.licenseType;
        }

        const theLicense = ['BY'];

        if (this.state.commercial !== '-') {
            theLicense.push(this.state.commercial);
        }

        if (this.state.sharing !== '-') {
            theLicense.push(this.state.sharing);
        }

        return theLicense.join('-');
    }

    handleLicenseTypeChange(licenseType) {
        let license = this.state.license;
        if (licenseType === 'PUBLICDOMAIN') {
            license = this.state.publicDomainLicense;
        }

        this.setState({
            licenseType: licenseType,
            license: license,
        }, this.callOnChange);
    };

    getAttributionData() {
        let data = {};
        if (this.props.useAttribution) {
            data = {
                attributionName: this.props.attributionName,
                attributionTitle: this.props.attributionTitle,
                attributionUrl: this.props.attributionUrl
            };
        }
        return data;
    };

    getData() {
        return Object.assign({
            license: this.getLicenseString()
        }, this.getAttributionData());
    };

    callOnChange(newValue) {
        if (this.props.onChange) {
            let data = this.getData();
            if (typeof newValue !== 'undefined') {
                data = Object.assign(data, newValue);
            }
            this.props.onChange(data);
        }
    };

    handleAttributionTitle(e) {
        this.callOnChange({
            attributionTitle: e.target.value
        });
    };

    handleAttributionName(e) {
        this.callOnChange({
            attributionName: e.target.value
        });
    };

    handleAttributionUrl(e) {
        this.callOnChange({
            attributionUrl: e.target.value
        });
    }

    getHelpMessage(messageId) {
        return this.props.intl.formatMessage(
            {
                id: messageId,
            }, {
                p: chunks => <p>{chunks}</p>,
                strong: chunks => <strong>{chunks}</strong>,
                nl: <br/>,
            }
        );
    }

    render() {
        let publicDomain = null;
        let createCommons = null;
        let attributionFields = null;
        // let disableCC0 = (this.allowedLicenses.indexOf('CC0') === -1);
        // let disableCopyright = (this.allowedLicenses.indexOf('COPYRIGHT') === -1);

        if (['PUBLICDOMAIN'].indexOf(this.state.licenseType) !== -1) {
            publicDomain = (
                <div>
                    <FormGroup controlId="publicdomain">
                        <ControlLabel className="licensechooser-group-title">
                            <FormattedMessage id="LICENSECHOOSER.PUBLICDOMAIN"/>
                        </ControlLabel>
                        <HelpIcon messageString={this.getHelpMessage('LICENSECHOOSER.PUBLICDOMAIN.HELP')}/>
                        <RadioGroup
                            name="publicdomainlicense"
                            selectedValue={this.state.publicDomainLicense}
                            onChange={this.handlePublicDomainLicenseChange}
                        >
                            <label className="radio-inline">
                                <Radio className="radio-inline" value="CC0"/>
                                <FormattedMessage id="LICENSECHOOSER.PUBLICDOMAIN.CC0"/>
                            </label>
                            <label className="radio-inline">
                                <Radio className="radio-inline" value="PDM"/>
                                <FormattedMessage id="LICENSECHOOSER.PUBLICDOMAIN.PDM"/>
                            </label>
                        </RadioGroup>
                    </FormGroup>
                </div>
            );
        }

        if (this.state.licenseType === 'CC') {
            createCommons = (
                <div>
                    <FormGroup controlId="sharing">
                        <ControlLabel className="licensechooser-group-title">
                            <FormattedMessage id="LICENSECHOOSER.ADAPTIONS"/>
                        </ControlLabel>
                        <HelpIcon messageString={this.getHelpMessage('LICENSECHOOSER.ADAPTIONS-HELP')}/>
                        <RadioGroup
                            name="sharing"
                            selectedValue={this.state.sharing}
                            onChange={this.handleSharingChange}
                        >
                            <label className="radio-inline">
                                <Radio className="radio-inline" value="-"/>
                                <FormattedMessage id="LICENSECHOOSER.YES"/>
                            </label>
                            <label className="radio-inline">
                                <Radio className="radio-inline" value="ND"/>
                                <FormattedMessage id="LICENSECHOOSER.NO"/>
                            </label>
                            <label className="radio-inline">
                                <Radio className="radio-inline" value="SA"/>
                                <FormattedMessage id="LICENSECHOOSER.OPTION-SHAREALIKE"/>
                            </label>
                        </RadioGroup>
                    </FormGroup>

                    <FormGroup controlId="commercial">
                        <ControlLabel className="licensechooser-group-title">
                            <FormattedMessage id="LICENSECHOOSER.COMMERCIAL-USE"/>
                        </ControlLabel>
                        <HelpIcon messageString={this.getHelpMessage('LICENSECHOOSER.COMMERCIAL-USE-HELP')}/>
                        <RadioGroup
                            name="commercial"
                            selectedValue={this.state.commercial}
                            onChange={this.handleCommercialChange}
                        >
                            <label className="radio-inline">
                                <Radio value="-" className="radio-inline"/>
                                <FormattedMessage id="LICENSECHOOSER.YES"/>
                            </label>
                            <label className="radio-inline">
                                <Radio value="NC" className="radio-inline"/>
                                <FormattedMessage id="LICENSECHOOSER.NO"/>
                            </label>
                        </RadioGroup>
                    </FormGroup>
                </div>
            );
        }

        if (this.props.useAttribution && this.state.licenseType !== 'EDLL') {
            attributionFields = (
                <div className="licensechooser-attribution-container">
                    <ControlLabel className="licensechooser-group-title">
                        <FormattedMessage id="LICENSECHOOSER.ATTRIBUTION-TITLE"/>
                    </ControlLabel>
                    <HelpIcon messageString={this.getHelpMessage('LICENSECHOOSER.ATTRIBUTION-HELP')}/>

                    <FormGroup>
                        <ControlLabel className="licensechooser-input-title">
                            <FormattedMessage id="LICENSECHOOSER.ATTRIBUTION-FIELD-TITLE"/>
                        </ControlLabel>
                        <FormControl
                            type="text"
                            value={this.props.attributionTitle}
                            placeholder={this.props.intl.formatMessage({ id: 'LICENSECHOOSER.ATTRIBUTION-FIELD-TITLE-PLACEHOLDER' })}
                            onChange={this.handleAttributionTitle}
                        />
                    </FormGroup>

                    <FormGroup>
                        <ControlLabel className="licensechooser-input-title">
                            <FormattedMessage id="LICENSECHOOSER.ATTRIBUTION-FIELD-NAME"/>
                        </ControlLabel>
                        <FormControl
                            type="text"
                            value={this.props.attributionName}
                            placeholder={this.props.intl.formatMessage({ id: 'LICENSECHOOSER.ATTRIBUTION-FIELD-NAME-PLACEHOLDER' })}
                            onChange={this.handleAttributionName}
                        />
                    </FormGroup>

                    <FormGroup>
                        <ControlLabel className="licensechooser-input-title">
                            <FormattedMessage id="LICENSECHOOSER.ATTRIBUTION-FIELD-URL"/>
                        </ControlLabel>
                        <FormControl
                            type="text"
                            value={this.props.attributionUrl}
                            placeholder={this.props.intl.formatMessage({ id: 'LICENSECHOOSER.ATTRIBUTION-FIELD-URL-PLACEHOLDER' })}
                            onChange={this.handleAttributionUrl}
                        />
                    </FormGroup>
                </div>
            );
        }

        return (
            <div>
                <ControlLabel className="licensechooser-group-title">
                    <FormattedMessage id="LICENSECHOOSER.RESTRICTION-LEVEL"/>
                </ControlLabel>
                <HelpIcon messageString={this.getHelpMessage('LICENSECHOOSER.RESTRICTION-LEVEL-HELP')}/>
                <FormGroup>
                    <RadioGroup
                        name="restriction"
                        selectedValue={this.state.licenseType}
                        onChange={this.handleLicenseTypeChange}
                    >
                        <label className="radio-inline">
                            <Radio className="radio-inline" value="PUBLICDOMAIN"/>
                            <FormattedMessage id="LICENSECHOOSER.PUBLIC-DOMAIN"/>
                        </label>

                        <label className="radio-inline">
                            <Radio className="radio-inline" value="CC"/>
                            <FormattedMessage id="LICENSECHOOSER.CREATIVE-COMMONS"/>
                        </label>

                        <label className="radio-inline">
                            <Radio className="radio-inline" value="EDLL"/>
                            <FormattedMessage id="LICENSECHOOSER.EDLL"/>
                        </label>
                    </RadioGroup>
                </FormGroup>

                {publicDomain}
                {createCommons}
                {attributionFields}
            </div>
        );
    }
}

export default injectIntl(LicenseChooser);
