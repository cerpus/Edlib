import React from 'react';
import { Link as RouterLink } from 'react-router-dom';
import { Link as MaterialUiLink } from '@material-ui/core';

const Link = ({ to, children, ...props }) => {
    return (
        <MaterialUiLink to={to} component={RouterLink} {...props}>
            {children}
        </MaterialUiLink>
    );
};

export default Link;
