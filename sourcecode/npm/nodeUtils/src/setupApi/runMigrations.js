import './setupDotenv.js';
import chalk from 'chalk';
import logger from '../services/logger.js';
import db from '../services/db.js';

db.migrate
    .latest()
    .then(() => {
        logger.info(chalk.green(`Migration is done`));
        process.exit(0);
    })
    .catch((error) => {
        logger.error(chalk.red(`Error migrating`));
        logger.error(chalk.red(error.stack));
        process.exit(1);
    });
