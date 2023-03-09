import knex from './db.js';
import { ApiException } from '../exceptions/index.js';
import dbConfig from '../envConfig/db.js';

const colors = {
    SUCCESS: 'success',
    WARNING: 'warning',
    DANGER: 'danger',
};

const colorsPriority = [colors.DANGER, colors.WARNING, colors.SUCCESS];

export default ({
    authApi,
    coreExternalApi,
    versionApi,
}) => {
    const auth = async () => {
        if (!authApi) {
            throw new ApiException('authApi is not passed to status service');
        }

        let errorMessage = null;

        try {
            await authApi.getOAuthToken(true);

            return {
                name: 'Auth service',
                statusMessage: 'All good',
                color: colors.SUCCESS,
                parameters: {
                    url: authApi.config.url,
                },
            };
        } catch (e) {
            errorMessage = e.message;
        }

        return {
            name: 'Auth service',
            statusMessage: errorMessage,
            color: colors.DANGER,
            parameters: {
                url: authApi.config.url,
            },
        };
    };

    const version = async () => {
        if (!versionApi) {
            throw new ApiException(
                'versionApi is not passed to status service'
            );
        }

        try {
            await versionApi.healthy();

            return {
                name: 'VersionAPI service',
                color: colors.SUCCESS,
                statusMessage: `All good`,
                parameters: {
                    url: versionApi.config.url,
                },
            };
        } catch (e) {
            return {
                name: 'VersionAPI service',
                color: colors.DANGER,
                statusMessage: `Noe skjedde (${e.message})`,
                parameters: {
                    url: versionApi.config.url,
                },
            };
        }
    };

    const coreExternal = async () => {
        if (!coreExternalApi) {
            throw new ApiException(
                'coreExternalApi is not passed to status service'
            );
        }

        try {
            await coreExternalApi.license.getAll();

            return {
                name: 'Core service',
                color: colors.SUCCESS,
                statusMessage: `All good`,
                parameters: {
                    url: coreExternalApi.config.url,
                },
            };
        } catch (e) {
            return {
                name: 'Core service',
                color: colors.DANGER,
                statusMessage: `Noe skjedde (${e.message})`,
                parameters: {
                    url: coreExternalApi.config.url,
                },
            };
        }
    };

    const db = async () => {
        const parameters = {
            host: dbConfig.host,
            user: dbConfig.user,
            port: dbConfig.port,
            database: dbConfig.database,
        };

        try {
            await knex.select(knex.raw('0'));

            return {
                name: 'Database',
                color: colors.SUCCESS,
                statusMessage: `All good`,
                parameters,
            };
        } catch (e) {
            return {
                name: 'Database',
                color: colors.DANGER,
                statusMessage: `Noe skjedde (${e.message})`,
                parameters,
            };
        }
    };

    const parser = (serviceName, systems) => {
        const status = systems.reduce(
            (response, systemStatus) => {
                const index = colorsPriority.indexOf(systemStatus.color);

                if (index < colorsPriority.indexOf(response.color)) {
                    return systemStatus;
                }

                return response;
            },
            {
                statusMessage: 'All good',
                color: colors.SUCCESS,
            }
        );

        return {
            name: serviceName,
            status: status.statusMessage,
            color: status.color,
            systems,
        };
    };

    return { auth, coreExternal, version, db, parser };
};
