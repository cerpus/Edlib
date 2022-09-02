'use strict';

import React, { Component } from 'react';
import PropTypes from 'prop-types';

/**
 * @external "react-intl"
 * @see {@link https://github.com/yahoo/react-intl/wiki/API#injectintl|React Intl Wiki: injectIntl}
 */
import { injectIntl } from 'react-intl';
import Tooltip from '../Tooltip';
import EDLLIcon from './Icons/EDLLIcon';

/**
 * Display license icon for a resource
 * Uses icon font from https://creativecommons.org/about/downloads/
 *
 * @module ./components/LicenseIcon
 * @exports LicenseIcon
 *
 * @property {string} license       The license string
 * @property {Number} [size=1]      Size of the icon, number from 1 to 5 inclusive
 */
class LicenseIcon extends Component {
    static displayName = 'LicenseIcon';
    static propTypes = {
        license: PropTypes.string.isRequired,
        size: PropTypes.oneOf([1, 2, 3, 4, 5]),
        className: PropTypes.string,
        addCCIcon: PropTypes.bool,
    };

    static defaultProps = {
        license: '',
        size: 1,
        className: '',
        addCCIcon: false
    };

    getSizeClass() {
        return 'cc-' + this.props.size + 'x';
    };

    /**
     * Build icon representation of the license string
     *
     * @param {string} license
     * @returns {elements[]}
     */
    geAsIcons(license) {
        if (license.length > 0) {
            return license.split('-').map(part => {
                return this.getIcon(part);
            });
        } else {
            return [];
        }
    };

    /**
     * Get class name and title from license part type
     *
     * @param {string} part Part of license to get icon for
     * @returns {{className: boolean|string, title: string}}
     */
    getIcon(part) {
        let className = '';
        let title = '';

        if (part !== null && part !== '') {
            part = part.toUpperCase();
            title = part;
            switch (part) {
                // Share alike
                case 'SA':
                    className = 'cc-sa ';
                    break;

                // Attribution
                case 'BY':
                    className = 'cc-by ';
                    break;

                // CC Circle
                case 'CC':
                    className = 'cc-cc ';
                    break;

                // No derivatives
                case 'ND':
                    className = 'cc-nd ';
                    break;

                // Non commercial
                case 'NC':
                    className = 'cc-nc ';
                    break;

                // Public domain
                case 'CC0':
                    className = 'cc-zero ';
                    break;

                case 'PD':
                case 'PDM':
                    className = 'cc-pd-alt ';
                    break;

                case 'PRIVATE':
                    title = 'COPYRIGHT';
                    className = 'cerpus-Copyright';
                    break;

                case 'COPYRIGHT':
                    className = 'cerpus-Copyright';
                    break;
                case 'EDLL':
                    className = 'edll';
                    break;
            }

            if (className === 'edll') {
                className = className + ' edll-' + this.props.size + 'x';
                let typeText = this.props.intl.formatMessage({
                    id: 'LICENSE.PART.' + part,
                    defaultMessage: ''
                });
                if (typeText !== '') {
                    title += ': ' + typeText;
                }
            } else if (className === 'cerpus-Copyright') {
                className = className + ' facc-' + this.props.size + 'x';
                let typeText = this.props.intl.formatMessage({
                    id: 'LICENSE.PART.' + part,
                    defaultMessage: ''
                });
                if (typeText !== '') {
                    title += ': ' + typeText;
                }
            } else if (className !== '') {
                className = 'cc ' + className + this.getSizeClass();
                let typeText = this.props.intl.formatMessage({
                    id: 'LICENSE.PART.' + part,
                    defaultMessage: ''
                });
                if (typeText !== '') {
                    title += ': ' + typeText;
                }
            }
        }

        return {
            id: part,
            className: className,
            title: title
        };
    };

    getLicense() {
        let license = this.props.license;

        if (this.props.addCCIcon) {
            if (license.includes('BY')) {
                license = 'CC-' + license;
            }
        }

        return license.toLowerCase();
    };

    render() {
        let classes = 'license-icon-container';
        let license = this.getLicense();

        classes += (' ' + this.props.className);

        return (
            <div className={classes}>
                {
                    this.geAsIcons(license).map(icon => {
                        let displayIcon = <i className={icon.className}/>;
                        if (icon.id === "EDLL") {
                            displayIcon = <span><EDLLIcon className={icon.className} /></span>;
                        }
                        return (
                            <Tooltip key={icon.id} message={icon.title}>
                                {displayIcon}
                            </Tooltip>
                        );
                    })
                }
            </div>
        );
    }
}

export default injectIntl(LicenseIcon);
