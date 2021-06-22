import React from 'react';
import { action } from '@storybook/addon-actions';
import EdlibModalComponent from '../exportedComponents/EdlibModal';
import { EdlibComponentsProvider } from '../contexts/EdlibComponents';
import AuthWrapper from '../components/AuthWrapper';
import EditEdlibResourceModal from '../exportedComponents/EditEdlibResourceModal';

export default {
    title: 'EdlibModal',
};

const edlibApiUrl = 'https://api.edlib.local';

export const EdlibModal = () => {
    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                        configuration={{
                            canReturnResources: false,
                        }}
                    >
                        <EdlibModalComponent
                            enableDoku={true}
                            enableVersionInterface={true}
                            onClose={action('on close')}
                            onResourceSelected={async (info) =>
                                action('Resource insert')(info)
                            }
                        />
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};

export const EdlibModalIframe = () => {
    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                    >
                        <div style={{ height: '50vh' }}>
                            <EdlibModalComponent
                                contentOnly
                                enableDoku
                                enableVersionInterface
                                onClose={action('on close')}
                                onResourceSelected={async (info) =>
                                    action('Resource insert')(info)
                                }
                            />
                        </div>
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};

export const EditResourceModal = () => {
    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                    >
                        <EditEdlibResourceModal
                            ltiLaunchUrl={
                                'https://api.edlib.local/lti/v2/lti-links/15071fd6-af90-45e6-b499-3a49800c5336'
                            }
                            onUpdateDone={action('Resource update done')}
                        />
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};

export const EditResourceModalFrame = () => {
    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                    >
                        <EditEdlibResourceModal
                            removePadding
                            ltiLaunchUrl={
                                'https://api.edlib.local/lti/v2/lti-links/15071fd6-af90-45e6-b499-3a49800c5336'
                            }
                            onUpdateDone={action('Resource update done')}
                        />
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};
