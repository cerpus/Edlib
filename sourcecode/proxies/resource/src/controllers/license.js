export default {
    getAll: async (req, res, next) => {
        return [
            { id: 'CC0', name: 'Creative Commons' },
            { id: 'BY', name: 'CC Attribution' },
            { id: 'BY-SA', name: 'CC Attribution, sharealike' },
            { id: 'BY-NC', name: 'CC Attribution, noncommercial' },
            { id: 'BY-ND', name: 'CC Attribution, no derrivatives' },
            {
                id: 'BY-NC-SA',
                name: 'CC Attribution, noncommercial, sharealike',
            },
            {
                id: 'BY-NC-ND',
                name: 'CC Attribution, noncommercial, no derrivatives',
            },
            { id: 'PDM', name: 'Public Domain Mark' },
            { id: 'EDLL', name: 'EdLib license' },
        ];
    },
};
