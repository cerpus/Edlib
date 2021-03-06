import { NotFoundException } from '@cerpus/edlib-node-utils';

export default {
    viewUrl: async (req, res, next) => {
        const urlInfo = await req.context.services.url.getByIdWithEmbedInfo(
            req.params.urlId
        );

        if (!urlInfo) {
            throw new NotFoundException('url');
        }

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
