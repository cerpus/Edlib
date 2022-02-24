import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

const useConfig = () => {
    const { config } = useEdlibComponentsContext();

    return {
        edlib: (path) => `${config.urls.edlibUrl}${path}`,
        edlibFrontend: (path) => `${config.urls.edlibFrontendUrl}${path}`,
        doku: (path) => `${config.urls.dokuUrl}${path}`,
        ndlaApi: (path) => `${config.urls.ndlaApiUrl}${path}`,
        ndlaUrl: config.urls.ndlaUrl,
        ndlaApiUrl: config.urls.ndlaApiUrl,
    };
};

export default useConfig;
