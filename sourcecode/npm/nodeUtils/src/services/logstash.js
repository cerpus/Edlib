import dgram from 'dgram';
import winston from 'winston';
import os from 'os';
import appConfig from '../envConfig/app.js';

export default class LogstashTransport extends winston.Transport {
    constructor(opts) {
        super(opts);

        this._udpClient = dgram.createSocket('udp4');
        this.connectionInfo = {
            host: opts.logstash.host || '127.0.0.1',
            port: opts.logstash.port || 9605,
        };
    }

    removeColorsFromString(string) {
        if (!(typeof string === 'string' || string instanceof String)) {
            return string;
        }

        return string.replace(
            /[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g,
            ''
        );
    }

    retrieveMeta(info) {
        const ignoreKeys = ['message', 'level'];
        return Object.entries(info)
            .filter(([key]) => ignoreKeys.indexOf(key) === -1)
            .reduce(
                (result, [key, value]) => ({
                    ...result,
                    [key]: value,
                }),
                {}
            );
    }

    log(info, callback) {
        setImmediate(() => {
            this.emit('logged', info);
        });

        try {
            const logEntry = {
                '@timestamp': new Date().toISOString(),
                '@version': 1,
                'message': this.removeColorsFromString(info.message),
                'host': os.hostname(),
                'service': appConfig.serviceName || 'npm-edlib-node-utils',
                'namespace': appConfig.environment,
                'level': info.level,
                'type': 'node.js',
                'fields': {
                    ...this.retrieveMeta(info),
                    level: info.level,
                },
            };

            this._send(JSON.stringify(logEntry), (error) => {
                if (error) {
                    console.error(error);
                }

                callback();
            });
        } catch (e) {
            console.error(e);
            callback();
        }
    }

    _send(message, callback) {
        const buf = new Buffer(message);
        this._udpClient.send(
            buf,
            0,
            buf.length,
            this.connectionInfo.port,
            this.connectionInfo.host,
            callback
        );
    }
}
