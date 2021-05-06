import React from 'react';
import { Add, Link, QuestionAnswer } from '@material-ui/icons';
import styled from 'styled-components';
import { lighten, darken } from 'polished';
import cn from 'classnames';
import resourceTypes, { h5pTypes } from '../../../../config/resourceTypes';
import {
    FromSideModal,
    FromSideModalHeader,
} from '../../../../components/FromSideModal';
import UrlAuthor from '../../../../components/UrlAuthor';
import atomicTypes from '../../../../config/atomicTypes';
import { useDokuContext } from '../../dokuContext';
import getSelectedBlockNode from '../../draftJSHelpers/getSelectedBlockNode';
import ResourceEditor from '../../../../components/ResourceEditor';
import getDomElementForBlockKey from '../../draftJSHelpers/getDomElementForBlockKey';

const offsetAddIconTop = -14;

const AddRow = styled.div`
    position: absolute;
    left: 0;
    display: flex;

    transition: top 0.3s ease 0s;

    & > * {
        display: flex;
        flex-direction: row;
        align-items: center;
    }
`;

const ResourceTypes = styled.div`
    > * {
        cursor: pointer;
        margin-left: 10px;
    }
`;

const AddButton = styled(Add)`
    cursor: pointer;
    color: black;
    font-size: 3em !important;
    background-color: ${(props) => props.theme.colors.tertiary};
    border-radius: 50%;

    &:hover {
        background-color: ${(props) =>
            darken(0.1, props.theme.colors.tertiary)};
    }

    &.selected {
        transition: transform 0.15s ease-in-out;
        color: white;
        transform: rotate(45deg);
    }

    &:not(.selected) {
        transition: transform 0.15s ease-in-out;
        transform: rotate(0deg);
    }
`;

const TypeLink = styled.div`
    background-color: ${(props) => lighten(0.6, props.theme.colors.primary)};
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    height: 45px;
    width: 45px;

    svg {
        font-size: 1.6em;
    }

    &:hover {
        background-color: ${(props) =>
            lighten(0.5, props.theme.colors.primary)};
    }
`;

const getShowButton = (editorState) => {
    const contentState = editorState.getCurrentContent();
    const selectionState = editorState.getSelection();
    if (
        !selectionState.isCollapsed() ||
        selectionState.anchorKey !== selectionState.focusKey ||
        contentState
            .getBlockForKey(selectionState.getAnchorKey())
            .getType()
            .indexOf('atomic') >= 0
    ) {
        return false;
    }

    const block = contentState.getBlockForKey(selectionState.anchorKey);

    return block.getLength() <= 0;
};

const getDefaultOffset = (editorState) => {
    const currentContent = editorState.getCurrentContent();
    const lastBlock = currentContent.getLastBlock();

    if (!lastBlock) {
        return offsetAddIconTop;
    }

    if (!currentContent.hasText()) {
        return offsetAddIconTop;
    }

    const element = getDomElementForBlockKey(lastBlock.getKey());

    if (!element) {
        return offsetAddIconTop;
    }

    return element.offsetTop + element.offsetHeight;
};

const AddResource = ({ onAddResource, offsetTop = 0 }) => {
    const { editorState, wrapperSize } = useDokuContext();
    const [showResourceTypes, setShowResourceTypes] = React.useState(false);
    const [selectedResourceType, setSelectedResourceType] = React.useState(
        false
    );
    const [positionInfo, setPositionInfo] = React.useState({
        offsetTop: 0,
        shouldMoveCursorToEndOnInsert: true,
    });

    const selectedNode = getSelectedBlockNode(window);

    React.useLayoutEffect(() => {
        const showButton = getShowButton(editorState);
        if (showButton && selectedNode) {
            return setPositionInfo({
                offsetTop:
                    offsetTop + selectedNode.offsetTop + offsetAddIconTop,
            });
        }

        return setPositionInfo({
            offsetTop: offsetTop + getDefaultOffset(editorState),
            shouldMoveCursorToEndOnInsert: true,
        });
    }, [editorState, selectedNode, wrapperSize.height]);

    React.useEffect(() => {
        if (!positionInfo.offsetTop && showResourceTypes) {
            setShowResourceTypes(false);
        }
    }, [positionInfo.offsetTop, showResourceTypes]);

    return (
        <>
            <AddRow
                style={{ top: positionInfo.offsetTop }}
                onMouseDown={(event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    setShowResourceTypes(!showResourceTypes);
                }}
            >
                <div>
                    <AddButton
                        className={cn({
                            selected: !!showResourceTypes,
                        })}
                    />
                </div>
                {showResourceTypes && (
                    <ResourceTypes>
                        {[
                            {
                                type: h5pTypes.H5P,
                                body: 'H5P',
                            },
                            {
                                type: resourceTypes.URL,
                                body: <Link />,
                            },
                            {
                                type: h5pTypes.questionset,
                                body: <QuestionAnswer />,
                            },
                        ].map((link) => (
                            <TypeLink
                                key={link.type}
                                onMouseDown={(event) => {
                                    event.preventDefault();
                                    event.stopPropagation();
                                    setSelectedResourceType(link.type);
                                }}
                            >
                                {link.body}
                            </TypeLink>
                        ))}
                    </ResourceTypes>
                )}
            </AddRow>
            <FromSideModal
                isOpen={selectedResourceType}
                onClose={() => setSelectedResourceType(false)}
                usePortal={false}
            >
                {selectedResourceType && (
                    <div
                        style={{
                            display: 'flex',
                            flexDirection: 'column',
                            height: '100%',
                        }}
                    >
                        <FromSideModalHeader
                            onClose={() => setSelectedResourceType(false)}
                        >
                            {selectedResourceType}
                        </FromSideModalHeader>
                        {[h5pTypes.H5P, h5pTypes.questionset].indexOf(
                            selectedResourceType
                        ) !== -1 && (
                            <ResourceEditor
                                type={selectedResourceType}
                                onResourceReturned={(edlibId) => {
                                    setSelectedResourceType(false);
                                    setShowResourceTypes(false);
                                    onAddResource(
                                        atomicTypes.EDLIB_RESOURCE,
                                        edlibId,
                                        undefined,
                                        positionInfo.shouldMoveCursorToEndOnInsert
                                    );
                                }}
                            />
                        )}
                        {selectedResourceType === resourceTypes.URL && (
                            <UrlAuthor
                                onUse={(resourceType, edlibId, data) => {
                                    onAddResource(
                                        resourceType,
                                        edlibId,
                                        data,
                                        positionInfo.shouldMoveCursorToEndOnInsert
                                    );
                                    setSelectedResourceType(false);
                                }}
                            />
                        )}
                    </div>
                )}
            </FromSideModal>
        </>
    );
};

export default AddResource;
