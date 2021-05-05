import axios from 'axios';
import { exceptionTranslator } from '../../services/index.js';
import externalSystemNames from '../../constants/externalSystemNames.js';
import {
    NotFoundException,
    ValidationException,
    validationExceptionError,
} from '../../exceptions/index.js';
import versionPurposes from '../../constants/versionPurposes.js';
import * as errorReporting from '../../services/errorReporting.js';

const createVersionAxios = (req, config) => async (options) => {
    try {
        return await axios({
            ...options,
            url: `${config.url}${options.url}`,
            maxRedirects: 0,
            headers: {
                ...options.headers,
                ...errorReporting.getTraceHeaders(req),
            },
        });
    } catch (e) {
        throw e;
    }
};

const validateExternalName = (externalSystemName) => {
    if (Object.values(externalSystemNames).indexOf(externalSystemName) === -1) {
        const newExternalSystemName = Object.values(externalSystemNames).reduce(
            (newExternalSystemName, aliasExternalSystemName) => {
                if (newExternalSystemName) {
                    return newExternalSystemName;
                }

                if (
                    aliasExternalSystemName.toLowerCase() ===
                    externalSystemName.toLowerCase()
                ) {
                    return aliasExternalSystemName;
                }

                return null;
            },
            null
        );

        if (!newExternalSystemName) {
            throw new ValidationException(
                validationExceptionError(
                    'externalSystemName',
                    'versionClient.getForResource',
                    'invalid externalSystemName'
                )
            );
        }

        return newExternalSystemName;
    }

    return externalSystemName;
};

export default (req, config) => {
    const versionAxios = createVersionAxios(req, config);

    const healthy = async () => {
        return (
            await versionAxios({
                url: `/healthy`,
            })
        ).data;
    };

    const getForResource = async (externalSystemName, externalSystemId) => {
        const _externalSystemName = validateExternalName(externalSystemName);

        try {
            return (
                await versionAxios({
                    url: `/v1/resources/${_externalSystemName}/${externalSystemId}`,
                })
            ).data.data;
        } catch (e) {
            if (e.response && e.response.status === 404) {
                return null;
            }
            throw e;
        }
    };

    const getVersionParents = async (id) => {
        return (
            await versionAxios({
                url: `/v1/resources/${id}/parents`,
            })
        ).data.data;
    };

    const create = async (
        versionPurpose,
        externalSystemName,
        externalSystemId,
        parentExternalSystemId = null
    ) => {
        const _externalSystemName = validateExternalName(externalSystemName);

        if (Object.values(versionPurposes).indexOf(versionPurpose) === -1) {
            throw new ValidationException(
                validationExceptionError(
                    'versionPurpose',
                    'versionClient.create',
                    'invalid versionPurpose'
                )
            );
        }
        let parentId = null;

        if (parentExternalSystemId) {
            const versionResource = await getForResource(
                _externalSystemName,
                parentExternalSystemId
            );

            if (!versionResource) {
                throw new NotFoundException('parent not found');
            }

            parentId = versionResource.id;
        }

        return (
            await versionAxios({
                url: `/v1/resources`,
                method: 'POST',
                data: {
                    externalSystem: _externalSystemName,
                    externalReference: externalSystemId,
                    externalUrl: `http://${externalSystemName}/${externalSystemId}`,
                    parent: parentId,
                    versionPurpose,
                },
            })
        ).data.data;
    };

    return {
        getForResource,
        getVersionParents,
        create,
        healthy,
        config,
    };
};
