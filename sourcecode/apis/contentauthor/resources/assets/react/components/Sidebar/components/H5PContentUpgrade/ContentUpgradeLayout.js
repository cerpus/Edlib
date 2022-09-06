import React from 'react';
import PropTypes from 'prop-types';
import { Button, ControlLabel, FormControl, FormGroup, ProgressBar } from 'react-bootstrap';
import ModalWindow from '../../../ModalWindow';
import { FormattedMessage, useIntl } from 'react-intl';

const ContentNoUpgrades = ({
    noUpgradeAvailable,
}) => {
    return (
        <div>
            {noUpgradeAvailable}
        </div>
    );
};

ContentNoUpgrades.propTypes = {
    noUpgradeAvailable: PropTypes.node.isRequired,
};

const ContentUpgradeLayout = ({
    onClick,
    libraries,
    showConfirm,
    onConfirm,
    upgradeComplete,
    onToggleConfirm,
    onUndoUpgrade,
    percentProgress,
    inProgress,
    selectedLibraryId,
}) => {
    const { formatMessage } = useIntl();

    return (
        <div className="upgradeVersionContainer">
            {(upgradeComplete !== true && inProgress !== true) && (
                <>
                    <FormGroup controlId="formControlsSelect">
                        <FormControl
                            componentClass="select"
                            onChange={onClick}
                            value={selectedLibraryId}
                        >
                            <option value="">
                                {formatMessage({id:'H5PCONTENTUPGRADE.SELECTVERSION'})}
                            </option>
                            {libraries.map((library, index) => {
                                return (
                                    <option key={index} value={library.id}>{library.version}</option>
                                );
                            })}
                        </FormControl>
                    </FormGroup>
                    <ModalWindow
                        show={showConfirm}
                        onHide={onToggleConfirm}
                        header={
                            <div>
                                <FormattedMessage id="H5PCONTENTUPGRADE.CONFIRMATION"/>
                            </div>
                        }
                        footer={
                            <div>
                                <Button onClick={onConfirm} bsStyle="success">
                                    <FormattedMessage id="H5PCONTENTUPGRADE.YES"/>
                                </Button>
                                <Button onClick={onToggleConfirm} bsStyle="danger">
                                    <FormattedMessage id="H5PCONTENTUPGRADE.NO"/>
                                </Button>
                            </div>
                        }
                    >
                        <FormattedMessage id="H5PCONTENTUPGRADE.UPGRADE-CONFIRMATION"/>
                    </ModalWindow>
                </>
            )}
            {(inProgress === true || upgradeComplete === true) && (
                <>
                    <ControlLabel>
                        <FormattedMessage id="H5PCONTENTUPGRADE.PROGRESS"/>
                    </ControlLabel>
                    <ProgressBar
                        now={percentProgress}
                        label={`${percentProgress}%`}
                    />
                </>
            )}
            {upgradeComplete === true && (
                <div className="contentupgrade-complete">
                    <div>
                        <FormattedMessage id="H5PCONTENTUPGRADE.UNDO-TEXT-1"/>
                        <br/>
                        <FormattedMessage id="H5PCONTENTUPGRADE.UNDO-TEXT-2"/>
                    </div>
                    <Button
                        bsStyle="danger"
                        onClick={onUndoUpgrade}
                    >
                        <FormattedMessage id="H5PCONTENTUPGRADE.UNDO"/>
                    </Button>
                </div>
            )}
        </div>
    );
};

ContentUpgradeLayout.propTypes = {
    onClick: PropTypes.func.isRequired,
    libraries: PropTypes.array,
    showConfirm: PropTypes.bool,
    onConfirm: PropTypes.func,
    upgradeComplete: PropTypes.bool,
    onToggleConfirm: PropTypes.func,
    onUndoUpgrade: PropTypes.func,
    percentProgress: PropTypes.number,
    inProgress: PropTypes.bool,
    selectedLibraryId: PropTypes.string,
};

ContentUpgradeLayout.defaultProps = {
    showConfirm: false,
    libraries: [],
    readyForUpgrade: false,
};

export {
    ContentUpgradeLayout as default,
    ContentNoUpgrades,
};
