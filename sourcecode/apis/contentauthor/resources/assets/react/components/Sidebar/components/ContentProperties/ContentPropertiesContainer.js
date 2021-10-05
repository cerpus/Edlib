import React, { Component } from 'react';
import PropTypes from 'prop-types';
import ContentPropertiesLayout from './ContentPropertiesLayout';
import { injectIntl, intlShape } from 'react-intl';
import Moment from 'moment';

class ContentPropertiesContainer extends Component {
    static propTypes = {
        id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        createdAt: PropTypes.string,
        format: PropTypes.string,
        intl: intlShape,
        type: PropTypes.string,
        maxScore: PropTypes.number,
        customFields: PropTypes.array,
        ownerName: PropTypes.string,
    };

    static defaultProps = {
        format: 'lll',
    };

    render() {
        const rows = [];
        const {
            id,
            createdAt,
            type,
            intl,
            format,
            maxScore,
            customFields,
            ownerName,
        } = this.props;

        if (id !== null) {
            rows.push({
                label: intl.formatMessage({
                    id: 'CONTENTPROPERTIES.ID',
                }),
                value: id.split('-')[0],
                fullValue: id,
            });
        }

        if (typeof createdAt !== 'undefined' && createdAt !== null) {
            const created = Moment(createdAt);
            created.locale(intl.locale);
            rows.push({
                label: intl.formatMessage({
                    id: 'CONTENTPROPERTIES.CREATEDAT',
                }),
                value: created.format(format),
            });
        }

        if (typeof ownerName !== 'undefined' && ownerName !== null) {
            rows.push({
                label: intl.formatMessage({
                    id: 'CONTENTPROPERTIES.CREATEDBY',
                }),
                value: ownerName,
            });
        }

        if (typeof type !== 'undefined' && type !== null) {
            rows.push({
                label: intl.formatMessage({
                    id: 'CONTENTPROPERTIES.TYPE',
                }),
                value: type,
            });
        }

        if (typeof maxScore !== 'undefined' && maxScore !== null) {
            rows.push({
                label: intl.formatMessage({
                    id: 'CONTENTPROPERTIES.MAXSCORE',
                }),
                value: maxScore,
                cssClassName: 'maxscore-contentproperty',
            });
        }

        if (typeof customFields !== 'undefined' && customFields !== null) {
            customFields.map(field => (
                rows.push({
                    label: field.label,
                    value: field.value,
                })
            ));
        }

        return (
            <ContentPropertiesLayout
                rows={rows}
            />
        );
    }
}

export default injectIntl(ContentPropertiesContainer);
