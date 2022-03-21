import React from 'react';
import styled from 'styled-components';
import Square from './Square';

const ToolbarWrapper = styled.div`
    position: absolute;
    z-index: 1;
    top: ${(props) => props.top || 0}px;
    left: ${(props) => props.left || 0}px;
    background-color: ${(props) => props.theme.colors.tertiary};
    border-radius: 10px;
    display: flex;

    ${(props) =>
        props.hidden &&
        `
        visibility: hidden;
    `}
`;

const StyledToolbar = styled.div`
    z-index: 1;
    background-color: ${(props) => props.theme.colors.tertiary};
    border-radius: 10px;
    display: flex;
`;

const Toolbar = ({ children, hidden, ...otherProps }, ref) => {
    return (
        <ToolbarWrapper ref={ref} hidden={hidden} {...otherProps}>
            <Square />
            <StyledToolbar>{children}</StyledToolbar>
        </ToolbarWrapper>
    );
};

export default React.forwardRef(Toolbar);
