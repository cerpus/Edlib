import apisConfig from '../config/apis.js';

const getRecommendationStatus = async (context) => {
    try {
        await context.services.recommender.recommend.getRecommendation({
            title: 'test',
            description: 'test',
            types: [],
            tags: [],
            licenses: [],
        });

        return {
            name: 'Recommender service',
            color: 'success',
            statusMessage: `All good`,
            parameters: {
                url: apisConfig.recommender.url,
            },
        };
    } catch (e) {
        console.error(e);
        return {
            name: 'Recommender service',
            color: 'danger',
            statusMessage: `Noe skjedde (${e.message})`,
            parameters: {
                url: apisConfig.recommender.url,
            },
        };
    }
};

export default {
    systemStatus: async (req, res, next) => {
        const systems = [
            await req.context.services.status.auth(),
            await getRecommendationStatus(req.context),
            await req.context.services.status.coreExternal(req.context),
        ];

        return {
            name: 'EdLibAPI - Auth',
            status: 'All good',
            color: 'success',
            systems,
        };
    },
};
