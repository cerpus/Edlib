import React from 'react';
import _ from 'lodash';
import { Spinner } from '@cerpus/ui';
import ModalHeader from './ModalHeader';
import useIframeIntegration from '../../../../../hooks/useIframeIntegration';
import { useEdlibResource } from '../../../../../hooks/requests/useResource';
import useFetchWithToken from '../../../../../hooks/useFetchWithToken';
import ResourceEditor from '../../../../../components/ResourceEditor';
import { EdlibComponentsProvider } from '../../../../../contexts/EdlibComponents';
import useTranslation from '../../../../../hooks/useTranslation.js';
import { useConfigurationContext } from '../../../../../contexts/Configuration.jsx';

const EditEdlibResourceModal = ({ ltiLaunchUrl, onAction }) => {
    const createResourceLink = useEdlibResource();
    const { www } = useConfigurationContext();
    const { t } = useTranslation();
    const { edlibApi } = useConfigurationContext();
    const {
        response,
        error: ltiError,
        loading: ltiLoading,
    } = useFetchWithToken(
        edlibApi(`/lti/v2/lti/convert-launch-url`),
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

    if (ltiLoading) {
        return <Spinner />;
    }

    if (ltiError) {
        return (
            <>
                <ModalHeader onClose={() => onAction('onClose')}>
                    {_.capitalize(t('something_happened'))}
                </ModalHeader>
            </>
        );
    }

    return (
        <>
            <ModalHeader onClose={() => onAction('onClose')}>
                {www(`/s/resources/${response.id}`)}
            </ModalHeader>
            <ResourceEditor
                edlibId={response.id}
                onResourceReturned={async ({
                    resourceId,
                    resourceVersionId,
                }) => {
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
        </>
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
            externalJwt={{
                type: 'external',
                token: jwt,
            }}
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
                <EditEdlibResourceModal
                    ltiLaunchUrl={queryParams.ltiLaunchUrl}
                    onAction={onAction}
                />
            </div>
        </EdlibComponentsProvider>
    );
};

export default EditResourceFromLtiLinkContainer;
