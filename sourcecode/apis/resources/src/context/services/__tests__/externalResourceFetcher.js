import _externalResourceFetcher from '../externalResourceFetcher.js';
import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { ClientException } from '@cerpus/edlib-node-utils/lib/exceptions';
import { AxiosException } from '@cerpus/edlib-node-utils';

describe('Context - Services - External Resource Fetcher', () => {
    let mock;

    beforeAll(() => {
        mock = new MockAdapter(axios);
    });

    afterEach(() => {
        mock.reset();
    });

    describe('getContentTypeInfo', () => {
        test('unknown external system name returns null', async () => {
            const externalResourceFetcher = _externalResourceFetcher(false);

            const response = await externalResourceFetcher.getContentTypeInfo(
                'unknown',
                'h5p.test'
            );

            expect(response).toBe(null);
        });
        test('returns null on 404', async () => {
            const externalResourceFetcher = _externalResourceFetcher(false);

            mock.onAny().reply(404, {});

            const response = await externalResourceFetcher.getContentTypeInfo(
                'contentauthor',
                'h5p.test'
            );

            expect(response).toBe(null);
        });
        test('returns content type on 200', async () => {
            const externalResourceFetcher = _externalResourceFetcher(false);

            const contentType = {
                test: true,
            };

            mock.onAny().reply(200, {
                contentType,
            });

            const response = await externalResourceFetcher.getContentTypeInfo(
                'contentauthor',
                'h5p.test'
            );

            expect(response).toEqual(contentType);
        });
        test('returns null on missing configuration', async () => {
            const externalResourceFetcher = _externalResourceFetcher(false);

            const response = await externalResourceFetcher.getContentTypeInfo(
                'url',
                'h5p.test'
            );

            expect(response).toEqual(null);
        });
        test('throws on http 400', async () => {
            const externalResourceFetcher = _externalResourceFetcher(false);

            mock.onAny().reply(400, {});

            await expect(
                externalResourceFetcher.getContentTypeInfo(
                    'contentauthor',
                    'h5p.test'
                )
            ).rejects.toBeInstanceOf(ClientException);
        });
        test('throws on http 500', async () => {
            const externalResourceFetcher = _externalResourceFetcher(false);

            mock.onAny().reply(500, {});

            await expect(
                externalResourceFetcher.getContentTypeInfo(
                    'contentauthor',
                    'h5p.test'
                )
            ).rejects.toBeInstanceOf(AxiosException);
        });
    });
});
