import React from 'react';
import { Button, CircularProgress, LinearProgress } from '@material-ui/core';
import { Alert } from '@material-ui/lab';

const Job = ({ start, status, name, onStop, showKillButton }) => {
    return (
        <>
            <h2>{name}</h2>
            {status.loading && (
                <>
                    <div>
                        <CircularProgress />
                    </div>
                    <div>
                        <LinearProgress
                            variant="determinate"
                            value={status.percentDone || 0}
                        />
                    </div>
                    <div>{status.message}</div>
                    {!status.killingStarted && showKillButton && (
                        <div>
                            <Button variant="contained" onClick={onStop}>
                                Stop
                            </Button>
                        </div>
                    )}
                </>
            )}
            {status.done && <Alert severity="success">{status.message}</Alert>}
            {status.error && <Alert severity="error">{status.message}</Alert>}
            {!status.loading && !status.done && !status.error && (
                <Button onClick={start}>Start</Button>
            )}
        </>
    );
};

export default Job;
