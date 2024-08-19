import { buildRawContext } from '../context/index.js';
import { logger, validateJoi } from '@cerpus/edlib-node-utils';
import JobKilledException from '../exceptions/JobKilledException.js';
import { getJobData, updateJobInfo } from '../services/job.js';
import Joi from 'joi';
import appConfig from '../config/app.js';

export default ({ pubSubConnection }) =>
    async ({ jobId }) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        const returnInvalidData = async () => {
            return await context.db.job.update(jobId, {
                message:
                    'Invalid JSON. Must be an array with object and keys usageId and caId',
                failedAt: new Date(),
                doneAt: new Date(),
            });
        };
        try {
            let { resumeData, data } = await getJobData(context, jobId);

            let dataJson;
            try {
                dataJson = validateJoi(
                    JSON.parse(data),
                    Joi.array()
                        .items(
                            Joi.object().keys({
                                caId: Joi.string().required(),
                                usageId: Joi.string().uuid().required(),
                            })
                        )
                        .min(1)
                );
            } catch (e) {
                return await returnInvalidData();
            }

            let alreadyInDb = 0;
            let notFoundInResourceAPI = 0;
            let created = 0;
            for (let item of dataJson) {
                const usage = await context.db.usage.getById(item.usageId);
                if (!usage) {
                    const resource =
                        await context.services.resource.getResourceFromExternalSystemInfo(
                            'contentauthor',
                            item.caId
                        );

                    if (!resource) {
                        notFoundInResourceAPI++;
                    } else {
                        created++;
                        await context.db.usage.create({
                            id: item.usageId,
                            resourceId: resource.id,
                            resourceVersionId: appConfig.features
                                .autoUpdateLtiUsage
                                ? null
                                : resource.version.id,
                        });
                    }
                } else {
                    alreadyInDb++;
                }
            }

            await updateJobInfo(context, jobId, {
                doneAt: new Date(),
                message: `Ferdig med å importere ltiUsages. ${dataJson.length} totalt å importere. ${created} har blitt laget. ${alreadyInDb} allerede i ltiUsage databasen og ${notFoundInResourceAPI} ikke funnet i resourceAPI`,
                resumeData: null,
            });
        } catch (e) {
            await context.db.job.update(jobId, {
                message: e.message,
                failedAt: new Date(),
                doneAt: new Date(),
            });

            if (!(e instanceof JobKilledException)) {
                logger.error(e);
            }
        }
    };
