import mysql from 'mysql';
import dbConfig from '../envConfig/db.js';
import appConfig from '../envConfig/app.js';
import knex from 'knex';
import moment from 'moment-timezone';
import path from 'path';

const commonConfig = {
    migrations: {
        directory: path.resolve('./src/migrations'),
        tableName: 'migrations',
        loadExtensions: ['.cjs'],
    },
};

const queryBuilder = !appConfig.isTest
    ? knex({
          client: 'mysql',
          connection: {
              user: dbConfig.user,
              host: dbConfig.host,
              database: dbConfig.database,
              password: dbConfig.password,
              port: dbConfig.port,
              typeCast: function (field, next) {
                  if (field.type === 'TINY' && field.length === 1) {
                      return field.string() === '1'; // 1 = true, 0 = false
                  }

                  return next();
              },
          },
          pool: { min: 1, max: 10 },
          ...commonConfig,
      })
    : knex({
          client: 'sqlite3',
          connection: ':memory',
          useNullAsDefault: true,
          ...commonConfig,
      });

const shouldLogQuery = (sql, executionTimeMS) => {
    const illegalStrings = [
        'migrations',
        'migrations_lock',
        'information_schema.tables',
    ];

    return !illegalStrings.some((illegalString) => sql.includes(illegalString));
};

const times = {};

queryBuilder
    .on('query', (query) => {
        times[query.__knexQueryUid] = moment();
    })
    .on('query-response', (response, query) => {
        if (times[query.__knexQueryUid]) {
            const sql = mysql.format(query.sql, query.bindings).toString();
            const executionTimeMs = times[query.__knexQueryUid];

            if (shouldLogQuery(sql, executionTimeMs)) {
                // logger.debug(chalk.blue(`DB query`), {
                //     sql,
                //     executionTimeMs,
                // });
            }

            delete times[query.__knexQueryUid];
        }
    });

export const dbHelpers = {
    create: async (tableName, data) => {
        await queryBuilder(tableName).insert(data);
        return queryBuilder(tableName).where('id', data.id).first();
    },
    updateId: async (tableName, id, data) => {
        await queryBuilder(tableName).where('id', id).update(data);
        return queryBuilder(tableName).where('id', id).first();
    },
    isUniqueViolation: (error, uniqueKeyName) =>
        error &&
        error.code &&
        parseInt(error.code) === 23505 &&
        (!uniqueKeyName || error.constraint === uniqueKeyName),
};

export default queryBuilder;
