let keys = [];

export default {
    add(key) {
        keys.push(key);
        return keys;
    },
    remove(key) {
        keys.filter((item) => item !== key);
        return keys;
    },
    includes(key) {
        return keys.includes(key);
    },
    getAll() {
        return keys;
    },
};
