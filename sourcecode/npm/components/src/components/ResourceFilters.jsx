import React from 'react';
import ResourcePageFilterGroup from './ResourcePage/components/ResourcePageFilterGroup';
import BorderSeparated from './BorderSeparated';
import Collapsable from './Collapsable';
import { H5PTypes, Licenses } from './Filters';
import useTranslation from '../hooks/useTranslation';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import resourceFilters from '../constants/resourceFilters';
import { Button } from '@material-ui/core';

const ResourceFilters = ({ filters }) => {
    const { t } = useTranslation();
    const { getUserConfig } = useEdlibComponentsContext();
    const disabledFilters = getUserConfig('disabledFilters') || null;

    const filterBlocks = [
        {
            type: resourceFilters.H5P_TYPE,
            title: t('H5P Type'),
            count: filters.contentTypes.value.length,
            content: <H5PTypes contentTypes={filters.contentTypes} />,
        },
        {
            type: resourceFilters.LICENSE,
            title: t('Lisens'),
            count: filters.licenses.value.length,
            content: <Licenses licenses={filters.licenses} />,
        },
    ];

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                e.stopPropagation();
            }}
        >
            <ResourcePageFilterGroup title={t('Ressurser')}>
                <BorderSeparated>
                    {filterBlocks
                        .filter(
                            (filterBlock) =>
                                disabledFilters === null ||
                                disabledFilters.indexOf(filterBlock.type) === -1
                        )
                        .map((filterBlock) => (
                            <Collapsable
                                key={filterBlock.type}
                                title={filterBlock.title}
                                filterCount={filterBlock.count}
                            >
                                {filterBlock.content}
                            </Collapsable>
                        ))}
                </BorderSeparated>
            </ResourcePageFilterGroup>
            <Button
                variant="outlined"
                color="primary"
                fullWidth
                type="gray"
                onClick={() => {
                    filters.reset();
                }}
            >
                Nullstill
            </Button>
        </form>
    );
};

export default ResourceFilters;
