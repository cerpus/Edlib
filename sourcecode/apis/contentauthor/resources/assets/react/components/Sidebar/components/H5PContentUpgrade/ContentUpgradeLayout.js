import React from 'react';
import PropTypes from 'prop-types';
import { Button, ControlLabel, FormControl, FormGroup, ProgressBar } from 'react-bootstrap';
import ModalWindow from '../../../ModalWindow';

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
    translations,
    selectedLibraryId,
}) => {
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
                            <option value="">{translations.selectVersion}</option>
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
                                {translations.confirmation}
                            </div>
                        }
                        footer={
                            <div>
                                <Button onClick={onConfirm} bsStyle="success">
                                    {translations.yes}
                                </Button>
                                <Button onClick={onToggleConfirm} bsStyle="danger">
                                    {translations.no}
                                </Button>
                            </div>
                        }
                    >
                        {translations.upgradeConfirmation}
                    </ModalWindow>
                </>
            )}
            {(inProgress === true || upgradeComplete === true) && (
                <>
                    <ControlLabel>{translations.progress}</ControlLabel>
                    <ProgressBar
                        now={percentProgress}
                        label={`${percentProgress}%`}
                    />
                </>
            )}
            {upgradeComplete === true && (
                <div className="contentupgrade-complete">
                    <div>{translations.undoTextHTML}</div>
                    <Button
                        bsStyle="danger"
                        onClick={onUndoUpgrade}
                    >
                        {translations.undo}
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
    translations: PropTypes.shape({
        version: PropTypes.string.isRequired,
        upgrade: PropTypes.string.isRequired,
        confirmation: PropTypes.string.isRequired,
        yes: PropTypes.string.isRequired,
        no: PropTypes.string.isRequired,
        upgradeConfirmation: PropTypes.string.isRequired,
        progress: PropTypes.string.isRequired,
        undoText: PropTypes.string.isRequired,
        undo: PropTypes.string.isRequired,
        selectVersion: PropTypes.string.isRequired,
        undoTextHTML: PropTypes.node.isRequired,
    }).isRequired,
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
