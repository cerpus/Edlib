import React from 'react';
import styled from 'styled-components';

const Wrapper = styled.span`
    font-size: 20px;
`;

export default ({ icon: Icon }) => {
    return (
        <Wrapper>
            <Icon fontSize="inherit" />
        </Wrapper>
    );
};
