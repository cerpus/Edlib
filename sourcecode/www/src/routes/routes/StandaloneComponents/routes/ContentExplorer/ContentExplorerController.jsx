import React from 'react';

import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import ContentExplorer from '../../components/ContentExplorer';
import useIframeIntegration from '../../../../../hooks/useIframeIntegration';
import { IframeStandaloneProvider } from '../../../../../contexts/IframeStandalone';

const ContentExplorerController = ({ match }) => {
    const iframeIntegration = useIframeIntegration();

    if (!iframeIntegration) {
        return <div>missing parameters</div>;
    }

    const { queryParams, nonce, onAction } = iframeIntegration;

    return (
        <IframeStandaloneProvider basePath={match.url}>
            <EdlibComponentsProvider
                externalJwt={{
                    type: 'external',
                    token: queryParams.jwt,
                }}
                configuration={JSON.parse(queryParams.configuration)}
                language={queryParams.language}
            >
                <ContentExplorer
                    match={match}
                    nonce={nonce}
                    onAction={onAction}
                    basePath={match.path}
                    baseUrl={match.url}
                />
            </EdlibComponentsProvider>
        </IframeStandaloneProvider>
    );
};

export default ContentExplorerController;
