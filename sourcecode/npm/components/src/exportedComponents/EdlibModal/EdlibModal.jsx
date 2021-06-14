import React from 'react';
import Header from '../../components/Header';
import { MemoryRouter, Switch, Route } from 'react-router-dom';
import MyContent from './routes/MyContent';
import { ResourceCapabilitiesProvider } from '../../contexts/ResourceCapabilities';
import { useEdlibResource } from '../../hooks/requests/useResource';
import SharedContent from './routes/SharedContent';
import LinkAuthor from './routes/LinkAuthor';
import ResourceEditDone from './routes/ResourceEditDone';
import { ConfigurationProvider } from '../../contexts/Configuration';
import EditResource from './routes/EditResource';
import NewResource from './routes/NewResource';
import useFetch from '../../hooks/useFetch';
import useConfig from '../../hooks/useConfig';
import CssReset from '../../components/CSSReset';
import useRequestWithToken from '../../hooks/useRequestWithToken';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import contentExplorerLandingPages from '../../constants/contentExplorerLandingPages';
import ExportWrapper from '../../components/ExportWrapper';
import { Modal } from '@material-ui/core';

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
    removePadding = false,
}) => {
    const { edlib } = useConfig();
    const createResourceLink = useEdlibResource();
    const { getUserConfig } = useEdlibComponentsContext();
    const startPage = getStartPage(getUserConfig('landingContentExplorerPage'));

    const {
        error: errorLoadingConfig,
        loading: loadingConfig,
        response: dokuFeatures,
    } = useFetch(edlib(`/dokus/features`), 'GET');
    const request = useRequestWithToken();

    const height = removePadding ? '100vh' : 'calc(100vh - 40px)';

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
                        <Modal
                            open={true}
                            width="100%"
                            onClose={onClose}
                            style={{ margin: 20 }}
                        >
                            <div>
                                <CssReset>
                                    {!loadingConfig && (
                                        <div
                                            style={{
                                                height,
                                                position: 'relative',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                lineHeight: '24px',
                                                fontSize: 16,
                                                backgroundColor: 'white',
                                            }}
                                        >
                                            <Header
                                                onClose={onClose}
                                                viewportHeight={height}
                                            />
                                            <div
                                                style={{
                                                    flex: 1,
                                                    minHeight: 0,
                                                }}
                                            >
                                                <Switch>
                                                    <Route
                                                        path="/resources/new/:type"
                                                        component={NewResource}
                                                    />
                                                    <Route
                                                        exact
                                                        path="/resources/:edlibId"
                                                        component={EditResource}
                                                    />
                                                    <Route
                                                        exact
                                                        path="/resources/:edlibId/edit-done"
                                                        component={
                                                            ResourceEditDone
                                                        }
                                                    />
                                                    <Route
                                                        exact
                                                        path="/resources/:edlibId/:translateToLanguage"
                                                        component={EditResource}
                                                    />
                                                    <Route
                                                        path="/link-author"
                                                        component={LinkAuthor}
                                                    />
                                                    <Route
                                                        path="/my-content"
                                                        component={MyContent}
                                                    />
                                                    <Route
                                                        path="/shared-content"
                                                        component={
                                                            SharedContent
                                                        }
                                                    />
                                                </Switch>
                                            </div>
                                        </div>
                                    )}
                                </CssReset>
                            </div>
                        </Modal>
                    </ResourceCapabilitiesProvider>
                </ConfigurationProvider>
            </MemoryRouter>
        </ExportWrapper>
    );
};

export default EdlibModal;
