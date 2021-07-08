import React from 'react';
import RecommendationEngineComponent from '../exportedComponents/RecommendationEngine';
import { EdlibComponentsProvider } from '..';
import AuthWrapper from '../components/AuthWrapper.jsx';

export default {
    title: 'RecommendationEngine',
};

const edlibApiUrl = 'https://api.edlib.local';

export const RecommendationEngine = () => {
    return (
        <AuthWrapper edlibApiUrl={edlibApiUrl}>
            {({ getJwt }) => {
                return (
                    <EdlibComponentsProvider
                        edlibUrl={edlibApiUrl}
                        getJwt={getJwt}
                    >
                        <div
                            style={{ width: 500, paddingLeft: 20, height: 100 }}
                        >
                            <RecommendationEngineComponent
                                useV2={true}
                                showDragIcon
                            />
                        </div>
                    </EdlibComponentsProvider>
                );
            }}
        </AuthWrapper>
    );
};
