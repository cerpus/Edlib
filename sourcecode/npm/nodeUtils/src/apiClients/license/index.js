import axios from 'axios';
import { NotFoundException } from '../../exceptions/index.js';
import { exceptionTranslator } from '../../services/index.js';
import upperCaseFirstLetter from '../../helpers/upperCaseFirstLetter.js';
import * as errorReporting from '../../services/errorReporting.js';

const createLicenseAxios = (req, config) => async (options) => {
    try {
        return await axios({
            ...options,
            url: `${config.url}${options.url}`,
            maxRedirects: 0,
            timeout: 5000,
            headers: {
                ...options.headers,
                ...errorReporting.getTraceHeaders(req),
            },
        });
    } catch (e) {
        exceptionTranslator(e, 'license API');
    }
};

export default (req, config, { idApiClient }) => {
    const licenseAxios = createLicenseAxios(req, config);

    const getAll = async () => {
        return (
            await licenseAxios({
                url: '/v1/licenses',
                method: 'GET',
            })
        ).data;
    };

    const get = async (edlibId) => {
        const mapping = await idApiClient.getForId(edlibId);

        const licenses = (
            await licenseAxios({
                url: `/v1/site/${upperCaseFirstLetter(
                    mapping.externalSystemName
                )}/content/${mapping.externalSystemId}`,
                method: 'GET',
            })
        ).data.licenses;

        if (licenses.length === 0) {
            return null;
        }

        return licenses[0];
    };

    const getForResource = async (site = 'ContentAuthor', resourceId) => {
        const licenses = (
            await licenseAxios({
                url: `/v1/site/${upperCaseFirstLetter(
                    site
                )}/content/${resourceId}`,
                method: 'GET',
            })
        ).data.licenses;

        if (licenses.length === 0) {
            return null;
        }

        return licenses[0];
    };

    const addLicenseToContent = async (site, resourceId, license) => {
        return (
            await licenseAxios({
                url: `/v1/site/${upperCaseFirstLetter(
                    site
                )}/content/${resourceId}`,
                method: 'PUT',
                data: {
                    license_id: license,
                },
            })
        ).data.licenses;
    };

    const deleteLicenseFromContent = async (site, resourceId, license) => {
        return (
            await licenseAxios({
                url: `/v1/site/${upperCaseFirstLetter(
                    site
                )}/content/${resourceId}`,
                method: 'DELETE',
                data: {
                    license_id: license,
                },
            })
        ).data.licenses;
    };

    const createContent = async (site, resourceId) => {
        return (
            await licenseAxios({
                url: `/v1/site/${upperCaseFirstLetter(site)}/content`,
                method: 'POST',
                data: {
                    content_id: resourceId,
                    name: '---PLACEHOLDER---',
                },
            })
        ).data;
    };

    const set = async (site, resourceId, license) => {
        const formattedLicense = license.toUpperCase();

        let resourceLicense;
        try {
            resourceLicense = await getForResource(site, resourceId);
        } catch (e) {
            if (!(e instanceof NotFoundException)) {
                throw e;
            }

            await createContent(site, resourceId);
        }

        if (formattedLicense !== resourceLicense) {
            if (resourceLicense) {
                await deleteLicenseFromContent(
                    site,
                    resourceId,
                    resourceLicense
                );
            }
            if (formattedLicense) {
                await addLicenseToContent(site, resourceId, formattedLicense);
            }
        }
    };

    return {
        getAll,
        get,
        set,
        getForResource,
        config,
    };
};
