import React from 'react';
import styled from 'styled-components';
import NewsPaperO from './icons/NewsPaperO';

const icons = {
    'newspaper-o': NewsPaperO,
};

const StyledIcon = styled.div`
    display: inline-flex;
    justify-content: center;

    & > svg {
        width: 20px;
        height: 20px;

        @media(forced-colors: active) {
            stroke: currentColor;
        },
    }
`;

const Icon = ({ name }) => {
    const Component = name && icons[name];

    return (
        <StyledIcon>{Component ? <Component /> : name ? name : ''}</StyledIcon>
    );
};

export default Icon;
