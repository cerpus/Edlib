export default {
    getDisplayInfo: async (req, res, next) => {
        try {
            const embedlyData = await req.context.services.embedly.getForUrl(
                req.query.url
            );

            return [
                {
                    type: 'card',
                    url: embedlyData.url,
                    title: embedlyData.title,
                    img: embedlyData.thumbnail_url || null,
                    description: embedlyData.description,
                    provider: {
                        name: embedlyData.provider_name,
                        url: embedlyData.provider_url,
                    },
                },
                embedlyData.html && {
                    type: 'embedly',
                    url: embedlyData.url,
                    title: embedlyData.title,
                    html: embedlyData.html,
                    provider: {
                        name: embedlyData.provider_name,
                        url: embedlyData.provider_url,
                    },
                },
            ].filter(Boolean);
        } catch (e) {
            res.status(404).json({
                message: `Could not find display information for url ${req.query.url}`,
            });
        }
    },
};
