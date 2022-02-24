import React from 'react';
import { Spinner } from '@cerpus/ui';
import ModalHeader from './ModalHeader';
import useIframeIntegration from '../../../../../hooks/useIframeIntegration';
import appConfig from '../../../../../config/app';
import { useEdlibResource } from '../../../../../hooks/requests/useResource';
import useFetchWithToken from '../../../../../hooks/useFetchWithToken';
import ResourceEditor from '../../../../../components/ResourceEditor';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';

const EditEdlibResourceModal = ({ ltiLaunchUrl, onAction }) => {
    const createResourceLink = useEdlibResource();
    const { response, ltiError, ltiLoading } = useFetchWithToken(
        `${appConfig.apiUrl}/lti/v2/lti/convert-launch-url`,
        'GET',
        React.useMemo(
            () => ({
                query: {
                    launchUrl: ltiLaunchUrl,
                },
            }),
            [ltiLaunchUrl]
        )
    );

    if (ltiLoading || !response) {
        return <Spinner />;
    }

    if (ltiError) {
        return <div>Noe skjedde</div>;
    }

    return (
        <ResourceEditor
            edlibId={response.id}
            onResourceReturned={async ({ resourceId, resourceVersionId }) => {
                if (
                    resourceId === response.id &&
                    resourceVersionId === response.version.id
                ) {
                    onAction('onUpdateDone', null);
                    onAction('onLtiResourceSelected', null);
                    return;
                }

                const info = await createResourceLink(resourceId);

                onAction('onUpdateDone', info);
                onAction('onLtiResourceSelected', info);
            }}
        />
    );
};

const EditResourceFromLtiLinkContainer = () => {
    const iframeIntegration = useIframeIntegration([
        'resourceTitle',
        'ltiLaunchUrl',
    ]);

    if (!iframeIntegration) {
        return <div>missing parameters</div>;
    }

    const { queryParams, onAction, jwt } = iframeIntegration;

    return (
        <EdlibComponentsProvider
            edlibUrl={appConfig.apiUrl}
            getJwt={async () => ({
                type: 'external',
                token: jwt,
            })}
            configuration={JSON.parse(queryParams.configuration)}
            language={queryParams.language}
        >
            <div
                style={{
                    height: '100vh',
                    position: 'relative',
                    display: 'flex',
                    flexDirection: 'column',
                    backgroundColor: 'white',
                }}
            >
                <ModalHeader onClose={() => onAction('onClose')}>
                    {queryParams.resourceTitle}
                </ModalHeader>
                <EditEdlibResourceModal
                    ltiLaunchUrl={queryParams.ltiLaunchUrl}
                    onAction={onAction}
                />
            </div>
        </EdlibComponentsProvider>
    );
};

export default EditResourceFromLtiLinkContainer;
