import React from 'react';
import Job from './Job.jsx';
import request from '../../helpers/request.js';

const JobContainer = ({ name, startUrl, statusUrl }) => {
    const [status, setStatus] = React.useState({
        loading: false,
        error: false,
        done: false,
        message: '',
    });
    const [currentJobId, setCurrentJobId] = React.useState(null);

    const errorHandler = React.useCallback(
        (error) => {
            console.log(error);
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

    return <Job start={start} status={status} name={name} />;
};

export default JobContainer;
