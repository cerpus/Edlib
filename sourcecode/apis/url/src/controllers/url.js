import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';

export default {
    getById: async (req) => {
        const dbUrl = await req.context.db.url.getById(req.params.urlId);

        if (!dbUrl) {
            throw new NotFoundException('url');
        }

        if (req.query.embedInfo === '1') {
            const embedlyInfo = await req.context.services.embedly.getForUrl(
                dbUrl.url
            );

            return {
                ...dbUrl,
                embed: {
                    title: embedlyInfo.title,
                    description: embedlyInfo.description,
                    html: embedlyInfo.html,
                },
            };
        }

        return dbUrl;
    },
};
