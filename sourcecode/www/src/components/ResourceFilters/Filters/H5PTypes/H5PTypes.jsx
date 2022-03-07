import React from 'react';
import filterViewTypes from '../../filterViewTypes.js';
import Grouped from './Grouped.jsx';
import Alphabetical from './Alphabetical.jsx';

const H5PTypes = ({
    contentTypes,
    filterCount,
    contentTypeData,
    filterViewType,
}) => {
    const allH5ps = contentTypeData
        .map((item) => {
            const count = filterCount.find(
                (filterCount) => filterCount.key === item.contentType
            );
            return {
                title: item.title,
                value: item.contentType,
                filteredCount: count ? count.count : 0,
            };
        })
        .sort((a, b) => (a.title < b.title ? -1 : a.title > b.title ? 1 : 0));

    if (filterViewType === filterViewTypes.GROUPED) {
        return <Grouped allH5ps={allH5ps} contentTypes={contentTypes} />;
    }

    return <Alphabetical allH5ps={allH5ps} contentTypes={contentTypes} />;
};

export default H5PTypes;
