import React from 'react';
import { Switch, Route, Redirect } from 'react-router-dom';

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
import appConfig from '../../../../../config/app';
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

const ContentExplorer = ({ match, onAction }) => {
    const { t } = useTranslation();
    const { inMaintenanceMode } = useConfigurationContext();
    const createResourceLink = useEdlibResource();
    const { getUserConfig } = useEdlibComponentsContext();
    const request = useRequestWithToken();
    const startPage = getStartPage(getUserConfig('landingContentExplorerPage'));

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
                        `${appConfig.apiUrl}/resources/v2/resources/${edlibId}`,
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
                    getUrl={(path) => match.url + path}
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
                                path={`${match.path}/resources/new/:type`}
                                component={NewResource}
                            />
                            <Route
                                exact
                                path={`${match.path}/resources/:edlibId`}
                                component={EditResource}
                            />
                            <Route
                                exact
                                path={`${match.path}/resources/:edlibId/edit-done`}
                                component={ResourceEditDone}
                            />
                            <Route
                                exact
                                path={`${match.path}/resources/:edlibId/:translateToLanguage`}
                                component={EditResource}
                            />
                            <Route
                                path={`${match.path}/my-content`}
                                component={MyContent}
                            />
                            <Route
                                path={`${match.path}/shared-content`}
                                component={SharedContent}
                            />
                            <Redirect to={`${match.path}${startPage}`} />
                        </Switch>
                    )}
                </div>
            </div>
        </ResourceCapabilitiesProvider>
    );
};

export default ContentExplorer;
