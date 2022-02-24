import React from 'react';

const areFiltersAndChoicesIdenticalForGroup = (choices, filters) => {
    if (filters.length !== choices.length) {
        return false;
    }

    return !filters.some(
        (filter) => !choices.some((choice) => choice.value === filter.value)
    );
};

const FilterUtils = (filters, { contentTypes, licenses } = {}) => {
    const addFilterWithChoice = (choices, removeNotFound = true) =>
        choices
            .map((choice) => {
                let filter = null;
                switch (choice.group_name) {
                    case 'license':
                        filter = licenses.find(
                            (license) => license.id === choice.value
                        );
                        break;
                    case 'contentType':
                        filter = contentTypes.find(
                            (contentType) =>
                                contentType.contentType === choice.value
                        );
                        break;
                    default:
                        break;
                }
                return {
                    ...choice,
                    filter,
                };
            })
            .filter((choice) => !removeNotFound || choice.filter);

    return {
        areFiltersAndChoicesIdentical: (choices) =>
            areFiltersAndChoicesIdenticalForGroup(
                choices.filter((c) => c.group_name === 'contentType'),
                filters.contentTypes.value
            ) &&
            areFiltersAndChoicesIdenticalForGroup(
                choices.filter((c) => c.group_name === 'license'),
                filters.licenses.value
            ),
        setFilterFromChoices: (choices) => {
            const augmentedChoices = addFilterWithChoice(choices);

            filters.contentTypes.setValue(
                augmentedChoices
                    .filter((choice) => choice.group_name === 'contentType')
                    .map((choice) => ({
                        title: choice.filter.title,
                        value: choice.value,
                    }))
            );

            filters.licenses.setValue(
                augmentedChoices
                    .filter((choice) => choice.group_name === 'license')
                    .map((choice) => ({
                        title: choice.filter.name,
                        value: choice.value,
                    }))
            );
        },
        getChipsFromChoices: (choices) => {
            const augmentedChoices = addFilterWithChoice(choices);

            return [
                augmentedChoices
                    .filter((choice) => choice.group_name === 'contentType')
                    .map((choice) => ({
                        title: choice.filter.title,
                        value: choice.value,
                    })),
                augmentedChoices
                    .filter((choice) => choice.group_name === 'license')
                    .map((choice) => ({
                        title: choice.filter.name,
                        value: choice.value,
                    })),
            ].flat();
        },
        getChipsFromFilters: (allowDeletion = true) =>
            [
                filters.contentTypes.value.map((filter, index) => ({
                    title: filter.title,
                    value: filter.value,
                    onDelete: allowDeletion
                        ? () => filters.contentTypes.removeIndex(index)
                        : null,
                })),
                filters.licenses.value.map((filter, index) => ({
                    title: filter.title,
                    value: filter.value,
                    onDelete: allowDeletion
                        ? () => filters.licenses.removeIndex(index)
                        : null,
                })),
            ].flat(),
    };
};

export default FilterUtils;
