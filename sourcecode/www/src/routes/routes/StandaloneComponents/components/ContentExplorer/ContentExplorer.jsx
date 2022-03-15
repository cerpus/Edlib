import React from 'react';
import { Switch, Route, Redirect, useLocation } from 'react-router-dom';

import ContentExplorerHeader from '../../../../../components/ContentExplorerHeader';
import { useEdlibComponentsContext } from '../../../../../contexts/EdlibComponents';
import { useConfigurationContext } from '../../../../../contexts/Configuration';
import useTranslation from '../../../../../hooks/useTranslation';
import NewResource from './routes/NewResource';
import EditResource from './routes/EditResource';
import ResourceEditDone from './routes/ResourceEditDone';
import MyContent from './routes/MyContent';
import SharedContent from './routes/SharedContent';
import { ResourceCapabilitiesProvider } from '../../../../../contexts/ResourceCapabilities';
import { useEdlibResource } from '../../../../../hooks/requests/useResource';
import useRequestWithToken from '../../../../../hooks/useRequestWithToken';
import contentExplorerLandingPages from '../../../../../constants/contentExplorerLandingPages';

const getStartPage = (userConfiguredStartPage) => {
    if (
        userConfiguredStartPage &&
        contentExplorerLandingPages[userConfiguredStartPage]
    ) {
        return contentExplorerLandingPages[userConfiguredStartPage];
    }

    return '/my-content';
};

const ContentExplorer = ({ baseUrl, basePath, onAction }) => {
    const { t } = useTranslation();
    const { search } = useLocation();
    const { inMaintenanceMode } = useConfigurationContext();
    const createResourceLink = useEdlibResource();
    const { getUserConfig } = useEdlibComponentsContext();
    const request = useRequestWithToken();
    const startPage = getStartPage(getUserConfig('landingContentExplorerPage'));
    const { edlibApi } = useConfigurationContext();

    return (
        <ResourceCapabilitiesProvider
            value={{
                onInsert: async (resourceId, resourceVersionId, title) => {
                    if (getUserConfig('returnLtiLinks')) {
                        const info = await createResourceLink(
                            resourceId,
                            resourceVersionId
                        );

                        onAction('onLtiResourceSelected', info);
                    } else {
                        let url = new URL(
                            'https://spec.edlib.com/resource-reference'
                        );

                        url.searchParams.append('resourceId', resourceId);

                        if (resourceVersionId) {
                            url.searchParams.append(
                                'resourceVersionId',
                                resourceVersionId
                            );
                        }

                        onAction('onResourceSelected', {
                            url,
                            title,
                        });
                    }
                },
                onRemove: async (edlibId) => {
                    await request(
                        edlibApi(`/resources/v2/resources/${edlibId}`),
                        'DELETE'
                    );
                },
            }}
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
                <ContentExplorerHeader
                    onClose={() => onAction('onClose')}
                    getUrl={(path) => baseUrl + path}
                />
                <div
                    style={{
                        flex: 1,
                        minHeight: 0,
                    }}
                >
                    {inMaintenanceMode && (
                        <p style={{ padding: '1em' }}>⚠️ {t('maintenance')}</p>
                    )}
                    {!inMaintenanceMode && (
                        <Switch>
                            <Route
                                path={`${basePath}/resources/new/:type`}
                                component={NewResource}
                            />
                            <Route
                                exact
                                path={`${basePath}/resources/:edlibId`}
                                component={EditResource}
                            />
                            <Route
                                exact
                                path={`${basePath}/resources/:edlibId/edit-done`}
                                component={ResourceEditDone}
                            />
                            <Route
                                exact
                                path={`${basePath}/resources/:edlibId/:translateToLanguage`}
                                component={EditResource}
                            />
                            <Route
                                path={`${basePath}/my-content`}
                                component={MyContent}
                            />
                            <Route
                                path={`${basePath}/shared-content`}
                                component={SharedContent}
                            />
                            <Redirect to={`${basePath}${startPage}${search}`} />
                        </Switch>
                    )}
                </div>
            </div>
        </ResourceCapabilitiesProvider>
    );
};

export default ContentExplorer;
