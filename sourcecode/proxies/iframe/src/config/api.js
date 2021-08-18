const url = new URL(window.location.href);

export default {
    url: `${url.protocol}//${url.host}`,
};
