import React from 'react';
import styled from 'styled-components';
import { PanTool } from '@material-ui/icons';

const StyledDragIcon = styled.div`
    background-color: white;
    position: absolute;
    left: -25px;
    top: 130px;
    transform: rotate(-45deg);
    border: ${(props) => props.theme.border};
    box-shadow: 0 0 6px 5px rgba(0, 0, 0, 0.18);
    width: 50px;
    height: 50px;
    display: flex;
    padding: 5px;
`;

const DragIcon = () => {
    return (
        <StyledDragIcon>
            <PanTool style={{ fontSize: 25 }} />
        </StyledDragIcon>
    );
};

export default DragIcon;
