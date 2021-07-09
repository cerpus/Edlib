import React from 'react';
import queryString from 'query-string';
import {
    EditEdlibResourceModal,
    EdlibComponentsProvider,
} from '@cerpus/edlib-components';
import apiConfig from '../config/api.js';
import axios from 'axios';

const getConfig = (configString) => {
    try {
        return JSON.parse(configString);
    } catch (e) {
        return undefined;
    }
};

const EditResource = () => {
    const {
        jwt,
        config: configString,
        language,
        launchUrl,
    } = React.useMemo(() => {
        const query = queryString.parse(window.location.search);

        return {
            jwt: query.jwt || null,
            config: query.config || null,
            language: query.language || 'en',
            launchUrl: query.launchUrl || null,
        };
    }, []);
    const [currentJwt, setCurrentJwt] = React.useState(jwt);

    const config = React.useMemo(() => getConfig(configString), [configString]);
    return (
        <EdlibComponentsProvider
            language={language}
            getJwt={async () => {
                const { data } = await axios.post(
                    `/auth/v2/jwt/refresh`,
                    null,
                    {
                        headers: {
                            Authorization: `Bearer ${currentJwt}`,
                        },
                    }
                );

                setCurrentJwt(data.authToken);

                return data.authToken;
            }}
            edlibUrl={apiConfig.url}
            configuration={config}
        >
            <EditEdlibResourceModal
                removePadding
                header="Oppdater ressurs"
                ltiLaunchUrl={launchUrl}
                onClose={() => {
                    window.parent.postMessage(
                        {
                            messageType: 'closeEdlibModal',
                        },
                        '*'
                    );
                }}
                onUpdateDone={async (info) => {
                    if (info === null) {
                        window.parent.postMessage(
                            {
                                messageType: 'closeEdlibModal',
                            },
                            '*'
                        );
                        return;
                    }
                    window.parent.postMessage(
                        {
                            ...info,
                            messageType: 'resourceSelected',
                        },
                        '*'
                    );
                }}
            />
        </EdlibComponentsProvider>
    );
};

export default EditResource;
