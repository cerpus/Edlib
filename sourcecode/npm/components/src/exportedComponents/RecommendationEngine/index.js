import React from 'react';
import RecommendationEngine from './RecommendationEngine';
import RecommendationEngineV2 from './RecommendationEngineV2';

export default ({ useV2 = false, context, ...props }) => {
    if (useV2) {
        return <RecommendationEngineV2 context={context} {...props} />;
    }

    return <RecommendationEngine {...props} />;
};
