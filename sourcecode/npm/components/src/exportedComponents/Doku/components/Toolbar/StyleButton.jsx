import React from 'react';
import styled from 'styled-components';

const Button = styled.div`
    display: flex;
    justify-content: center;
    flex-direction: column;
    color: ${(props) => (props.active ? 'white' : 'black')};
    font-weight: ${(props) => (props.active ? 'bold' : 'normal')};
    cursor: pointer;
    padding: 5px;
`;

const StyleButton = ({ active, onToggle, onClick, children }) => (
    <Button
        onMouseDown={(e) => {
            e.preventDefault();
            onToggle && onToggle();
        }}
        onClick={onClick}
        active={active}
    >
        {children}
    </Button>
);

export default StyleButton;
