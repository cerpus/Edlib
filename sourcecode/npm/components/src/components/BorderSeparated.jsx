import React from 'react';
import styled from 'styled-components';

export default styled.div`
    margin-top: 10px;
    & > * {
        border-top: 2px solid ${(props) => props.theme.colors.border};
    }

    & > *:last-child {
        border-bottom: 2px solid ${(props) => props.theme.colors.border};
    }
`;
