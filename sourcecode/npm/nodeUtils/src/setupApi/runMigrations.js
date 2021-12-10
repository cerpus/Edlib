import './setupDotenv.js';
import logger from '../services/logger.js';
import db from '../services/db.js';

export default () =>
    db.migrate
        .latest()
        .then(() => {
            logger.info(`Migration is done`);
            process.exit(0);
        })
        .catch((error) => {
            logger.error(`Error migrating`, { error });
            process.exit(1);
        });
