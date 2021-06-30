import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import { logger } from '@cerpus/edlib-node-utils';
import JobKilledException from '../exceptions/JobKilledException.js';
import { getResumeData, updateJobInfo } from '../services/job.js';
import resourceService from '../services/resource.js';

export default ({ pubSubConnection }) => async ({ jobId }) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        let resumeData = await getResumeData(context, jobId);
        const totalResourceCount = await context.db.resourceVersion.count();

        let coreSyncCount =
            resumeData && resumeData.coreSyncCount
                ? resumeData.coreSyncCount
                : 0;

        // sync ids from core lti_resources
        let run = true;
        const limit = 50;
        let offset = resumeData ? resumeData.offset : 0;
        while (run) {
            await updateJobInfo(context, jobId, {
                percentDone: Math.floor(
                    (coreSyncCount / totalResourceCount) * 100 + 100 / 3
                ),
                message: `Step 2, sync resources with core. ${coreSyncCount} of ${totalResourceCount} done.`,
            });

            const resourceVersions = await context.db.resourceVersion.getAllPaginated(
                offset,
                limit
            );

            await resourceService.retrieveCoreInfo(context, resourceVersions);

            if (resourceVersions.length === 0) {
                run = false;
            }

            coreSyncCount = coreSyncCount + resourceVersions.length;
            offset = offset + limit;

            await updateJobInfo(context, jobId, {
                resumeData: {
                    offset,
                    coreSyncCount,
                },
            });
        }

        await updateJobInfo(context, jobId, {
            doneAt: new Date(),
            message: `Ferdig med å synkronisere ${coreSyncCount} ressurser med core.`,
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
            Sentry.captureException(e);
        }
    }
};
