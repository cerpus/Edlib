const getEnv = (key, defaultValue) => {
    if (!window._env_ || !window._env_[key]) {
        return defaultValue;
    }

    return window._env_[key];
};

export default getEnv;
