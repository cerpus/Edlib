import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

const useConfig = () => {
    const { config } = useEdlibComponentsContext();

    return {
        doku: (path) => `${config.urls.dokuUrl}${path}`,
        ndlaApi: (path) => `${config.urls.ndlaApiUrl}${path}`,
        ndlaUrl: config.urls.ndlaUrl,
        ndlaApiUrl: config.urls.ndlaApiUrl,
    };
};

export default useConfig;
