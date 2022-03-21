import React from 'react';
import cn from 'classnames';
import styled from 'styled-components';
import { useDokuContext } from '../../dokuContext';
import { FocusDecorator } from '../../plugins/Focusable';

export const alignment = {
    LEFT: 'left',
    CENTER: 'center',
};

const Wrapper = styled.div`
    max-width: 100%;
    margin-top: ${(props) => props.theme.doku.defaultSpacing}px;

    > div {
        position: relative;
        z-index: 2;
        margin-bottom: 10px;
    }

    &.left {
        margin-right: ${(props) => props.theme.doku.defaultSpacing}px;
        margin-left: -100px;
    }
`;

const Overlay = styled.div`
    position: absolute;
    background-color: rgba(0, 0, 0, 0);
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
`;

const AlignmentWrapper = ({ toolbar, align = 'center', children, block }) => {
    const ref = React.useRef();
    const toolbarRef = React.useRef();
    const [left, setLeft] = React.useState(null);
    const { isEditing } = useDokuContext();

    const maxWidth = align === alignment.LEFT ? 500 : '100%';

    let float = 'none';
    let actualBlockType = true;

    if (align === alignment.LEFT) {
        float = 'left';
        actualBlockType = false;
    }

    React.useEffect(() => {
        if (!ref.current || !toolbarRef.current) {
            return;
        }

        setLeft(
            Math.max(
                ref.current.offsetWidth / 2 -
                    toolbarRef.current.offsetWidth / 2,
                0
            )
        );
    }, [align]);

    return (
        <>
            <div style={{ clear: 'both' }} />
            <Wrapper
                style={{
                    display: actualBlockType ? 'flex' : 'block',
                    float,
                    width: actualBlockType ? '100%' : undefined,
                }}
                className={cn({
                    left: align === alignment.LEFT,
                })}
            >
                <div
                    style={{
                        width: maxWidth,
                        maxWidth: '100%',
                    }}
                    ref={ref}
                >
                    <FocusDecorator contentBlock={block}>
                        {({ isFocused }) => (
                            <>
                                {children}
                                {isEditing && toolbar && (
                                    <Overlay>
                                        {toolbar({
                                            isFocused,
                                            left,
                                            ref: toolbarRef,
                                        })}
                                    </Overlay>
                                )}
                            </>
                        )}
                    </FocusDecorator>
                </div>
            </Wrapper>
        </>
    );
};

export default AlignmentWrapper;
