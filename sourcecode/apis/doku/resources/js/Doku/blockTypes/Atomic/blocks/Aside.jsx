import React from 'react';
import styled from 'styled-components';
import InBlockEditor from '../../../containers/InBlockEditor';
import { useDokuContext } from '../../../dokuContext';

const StyledAside = styled.aside`
    float: ${(props) => (props.isMobile ? 'none' : 'right')};
    clear: right;
    width: ${(props) => (props.isMobile ? 'unset' : '300px')};
    border-left: 4px solid #e6e6e6;
    margin-top: 20px;
    margin-left: 26px;
    padding: 26px;
    font-size: 0.88889rem;
    line-height: 1.625;
`;

const Aside = ({ data, onUpdate }) => {
    const { isMobile } = useDokuContext();
    const editorRef = React.useRef();
    const focus = () => editorRef.current.focus();

    return (
        <StyledAside
            onClick={(e) => {
                e.stopPropagation();
                e.preventDefault();
                focus();
            }}
            isMobile={isMobile}
        >
            <InBlockEditor
                ref={editorRef}
                editorState={data.editorState}
                onUpdate={onUpdate}
            />
        </StyledAside>
    );
};

export default Aside;
