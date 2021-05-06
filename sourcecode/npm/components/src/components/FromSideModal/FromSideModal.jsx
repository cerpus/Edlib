import React from 'react';
import styled from 'styled-components';
import cn from 'classnames';
import { useLockBodyScroll } from 'moment-hooks';
import { Portal } from '@cerpus/ui';

const Background = styled.div`
    visibility: hidden;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: ${(props) => props.zIndex};

    overflow-x: hidden;
    overflow-y: auto;
    background-color: black;
    opacity: 0;

    &.open {
        visibility: visible;
        opacity: 0.5;
        transition: opacity 0.6s ease-in-out;
    }
`;

const Dialog = styled.div`
    position: fixed;
    top: 0;
    right: -85vw;
    bottom: 0;
    z-index: ${(props) => props.zIndex};
    width: 85vw;
    max-width: 100%;
    background-color: ${(props) => props.theme.colors.background};
    overflow-y: auto;
    transition: right 0.3s ease-in-out;

    &.open {
        box-shadow: -10px 0 5px rgba(0, 0, 0, 0.18);
        display: block;
        right: 0;
    }
`;

const Content = styled.div`
    min-height: 50px;
    height: 100vh;
    overflow-y: hidden;
`;

const FromSideModal = ({
    width = 600,
    children,
    onClose,
    isOpen = false,
    usePortal = true,
}) => {
    const ref = React.useRef();
    useLockBodyScroll(!!isOpen);

    const content = (
        <>
            <Dialog
                zIndex={2010}
                width={width}
                ref={ref}
                className={cn({
                    open: isOpen,
                })}
            >
                <Content>{children}</Content>
            </Dialog>
            <Background
                zIndex={2000}
                isOpen={isOpen}
                onClick={onClose}
                className={cn({
                    open: isOpen,
                })}
            />
        </>
    );

    if (usePortal) {
        return <Portal>{content}</Portal>;
    }

    return content;
};

export default (props) => {
    return <FromSideModal {...props} />;
};
