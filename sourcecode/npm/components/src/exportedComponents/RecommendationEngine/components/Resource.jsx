import React from 'react';
import styled from 'styled-components';
import { Visibility } from '@material-ui/icons';

const StyledResource = styled.div`
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-top: 2px solid ${(props) => props.theme.colors.border};

    & > div {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    & > div:first-child {
        padding-right: 10px;
    }
`;

const PreviewIcon = styled.a`
    padding-right: 5px;

    & > svg {
        width: ${(props) => props.size}px;
        height: ${(props) => props.size}px;
    }

    &,
    &:visited {
        text-decoration: none;
        color: gray;
    }
`;

const Resource = ({ resource, onPreview }) => {
    return (
        <StyledResource>
            <div>{resource.name}</div>
            <div>
                <PreviewIcon
                    href=""
                    onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        onPreview(e);
                    }}
                    size={24}
                >
                    <Visibility />
                </PreviewIcon>
            </div>
        </StyledResource>
    );
};

export default Resource;
