const lowerCaseFirstLetter = (str) => {
    if (str.length === 0) {
        return str;
    }

    return str.charAt(0).toLowerCase() + str.slice(1);
};

export default lowerCaseFirstLetter;
