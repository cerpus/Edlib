import React from 'react';
import styled from 'styled-components';
import { Input } from '@cerpus/ui';
import { Search } from '@material-ui/icons';
import useTranslation from '../../../hooks/useTranslation';

const Wrapper = styled.div`
    position: relative;
    margin-bottom: 15px;
`;

const StyledInput = styled(Input)`
    width: 100%;
    padding-right: 23px;
`;

const SearchIcon = styled.div`
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: gray;
`;

const SearchField = ({ value, onChange }) => {
    const { t } = useTranslation();
    return (
        <Wrapper>
            <StyledInput
                placeholder={t('Søk')}
                value={value}
                onChange={onChange}
            />
            <SearchIcon>
                <Search style={{ fontSize: 25 }} />
            </SearchIcon>
        </Wrapper>
    );
};

export default SearchField;
