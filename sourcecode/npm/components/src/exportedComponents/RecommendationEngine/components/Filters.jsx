import React from 'react';
import styled from 'styled-components';
import ResourceFilters from '../../../components/ResourceFilters';
import { Close as CloseIcon } from '@material-ui/icons';
import useTranslation from '../../../hooks/useTranslation';
import { Button } from '@material-ui/core';

const Background = styled.div`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    background-color: rgba(0, 0, 0, 0.5);
`;

const FiltersContent = styled.div`
    position: absolute;
    top: 40px;
    left: 0;
    bottom: 0;
    width: 100%;
    padding: 20px;
    overflow-y: auto;

    background-color: #f3f3f3;
`;

const Filters = ({ filters, onClose }) => {
    const { t } = useTranslation();

    return (
        <>
            <Background onClick={onClose} />
            <FiltersContent>
                <Button
                    style={{ marginBottom: 10 }}
                    color="primary"
                    variant="outlined"
                    onClick={onClose}
                    startIcon={<CloseIcon />}
                >
                    <span style={{ textTransform: 'uppercase' }}>
                        {t('Lukk')}
                    </span>
                </Button>
                <ResourceFilters filters={filters} />
            </FiltersContent>
        </>
    );
};

export default Filters;
