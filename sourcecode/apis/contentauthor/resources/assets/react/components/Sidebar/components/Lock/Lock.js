import React, { useEffect, useState } from 'react';
import { FormattedMessage, injectIntl, intlShape } from 'react-intl';
import PropTypes from 'prop-types';
import { Alert, Button } from '@cerpus/ui';
import Axios from 'utils/axiosSetup';
import { Lock as LockIcon } from '@material-ui/icons';

const Lock = ({ intl, pollUrl, editor, lockReleased, editUrl: currentEditUrl }) => {
    if (typeof intl === 'undefined') {
        return null;
    }

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
                    <div
                        className="btn btn-danger btn-block locked"
                    >
                        <LockIcon /> {intl.formatMessage({ id: 'LOCK.LOCKED' })}
                    </div>
                    <Alert
                        color="danger"
                    >
                        <FormattedMessage id="LOCK.LOCKEDFOREDITING" />
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
                        type="tertiary"
                        className="gotoEditBtn"
                    >
                        <FormattedMessage id="LOCK.GOTONEXTVERSION" />
                    </Button>
                    <Alert
                        color="warning"
                    >
                        <FormattedMessage id="LOCK.THECONTENTHASBEENUPDATED" />
                    </Alert>
                </>
            )}
        </div>
    );
};

Lock.propTypes = {
    intl: intlShape,
    expires: PropTypes.number,
    pollUrl: PropTypes.string,
    editUrl: PropTypes.string,
    editor: PropTypes.string,
    lockReleased: PropTypes.func,
};

export default injectIntl(Lock);
