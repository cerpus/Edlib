import React from 'react';
import { Checkbox as CerpusCheckbox } from '@cerpus/ui';
import styled from 'styled-components';

const Wrapper = styled.div`
    display: flex;
    cursor: pointer;

    img {
        max-width: 100%;
    }
`;

const CheckboxContainer = styled.div`
    margin-top: 4px;
`;

const Title = styled.div`
    padding-left: 15px;
`;

const Checkbox = ({ onToggle, checked, title }) => {
    return (
        <Wrapper onClick={onToggle}>
            <CheckboxContainer>
                <CerpusCheckbox
                    size={13}
                    onToggle={onToggle}
                    checked={checked}
                />
            </CheckboxContainer>
            <Title>{title}</Title>
        </Wrapper>
    );
};

export default Checkbox;
