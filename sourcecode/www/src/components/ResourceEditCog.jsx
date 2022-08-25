import React from 'react';
import { Manager, Popper, Reference } from 'react-popper';
import {
    ArrowForward,
    Edit as EditIcon,
    Delete as DeleteIcon,
} from '@mui/icons-material';
import MaterialIcon from './Icons/Material';
import styled from 'styled-components';
import useResourceCapabilities from '../hooks/useResourceCapabilities';
import { resourceCapabilities } from '../config/resource';
import { useConfigurationContext } from '../contexts/Configuration';
import useTranslation from '../hooks/useTranslation';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

const Wrapper = styled.div`
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
`;

const Content = styled.div`
    width: 200px;
    background-color: white;
    box-shadow: 0 0 6px 5px rgba(0, 0, 0, 0.18);
    border-radius: 5px;
`;

const Row = styled.div`
    padding: 5px 20px;
    display: flex;
    justify-content: space-between;
    cursor: pointer;

    & > div {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
`;

const ResourceEditCog = ({
    resource,
    isOpen,
    onOpen,
    onClose,
    onUse,
    onEdit,
    onTranslate,
    onRemove,
    showDeleteButton,
    children,
}) => {
    const { t } = useTranslation();
    const capabilities = useResourceCapabilities(resource);
    const { enableTranslationButton } = useConfigurationContext();
    const { getUserConfig } = useEdlibComponentsContext();
    const canReturnResources = getUserConfig('canReturnResources');

    return (
        <Manager>
            <Reference>
                {({ ref }) =>
                    children ? (
                        children({
                            ref,
                            onOpen,
                        })
                    ) : (
                        <MaterialIcon
                            name="MoreVert"
                            ref={ref}
                            onClick={(e) => {
                                e.stopPropagation();
                                e.preventDefault();
                                onOpen();
                            }}
                        />
                    )
                }
            </Reference>
            {isOpen && (
                <Popper placement="left-start" positionFixed>
                    {({ ref, style, placement }) => (
                        <Wrapper
                            onClick={(e) => {
                                onClose();
                                e.preventDefault();
                                e.stopPropagation();
                            }}
                        >
                            <Content
                                ref={ref}
                                style={style}
                                data-placement={placement}
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }}
                                onMouseDown={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }}
                            >
                                {[
                                    canReturnResources && {
                                        label: t('Sett inn'),
                                        icon: ArrowForward,
                                        onClick: onUse,
                                    },
                                    capabilities[resourceCapabilities.EDIT] && {
                                        label: t('Rediger innhold'),
                                        icon: EditIcon,
                                        onClick: onEdit,
                                    },
                                    capabilities[resourceCapabilities.EDIT] &&
                                        enableTranslationButton && {
                                            label: t('Oversett innhold'),
                                            icon: EditIcon,
                                            onClick: onTranslate,
                                        },
                                    capabilities[resourceCapabilities.EDIT] &&
                                        showDeleteButton && {
                                            label: t('Fjern'),
                                            icon: DeleteIcon,
                                            onClick: onRemove,
                                        },
                                ]
                                    .filter(Boolean)
                                    .map(({ label, icon: Icon, onClick }) => (
                                        <Row onClick={onClick} key={label}>
                                            <div>{label}</div>
                                            <div>
                                                <Icon />
                                            </div>
                                        </Row>
                                    ))}
                            </Content>
                        </Wrapper>
                    )}
                </Popper>
            )}
        </Manager>
    );
};

export default ResourceEditCog;
