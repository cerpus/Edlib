import React from 'react';
import queryString from 'query-string';
import { EdlibComponentsProvider } from '@cerpus/edlib-components';
import appConfig from '../../../../../config/app.js';
import axios from 'axios';
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
    const [currentJwt, setCurrentJwt] = React.useState(jwt);

    return (
        <div style={{ height: '100vh' }}>
            <EdlibComponentsProvider
                language={language}
                getJwt={async () => {
                    const { data } = await axios.post(
                        `${appConfig.apiUrl}/auth/v3/jwt/refresh`,
                        null,
                        {
                            headers: {
                                Authorization: `Bearer ${currentJwt}`,
                            },
                        }
                    );

                    setCurrentJwt(data.token);
                    return {
                        type: 'internal',
                        token: data.token,
                    };
                }}
                edlibUrl={appConfig.apiUrl}
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
