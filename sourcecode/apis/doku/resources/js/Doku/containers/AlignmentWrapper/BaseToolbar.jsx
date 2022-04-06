import React from 'react';
import { StyleButton, Toolbar } from '../../components/Toolbar';
import { LeftAlignImage } from '../../components/Icons/Custom';
import MaterialIcon from '../../components/Icons/Material';
import Splitter from '../../components/Toolbar/Splitter';
import { Edit } from '@mui/icons-material';
import { alignment } from './AlignmentWrapper';
import styled from 'styled-components';
import useTranslation from '../../hooks/useTranslation';

const EditWrapper = styled.div`
    display: flex;

    > * {
        display: flex;
        align-items: center;
    }

    .text {
        margin: 0 5px;
        font-weight: bold;
    }
`;

const BaseToolbar = React.forwardRef(
    (
        {
            align = 'center',
            left,
            isFocused,
            onUpdate,
            setEditEdlibResourceData,
            entityKey,
            data,
            extraButtons,
        },
        ref
    ) => {
        const { t } = useTranslation();
        return (
            <Toolbar top={-50} left={left} hidden={!isFocused} ref={ref}>
                <StyleButton
                    active={align === alignment.LEFT}
                    onToggle={() =>
                        onUpdate({
                            align: alignment.LEFT,
                        })
                    }
                >
                    <LeftAlignImage />
                </StyleButton>
                <StyleButton
                    active={align === alignment.CENTER}
                    onToggle={() =>
                        onUpdate({
                            align: alignment.CENTER,
                        })
                    }
                >
                    <MaterialIcon name="Image" />
                </StyleButton>
                {extraButtons && (
                    <>
                        <Splitter />
                        {extraButtons.map((buttonInfo) => {
                            if (buttonInfo === 'splitter') {
                                return <Splitter />;
                            }

                            return (
                                <StyleButton
                                    active={buttonInfo.active}
                                    onToggle={buttonInfo.onToggle}
                                >
                                    {buttonInfo.icon}
                                </StyleButton>
                            );
                        })}
                    </>
                )}
                <Splitter />
                <StyleButton
                    active={false}
                    onClick={() =>
                        setEditEdlibResourceData({
                            data,
                            entityKey,
                        })
                    }
                >
                    <EditWrapper>
                        <div>
                            <Edit />
                        </div>
                        <div className="text">{t('Edit')}</div>
                    </EditWrapper>
                </StyleButton>
            </Toolbar>
        );
    }
);

export default BaseToolbar;
