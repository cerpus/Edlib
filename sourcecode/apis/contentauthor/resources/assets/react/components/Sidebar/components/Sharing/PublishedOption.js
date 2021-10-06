import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import classnames from 'clsx';

const PublishedOption = ({ isPublished, onToggle }) => {
    return (
        <div
            className={classnames('panel panel-default', {
                'sharing': isPublished,
                'not-sharing': !isPublished,
            })}
        >
            <div className="panel-heading">
                <FormattedMessage id="SHARINGCOMPONENT.ISPUBLISHED" />
            </div>
            <div className="panel-body">
                <div className="sharinglayout-container">
                    <div>
                        <label>
                            <input
                                type="radio"
                                name="isPublished"
                                value="1"
                                checked={isPublished}
                                onChange={() => onToggle()}
                                className="sharinglayout-input"
                            />
                            <FormattedMessage id="SHARINGCOMPONENT.YES" />
                        </label>
                    </div>
                    <div>
                        <label>
                            <input
                                type="radio"
                                name="isPublished"
                                value="0"
                                checked={!isPublished}
                                onChange={() => onToggle()}
                                className="sharinglayout-input"
                            />
                            <FormattedMessage id="SHARINGCOMPONENT.NO" />
                        </label>
                    </div>
                </div>
            </div>
        </div>
    );
};

PublishedOption.propTypes = {
    isPublished: PropTypes.bool,
    onToggle: PropTypes.func,
};

export default PublishedOption;
