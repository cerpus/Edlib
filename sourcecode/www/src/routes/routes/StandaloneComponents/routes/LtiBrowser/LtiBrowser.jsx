import React from 'react';
import queryString from 'query-string';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import ContentExplorer from '../../components/ContentExplorer';

const LtiBrowser = ({ match }) => {
    const { jwt, language } = React.useMemo(() => {
        const query = queryString.parse(window.location.search);

        return {
            jwt: query.jwt || null,
            config: query.config || null,
            language: query.language || 'en',
        };
    }, []);

    return (
        <div style={{ height: '100vh' }}>
            <EdlibComponentsProvider
                language={language}
                externalJwt={{
                    type: 'external',
                    token: jwt,
                }}
                configuration={{
                    returnLtiLinks: false,
                }}
            >
                <ContentExplorer
                    basePath={match.path}
                    baseUrl={match.url}
                    onAction={(messageType, extras) => {
                        if (messageType === 'onResourceSelected') {
                            const infoUpdated = JSON.parse(
                                JSON.stringify(extras)
                            );
                            window.parent.postMessage(
                                {
                                    resources: [
                                        {
                                            type: 'ltiResourceLink',
                                            url: infoUpdated.url,
                                            title: infoUpdated.title,
                                        },
                                    ],
                                    messageType: 'resourceSelected',
                                },
                                '*'
                            );
                        }
                    }}
                />
            </EdlibComponentsProvider>
        </div>
    );
};

export default LtiBrowser;
