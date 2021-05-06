import React from 'react';
import { FormatBold, FormatItalic, FormatUnderlined } from '@material-ui/icons';
import IconButtonContent from '../components/Controls/components/IconButtonContent';

export default [
    { label: <IconButtonContent icon={FormatBold} />, style: 'BOLD' },
    { label: <IconButtonContent icon={FormatItalic} />, style: 'ITALIC' },
    {
        label: <IconButtonContent icon={FormatUnderlined} />,
        style: 'UNDERLINE',
    },
];
