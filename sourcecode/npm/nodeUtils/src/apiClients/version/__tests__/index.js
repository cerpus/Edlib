import versionApiClient from '../index.js';
import axios from 'axios';
import externalSystemNames from '../../../constants/externalSystemNames.js';
import { ApiException, ValidationException } from '../../../exceptions';
import versionPurposes from '../../../constants/versionPurposes.js';
jest.mock('axios');

describe('Api Clients', () => {
    describe('Version', () => {
        afterEach(() => {
            axios.mockClear();
        });
        describe('getForResource', () => {
            it('Should throw on invalid external system name', async () => {
                const versionApi = versionApiClient({}, {}, {});
                await expect(
                    versionApi.getForResource('invalid', 'id')
                ).rejects.toThrow(ValidationException);
            });
        });
        describe('create', () => {
            it('Should throw on invalid external system name', async () => {
                const versionApi = versionApiClient({}, {}, {});
                await expect(
                    versionApi.create(versionPurposes.CREATE, 'invalid', 'id')
                ).rejects.toThrow(ValidationException);
            });
            it('Should throw on invalid version purpose', async () => {
                const versionApi = versionApiClient({}, {}, {});
                await expect(
                    versionApi.create('invalid', externalSystemNames.CONTENT_AUTHOR, 'id')
                ).rejects.toThrow(ValidationException);
            });
        });
    });
});
