import idApiClient from '../index.js';
import axios from 'axios';
jest.mock('axios');

describe('Api Clients', () => {
    describe('Id', () => {
        afterEach(() => {
            axios.mockClear();
        });
        it('getForId', async () => {
            axios.mockImplementation(() => {
                return {
                    data: {
                        externalSystemName: 'doku',
                    },
                };
            });
            const id = 'some-id';
            const config = {
                url: 'http://test',
            };
            const idApi = idApiClient({}, config);

            const response = await idApi.getForId(id);

            expect(axios).toBeCalledTimes(1);
            expect(axios.mock.calls[0][0].url).toBe(
                `${config.url}/v1/edlib/${id}`
            );
            expect(response.externalSystemName).toBe('Doku');
        });
        it('getForExternal', async () => {
            const id = 'some-id';
            const externalSystemId = 'external-id';
            axios.mockImplementation(() => {
                return {
                    data: {
                        id,
                        externalSystemName: 'doku',
                        externalSystemId: externalSystemId,
                    },
                };
            });
            const config = {
                url: 'http://test',
            };
            const idApi = idApiClient({}, config);

            const response = await idApi.getForExternal(
                'Doku',
                externalSystemId
            );

            expect(axios).toBeCalledTimes(1);
            expect(axios.mock.calls[0][0].url).toBe(
                `${config.url}/v1/external/doku/${externalSystemId}`
            );
            expect(response.externalSystemName).toBe('Doku');
            expect(response.id).toBe(id);
            expect(response.externalSystemId).toBe(externalSystemId);
        });
    });
});
