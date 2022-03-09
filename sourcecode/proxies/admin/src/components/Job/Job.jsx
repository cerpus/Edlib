import React from 'react';
import {
    Box,
    Button,
    CircularProgress,
    LinearProgress,
    TextareaAutosize,
} from '@material-ui/core';
import { Alert } from '@material-ui/lab';

const Job = ({
    start,
    status,
    name,
    onStop,
    showKillButton,
    showResumeButton,
    showInput,
    onResume,
    data,
    setData,
}) => {
    return (
        <>
            <h2>{name}</h2>
            {showInput && (
                <Box>
                    <TextareaAutosize
                        value={data}
                        onChange={(e) => setData(e.target.value)}
                    />
                </Box>
            )}
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
            {!status.loading && (
                <Button
                    onClick={start}
                    disabled={showInput && data === ''}
                    variant="contained"
                    color="primary"
                >
                    Start
                </Button>
            )}
            {!status.loading &&
                !status.done &&
                !status.error &&
                showResumeButton && <Button onClick={onResume}>Resume</Button>}
        </>
    );
};

export default Job;
