import React from 'react';
import { createPortal } from 'react-dom';
import styled from 'styled-components';

const IFrame = styled.iframe`
    border: 0;
`;

export default ({ children, ...props }) => {
    const [contentRef, setContentRef] = React.useState(null);
    const mountNode = contentRef && contentRef.contentWindow.document.body;

    return (
        <IFrame {...props} ref={setContentRef}>
            {mountNode &&
                createPortal(React.Children.only(children), mountNode)}
        </IFrame>
    );
};
