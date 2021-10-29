import React from 'react';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import useConfig from '../../hooks/useConfig';
import { Modal, Spinner } from '@cerpus/ui';
import ResourceEditor from '../../components/ResourceEditor';
import { useEdlibResource } from '../../hooks/requests/useResource';
import ModalHeader from '../../components/ModalHeader';
import { MemoryRouter } from 'react-router-dom';
import ExportWrapper from '../../components/ExportWrapper';
import useMaintenanceMode from '../../hooks/requests/useMaintenanceMode';
import useTranslation from '../../hooks/useTranslation';
import {
    ConfigurationProvider,
    useConfigurationContext,
} from '../../contexts/Configuration.jsx';

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

export default ({ removePadding = false, ...props }) => {
    const { enabled: inMaintenanceMode } = useMaintenanceMode();

    return (
        <ExportWrapper>
            <MemoryRouter>
                <ConfigurationProvider inMaintenanceMode={inMaintenanceMode}>
                    <Modal
                        isOpen={true}
                        width="100%"
                        onClose={props.onClose}
                        displayCloseButton={false}
                        removePadding={removePadding}
                    >
                        <div
                            style={{
                                height: removePadding
                                    ? '100vh'
                                    : 'calc(100vh - 40px)',
                                display: 'flex',
                                flexDirection: 'column',
                            }}
                        >
                            <ModalHeader onClose={props.onClose}>
                                {props.header}
                            </ModalHeader>
                            <EditEdlibResourceModal {...props} />
                        </div>
                    </Modal>
                </ConfigurationProvider>
            </MemoryRouter>
        </ExportWrapper>
    );
};
