import React from 'react';
import Job from './Job.jsx';
import request from '../../helpers/request.js';
import useFetch from '../../hooks/useFetch.jsx';

const JobContainer = ({
    name,
    startUrl,
    statusUrl,
    showKillButton = false,
    resumable = false,
}) => {
    const [status, setStatus] = React.useState({
        loading: false,
        error: false,
        done: false,
        message: '',
    });
    const [currentJobId, setCurrentJobId] = React.useState(null);

    const { response } = useFetch(
        startUrl + '/resumable',
        'GET',
        React.useMemo(() => ({}), []),
        !resumable
    );

    const errorHandler = React.useCallback(
        (error) => {
            setCurrentJobId(null);
            setStatus({
                error: true,
                message: error.message,
            });
        },
        [setCurrentJobId, setStatus]
    );

    const start = React.useCallback(() => {
        setStatus({
            loading: true,
            error: false,
        });

        request(startUrl, 'POST')
            .then(({ jobId }) => {
                setCurrentJobId(jobId);
            })
            .catch(errorHandler);
    }, []);

    const onResume = React.useCallback(() => {
        setStatus({
            loading: true,
            error: false,
        });

        request(statusUrl(response.id) + '/resume', 'POST')
            .then(({ jobId }) => {
                setCurrentJobId(jobId);
            })
            .catch(errorHandler);
    }, [response]);

    const onStop = React.useCallback(() => {
        setStatus({
            ...status,
            killingStarted: true,
        });

        request(statusUrl(currentJobId), 'DELETE').catch(errorHandler);
    }, [currentJobId, status]);

    React.useEffect(() => {
        if (!currentJobId) {
            return;
        }

        const interval = setInterval(() => {
            request(statusUrl(currentJobId), 'GET')
                .then((job) => {
                    if (job.failedAt) {
                        setStatus({
                            loading: false,
                            error: true,
                            done: false,
                            message: job.message,
                        });
                        setCurrentJobId(null);
                    } else if (job.doneAt) {
                        setStatus({
                            loading: false,
                            error: false,
                            done: true,
                            message: job.message,
                        });
                        setCurrentJobId(null);
                    } else {
                        setStatus({
                            loading: true,
                            error: false,
                            done: false,
                            message: job.message,
                            percentDone: job.percentDone,
                        });
                    }
                })
                .catch(errorHandler);
        }, 1000);
        return () => clearInterval(interval);
    }, [currentJobId]);

    return (
        <Job
            start={start}
            status={status}
            name={name}
            onStop={onStop}
            onResume={onResume}
            showKillButton={showKillButton}
            showResumeButton={!!response}
        />
    );
};

export default JobContainer;
