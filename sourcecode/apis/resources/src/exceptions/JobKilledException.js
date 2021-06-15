class JobKilledException extends Error {
    constructor() {
        super('Job killed by client');
    }
}

export default JobKilledException;
