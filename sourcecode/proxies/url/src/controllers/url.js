import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';

export default {
    viewUrl: async (req, res, next) => {
        const urlInfo = await req.context.services.url.getByIdWithEmbedInfo(
            req.params.urlId
        );

        if (!urlInfo) {
            throw new NotFoundException('url');
        }

        console.log(urlInfo);
        if (urlInfo.embed.html) {
            res.render('ltiViewUrlEmbeded', {
                name: urlInfo.name,
                html: urlInfo.embed.html,
            });
            return;
        }

        res.render('ltiViewUrl', urlInfo);
    },
};
