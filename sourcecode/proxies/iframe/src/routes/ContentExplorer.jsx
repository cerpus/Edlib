import React from 'react';
import queryString from 'query-string';
import { EdlibModal, EdlibComponentsProvider } from '@cerpus/edlib-components';
import apiConfig from '../config/api.js';
import axios from 'axios';

const getConfig = (configString) => {
    try {
        return JSON.parse(configString);
    } catch (e) {
        return undefined;
    }
};

const ContentExplorer = () => {
    const {
        jwt,
        config: configString,
        language,
    } = React.useMemo(() => {
        const query = queryString.parse(window.location.search);

        return {
            jwt: query.jwt || null,
            config: query.config || null,
            language: query.language || 'en',
        };
    }, []);

    const config = React.useMemo(() => getConfig(configString), [configString]);
    return (
        <div style={{ height: '100vh' }}>
            <EdlibComponentsProvider
                language={language}
                getJwt={async () => jwt}
                edlibUrl={apiConfig.url}
                configuration={config}
            >
                <EdlibModal
                    contentOnly
                    isOpen
                    onClose={() => {
                        window.parent.postMessage(
                            {
                                messageType: 'closeEdlibModal',
                            },
                            '*'
                        );
                    }}
                    onResourceSelected={async (info) => {
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
        </div>
    );
};

export default ContentExplorer;
