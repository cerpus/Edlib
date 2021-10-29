import React from 'react';
import { MemoryRouter } from 'react-router-dom';
import { ResourceCapabilitiesProvider } from '../../contexts/ResourceCapabilities';
import { useEdlibResource } from '../../hooks/requests/useResource';
import { ConfigurationProvider } from '../../contexts/Configuration';
import useFetch from '../../hooks/useFetch';
import useConfig from '../../hooks/useConfig';
import useMaintenanceMode from '../../hooks/requests/useMaintenanceMode';
import useRequestWithToken from '../../hooks/useRequestWithToken';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import contentExplorerLandingPages from '../../constants/contentExplorerLandingPages';
import ExportWrapper from '../../components/ExportWrapper';
import { Modal } from '@material-ui/core';
import EdlibModalContent from './EdlibModalContent';
import Spinner from '@cerpus/ui';

const getStartPage = (userConfiguredStartPage) => {
    if (
        userConfiguredStartPage &&
        contentExplorerLandingPages[userConfiguredStartPage]
    ) {
        return contentExplorerLandingPages[userConfiguredStartPage];
    }

    return '/my-content';
};

const EdlibModal = ({
    onClose,
    onResourceSelected,
    enableVersionInterface = false,
    contentOnly = false,
}) => {
    const { edlib } = useConfig();
    const createResourceLink = useEdlibResource();
    const { getUserConfig } = useEdlibComponentsContext();
    const startPage = getStartPage(getUserConfig('landingContentExplorerPage'));
    const { enabled: inMaintenanceMode } = useMaintenanceMode();

    const {
        error: errorLoadingConfig,
        loading: loadingConfig,
        response: dokuFeatures,
    } = useFetch(edlib(`/dokus/features`), 'GET');
    const request = useRequestWithToken();

    return (
        <ExportWrapper>
            <MemoryRouter initialEntries={[startPage]}>
                <ConfigurationProvider
                    enableDoku={
                        !errorLoadingConfig &&
                        !loadingConfig &&
                        dokuFeatures.enableDoku
                    }
                    enableVersionInterface={enableVersionInterface}
                    inMaintenanceMode={inMaintenanceMode}
                >
                    <ResourceCapabilitiesProvider
                        value={{
                            onInsert: async (resourceId, resourceVersionId) => {
                                const info = await createResourceLink(
                                    resourceId,
                                    resourceVersionId
                                );

                                onResourceSelected(info);
                            },
                            onRemove: async (edlibId) => {
                                await request(
                                    edlib(`/resources/v2/resources/${edlibId}`),
                                    'DELETE'
                                );
                            },
                        }}
                    >
                        {contentOnly ? (
                            <EdlibModalContent
                                onClose={onClose}
                                loading={loadingConfig}
                            />
                        ) : (
                            <Modal
                                open={true}
                                width="100%"
                                onClose={onClose}
                                style={{ margin: 20 }}
                            >
                                <div>
                                    <EdlibModalContent
                                        onClose={onClose}
                                        height="calc(100vh - 40px)"
                                    />
                                </div>
                            </Modal>
                        )}
                    </ResourceCapabilitiesProvider>
                </ConfigurationProvider>
            </MemoryRouter>
        </ExportWrapper>
    );
};

export default EdlibModal;
