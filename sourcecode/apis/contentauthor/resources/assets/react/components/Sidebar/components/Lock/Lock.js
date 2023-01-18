import React, { useEffect, useState } from 'react';
import { FormattedMessage } from 'react-intl';
import PropTypes from 'prop-types';
import Button from '@material-ui/core/Button';
import Axios from '../../../../utils/axiosSetup';
import LockIcon from '@material-ui/icons/Lock';
import Alert from '@material-ui/lab/Alert';
import AlertTitle from '@material-ui/lab/AlertTitle';

const Lock = ({pollUrl, editor, lockReleased, editUrl: currentEditUrl }) => {
    const [isLocked, setIsLocked] = useState(true);
    const [editUrl, setEditUrl] = useState();
    let pollLockStatusInterval;

    const pollStatus = () => Axios
        .get(pollUrl)
        .then(({ data: { isLocked = true, editUrl } }) => {
            if (isLocked !== true) {
                setIsLocked(false);
                if (editUrl === currentEditUrl) {
                    lockReleased();
                } else {
                    setEditUrl(editUrl);
                }
                clearInterval(pollLockStatusInterval);
            }
        });

    useEffect(() => {
        pollLockStatusInterval = setInterval(pollStatus, 15000);
        return () => {
            if (pollLockStatusInterval){
                clearInterval(pollLockStatusInterval);
            }
        };
    }, []);

    return (
        <div>
            {isLocked && (
                <>
                    <div className="btn btn-danger btn-block locked">
                        <LockIcon /> <FormattedMessage id="LOCK.LOCKED" />
                    </div>
                    <Alert severity="warning">
                        <AlertTitle>
                            <FormattedMessage id="LOCK.LOCKEDFOREDITING" />
                        </AlertTitle>
                        <FormattedMessage
                            id="LOCK.LOCKEDWILLEXPIREMESSAGE"
                            values={{
                                name: editor,
                            }}
                        />
                    </Alert>
                </>
            )}
            {!isLocked && (
                <>
                    <Button
                        onClick={() => location.href = editUrl}
                        type="button"
                        className="gotoEditBtn"
                        variant="outlined"
                        fullWidth
                    >
                        <FormattedMessage id="LOCK.GOTONEXTVERSION" />
                    </Button>
                    <Alert severity="info">
                        <FormattedMessage id="LOCK.THECONTENTHASBEENUPDATED" />
                    </Alert>
                </>
            )}
        </div>
    );
};

Lock.propTypes = {
    expires: PropTypes.number,
    pollUrl: PropTypes.string,
    editUrl: PropTypes.string,
    editor: PropTypes.string,
    lockReleased: PropTypes.func,
};

export default Lock;
