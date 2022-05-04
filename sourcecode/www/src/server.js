import App from './App';
import React from 'react';
import { StaticRouter } from 'react-router-dom';
import express from 'express';
import { renderToString } from 'react-dom/server';
import createEmotionServer from '@emotion/server/create-instance';
import { CacheProvider } from '@emotion/react';
import { ThemeProvider } from '@mui/material/styles';
import { ServerStyleSheet } from 'styled-components';
import { getTssDefaultEmotionCache } from 'tss-react';
import cookieParser from 'cookie-parser';
import { parse } from 'set-cookie-parser';
import { Helmet } from 'react-helmet';

import { createEmotionCache, muiTheme } from './muiSetup.js';
import { addPromiseListToState, FetchProvider } from './contexts/Fetch.jsx';
import { ConfigurationProvider } from './contexts/Configuration.jsx';

const assets = require(process.env.RAZZLE_ASSETS_MANIFEST);

const cssLinksFromAssets = (assets, entrypoint) => {
    return assets[entrypoint]
        ? assets[entrypoint].css
            ? assets[entrypoint].css
                  .map((asset) => `<link rel="stylesheet" href="${asset}">`)
                  .join('')
            : ''
        : '';
};

const jsScriptTagsFromAssets = (assets, entrypoint, ...extra) => {
    return assets[entrypoint]
        ? assets[entrypoint].js
            ? assets[entrypoint].js
                  .map(
                      (asset) =>
                          `<script src="${asset}" ${extra.join(' ')}></script>`
                  )
                  .join('')
            : ''
        : '';
};

export const renderApp = async (req, res) => {
    const context = {};
    const emotionCache = createEmotionCache();
    const sheet = new ServerStyleSheet();

    const emotionServers = [
        // Every emotion cache used in the app should be provided.
        // Caches for MUI should use "prepend": true.
        // MUI cache should come first.
        emotionCache,
        getTssDefaultEmotionCache({ doReset: true }),
    ].map(createEmotionServer);

    const wwwUrl = `${req.protocol}://${req.get('host')}`;

    const SetupApp = (initialState, promiseList = []) => (
        <CacheProvider value={emotionCache}>
            <ThemeProvider theme={muiTheme}>
                <StaticRouter context={context} location={req.url}>
                    <FetchProvider
                        promiseList={promiseList}
                        initialState={initialState}
                        ssrCookies={res.getCookies()}
                        ssrAddCookiesFromSetCookie={res.addCookiesFromSetCookie}
                    >
                        <ConfigurationProvider
                            apiUrl={wwwUrl.replace('www', 'api')}
                            wwwUrl={wwwUrl}
                            isSSR
                            cookies={req.cookies}
                        >
                            <App />
                        </ConfigurationProvider>
                    </FetchProvider>
                </StaticRouter>
            </ThemeProvider>
        </CacheProvider>
    );

    const maxDepth = 10;
    const getStateFromTree = async (state = {}, depth = 0) => {
        const promiseList = [];
        renderToString(SetupApp(state, promiseList));
        if (promiseList.length !== 0 && depth < maxDepth) {
            await addPromiseListToState(state, promiseList);

            return getStateFromTree(state, depth + 1);
        }

        return { state };
    };

    const { state } = await getStateFromTree();

    const markup = renderToString(sheet.collectStyles(SetupApp(state, [])));
    const helmet = Helmet.renderStatic();

    // Grab the CSS from emotion
    const styleTagsAsStr = emotionServers
        .map(({ extractCriticalToChunks, constructStyleTagsFromChunks }) =>
            constructStyleTagsFromChunks(extractCriticalToChunks(markup))
        )
        .join('');

    // Grab the CSS from styled-components
    const styledComponentsTags = sheet.getStyleTags();

    const html = `<!doctype html>
    <html lang="">
    <head ${helmet.htmlAttributes.toString()}>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        ${cssLinksFromAssets(assets, 'client')}
        ${styleTagsAsStr}
        ${styledComponentsTags}
        ${helmet.title.toString()}
        ${helmet.meta.toString()}
        ${helmet.link.toString()}
    </head>
    <body ${helmet.bodyAttributes.toString()}>
        <script>
            window.__FETCH_STATE__=${JSON.stringify(state).replace(
                /</g,
                '\\u003c'
            )};
        </script>
        <div id="root">${markup}</div>
        ${jsScriptTagsFromAssets(assets, 'client', 'defer', 'crossorigin')}
    </body>
    </html>`;

    return { context, html };
};

const server = express();

server
    .disable('x-powered-by')
    .set('trust proxy', true)
    .use(cookieParser())
    .use(express.static(process.env.RAZZLE_PUBLIC_DIR))
    .use((req, res, next) => {
        res.cookiesToSet = {};
        res.getCookies = () => {
            const newSetCookies = Object.entries(res.cookiesToSet).reduce(
                (newSetCookies, [key, { value }]) => ({
                    ...newSetCookies,
                    [key]: value,
                }),
                {}
            );

            return {
                ...req.cookies,
                ...newSetCookies,
            };
        };

        res.addCookiesFromSetCookie = (setCookie) => {
            parse(setCookie).forEach((cookie) => {
                res.cookiesToSet[cookie.name] = cookie;
            });
        };

        next();
    })
    .get('/*', (req, res) => {
        renderApp(req, res).then(({ context, html }) => {
            if (context.url) {
                res.redirect(context.url);
            } else {
                Object.values(res.cookiesToSet).forEach((cookieToSet) =>
                    res.cookie(cookieToSet.name, cookieToSet.value, {
                        ...cookieToSet,
                    })
                );
                res.status(200).send(html);
            }
        }).catch((e) => {
            console.log(e);
            res.status(500).send();
        });
    });

export default server;
