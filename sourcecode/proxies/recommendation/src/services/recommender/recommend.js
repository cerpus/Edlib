export default (recommender) => {
    const getRecommendation = async (data) => {
        return (
            await recommender({
                url: `/recommend`,
                method: 'POST',
                data,
            })
        ).data;
    };

    return {
        getRecommendation,
    };
};
