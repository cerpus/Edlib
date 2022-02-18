import React from 'react';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import useConfig from '../../hooks/useConfig';
import { Modal, Spinner } from '@cerpus/ui';
import ResourceEditor from '../../components/ResourceEditor';
import { useEdlibResource } from '../../hooks/requests/useResource';
import ModalHeader from '../../components/ModalHeader';
import { MemoryRouter } from 'react-router-dom';
import ExportWrapper from '../../components/ExportWrapper';
import useTranslation from '../../hooks/useTranslation';
import {
    ConfigurationProvider,
    useConfigurationContext,
} from '../../contexts/Configuration';
import EdlibIframe from '../../components/EdlibIframe';

const EditEdlibResourceModal = ({ ltiLaunchUrl, onUpdateDone }) => {
    const { edlib } = useConfig();
    const createResourceLink = useEdlibResource();
    const { response, ltiError, ltiLoading } = useFetchWithToken(
        edlib('/lti/v2/lti/convert-launch-url'),
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
    const { t } = useTranslation();
    const { inMaintenanceMode } = useConfigurationContext();

    if (ltiLoading || !response) {
        return <Spinner />;
    }

    if (inMaintenanceMode) {
        return <p style={{ padding: '1em' }}>⚠️ {t('maintenance')}</p>;
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
                    return onUpdateDone(null);
                }

                const info = await createResourceLink(resourceId);

                onUpdateDone(info);
            }}
        />
    );
};

export default ({
    removePadding,
    ltiLaunchUrl,
    onClose,
    header,
    onUpdateDone,
}) => {
    return (
        <div
            style={{
                height: removePadding ? '100vh' : 'calc(100vh - 40px)',
            }}
        >
            <EdlibIframe
                path="/s/edit-from-lti-link"
                params={{
                    ltiLaunchUrl,
                    resourceTitle: header || 'Title',
                }}
                onAction={(data) => {
                    switch (data.messageType) {
                        case 'onClose':
                            onClose();
                            break;
                        case 'onUpdateDone':
                            onUpdateDone(data.extras);
                            break;
                        default:
                            break;
                    }
                }}
            />
        </div>
    );
};
