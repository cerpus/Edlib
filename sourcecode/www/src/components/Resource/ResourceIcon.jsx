import React from 'react';
import FontAwesomeIcon from '../Icons/FontAwesome';
import MaterialIcon from '../Icons/Material';
import styled from 'styled-components';

const StyledIcon = styled.div`
    display: inline-flex;
    width: ${(props) => props.theme.rem(props.sizeRem)};
    height: ${(props) => props.theme.rem(props.sizeRem)};

    & > img {
        width: ${(props) => props.theme.rem(props.sizeRem)};
        height: ${(props) => props.theme.rem(props.sizeRem)};
    }
`;

const ResourceIcon = ({ contentTypeInfo, fontSizeRem = 1.5 }) => {
    if (contentTypeInfo && contentTypeInfo.icon) {
        if (contentTypeInfo.icon.startsWith('fa:')) {
            return (
                <FontAwesomeIcon
                    name={contentTypeInfo.icon.substring('fa:'.length)}
                    fontSizeRem={fontSizeRem}
                />
            );
        }

        if (contentTypeInfo.icon.startsWith('mui:')) {
            return (
                <MaterialIcon
                    name={contentTypeInfo.icon.substring('mui:'.length)}
                    fontSizeRem={fontSizeRem}
                />
            );
        }

        return (
            <StyledIcon sizeRem={fontSizeRem * 2}>
                <img src={contentTypeInfo.icon} alt="" />
            </StyledIcon>
        );
    }

    return <span />;
};

export default ResourceIcon;
