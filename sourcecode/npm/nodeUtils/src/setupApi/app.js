import './setupDotenv.js';
import express from 'express';
import bodyParser from 'body-parser';
import cookieParser from 'cookie-parser';
import helmet from 'helmet';
import logRequest from '../middlewares/logRequest.js';
import endpointNotFoundHandler from '../middlewares/endpointNotFoundHandler.js';
import exceptionHandler from '../middlewares/exceptionHandler.js';
import * as errorReporting from '../services/errorReporting.js';
import prepareTrace from '../middlewares/prepareTrace.js';
import viewsDir from '../views/__dirname';
import { logExpressError } from '../services/errorReporting.js';

const app = express();

export default async (
    buildRouter,
    { errorReportingConfig, trustProxy, configureApp, extraViewDir } = {
        trustProxy: false,
        configureApp: () => {},
    }
) => {
    const compiledRouter = await buildRouter();

    const enableErrorReporting =
        errorReportingConfig && errorReportingConfig.enable;

    if (enableErrorReporting) {
        errorReporting.init(app, errorReportingConfig);
    }

    if (configureApp) {
        configureApp(app);
    }

    if (trustProxy) {
        app.set('trust proxy', true);
    }

    const viewsDirs = [viewsDir];

    if (extraViewDir) {
        viewsDirs.push(extraViewDir);
    }

    app.set('view engine', 'pug');
    app.set('views', viewsDirs);
    app.use(prepareTrace);
    app.use(helmet.hidePoweredBy());
    app.use(function (req, res, next) {
        const origin = req.get('origin');
        res.header('Access-Control-Allow-Credentials', 'true');
        res.header('Access-Control-Allow-Origin', origin);
        res.header(
            'Access-Control-Allow-Headers',
            'Origin, X-Token, Content-Type, Accept, authorization, sentry-trace'
        );
        res.header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE');

        if (req.method === 'OPTIONS') {
            res.sendStatus(200);
        } else {
            next();
        }
    });
    app.use(logRequest());
    app.use(bodyParser.json());
    app.use(bodyParser.urlencoded({ extended: false }));
    app.use(cookieParser());
    app.use(compiledRouter);
    app.use(endpointNotFoundHandler);
    app.use(logExpressError);
    app.use(exceptionHandler);

    return app;
};
