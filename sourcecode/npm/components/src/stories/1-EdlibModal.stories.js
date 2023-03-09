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
            {({ getJwt, getLanguage }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                        configuration={{
                            canReturnResources: true,
                        }}
                        language={getLanguage()}
                    >
                        <EdlibModalComponent
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
            {({ getJwt, getLanguage }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                        language={getLanguage()}
                    >
                        <div style={{ height: '85vh', border: '1px solid black' }}>
                            <EdlibModalComponent
                                contentOnly
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
                                'https://api.edlib.local/lti/v2/lti-links/3e2cac24-622e-4fe0-95f3-6e3f3689fea7'
                            }
                            onUpdateDone={action('Resource update done')}
                            onClose={action('onClose')}
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
