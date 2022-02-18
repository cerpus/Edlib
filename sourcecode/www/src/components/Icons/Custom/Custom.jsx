import React from 'react';
import styled from 'styled-components';

const StyledSvg = styled.svg`
    width: 1em;
    height: 1em;
    display: inline-block;
    font-size: ${(props) => props.theme.rem(1.5)};
    fill: currentColor;
`;

const Custom = ({ children, viewBox = '0 0 24 24', className }) => {
    return (
        <StyledSvg viewBox={viewBox} className={className}>
            {children}
        </StyledSvg>
    );
};

export default Custom;
