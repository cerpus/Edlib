import jose from 'node-jose';

let cache = null;
export const getKeyStore = async (context) => {
    const keystore = jose.JWK.createKeyStore();
    const dbKeys = await context.db.jwksKey.getAllActive();
    const keys = [];

    if (dbKeys.length !== 0) {
        for (let dbKey of dbKeys) {
            keys.push(await jose.JWK.asKey(dbKey.key));
        }
    }

    if (keys.length === 0) {
        const newKey = await jose.JWK.createKey('RSA', 2048, {
            alg: 'RS256',
            use: 'sig',
        });
        keys.push(newKey);

        await context.db.jwksKey.create({
            key: JSON.stringify(newKey.toJSON(true)),
        });
    }

    for (let key of keys) {
        await keystore.add(key);
    }

    cache = keystore;

    return cache;
};

export const wellKnownJwks = async (context) => {
    const keyStore = await getKeyStore(context);

    return keyStore.toJSON();
};

export const encrypt = async (context, payload, expireHours = 72, subject) => {
    const keyStore = await getKeyStore(context);
    const [key] = keyStore.all({ use: 'sig' });

    const opt = { compact: true, jwk: key, fields: { typ: 'jwt' } };
    const actualPayload = JSON.stringify({
        exp: Math.floor(Date.now() / 1000) + 60 * 60 * expireHours,
        iat: Math.floor(Date.now() / 1000),
        sub: subject,
        payload,
    });

    return await jose.JWS.createSign(opt, key).update(actualPayload).final();
};

export const verify = async (context, token, options) => {
    const keyStore = await getKeyStore(context);

    const { payload } = await jose.JWS.createVerify(keyStore).verify(token);
    return JSON.parse(payload.toString()).payload;
};

export default {
    wellKnownJwks,
    encrypt,
    verify,
};
