import React from 'react';
import {
    DoneAll,
    Link,
    AttachMoney,
    Image,
    Functions,
    FormatQuote,
    Title,
    FormatListBulleted,
    FormatUnderlined,
    FormatItalic,
    FormatBold,
    MoreVert,
    MoreHoriz,
    VideogameAsset,
} from '@material-ui/icons';
import styled from 'styled-components';

const icons = {
    DoneAll,
    Link,
    AttachMoney,
    Image,
    Functions,
    FormatQuote,
    Title,
    FormatListBulleted,
    FormatUnderlined,
    FormatItalic,
    FormatBold,
    MoreVert,
    MoreHoriz,
    VideogameAsset,
};

const StyledIcon = styled.div`
    display: inline-flex;
    justify-content: center;

    & > svg {
        font-size: ${(props) => props.theme.rem(props.fontSizeRem)};
    }
`;

const Icon = React.forwardRef(({ name, fontSizeRem = 1.5, ...props }, ref) => {
    const Component = name && icons[name];

    return (
        <StyledIcon fontSizeRem={fontSizeRem} ref={ref} {...props}>
            {Component ? <Component /> : name ? name : ''}
        </StyledIcon>
    );
});

export default Icon;
