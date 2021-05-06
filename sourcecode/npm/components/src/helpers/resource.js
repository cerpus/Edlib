export default {
    filterByCapabilities: (resources, capability) =>
        resources.filter(
            (r) => r.resourceCapabilities.indexOf(capability) !== -1
        ),
};
