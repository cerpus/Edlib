import React from 'react';
import styled from 'styled-components';
import InBlockEditor from '../../../containers/InBlockEditor';

const StyledBox = styled.aside`
    padding: 39px;
    margin-top: 52px;
    margin-bottom: 52px;
    border: 1px solid #a5bcd3;
    overflow: hidden;
`;

const Box = ({ data, onUpdate }) => {
    const editorRef = React.useRef();
    const focus = () => editorRef.current.focus();

    return (
        <StyledBox
            onClick={(e) => {
                e.stopPropagation();
                e.preventDefault();
                focus();
            }}
        >
            <InBlockEditor
                ref={editorRef}
                editorState={data.editorState}
                onUpdate={onUpdate}
            />
        </StyledBox>
    );
};

export default Box;
