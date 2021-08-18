import runAsync from './runAsync.js';

const proxyRequest = (getProxy, getUrl, getData = null) =>
    runAsync(async (req, res) => {
        return (
            await getProxy(req)({
                url: getUrl(req),
                data: getData && getData(req),
                method: req.method,
            })
        ).data;
    });

export default proxyRequest;
