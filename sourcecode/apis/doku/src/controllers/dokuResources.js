import { NotFoundException } from '@cerpus-private/edlib-node-utils/exceptions/index.js';

export default {
    getAllForDoku: async (req, res, next) => {
        const doku = await req.context.db.doku.getById.load(
            parseInt(req.params.id)
        );

        if (!doku) {
            throw new NotFoundException('doku');
        }

        return doku.data.blocks
            .filter((block) => block.entityRanges.length !== 0)
            .map(
                (block) =>
                    doku.data.entityMap[String(block.entityRanges[0].key)]
            )
            .filter(
                (entity) =>
                    ['edlibResource', 'edlibUrlResource'].indexOf(
                        entity.type
                    ) !== -1
            )
            .map((entity) => ({
                resourceId: entity.data.resourceId,
            }));
    },
};
