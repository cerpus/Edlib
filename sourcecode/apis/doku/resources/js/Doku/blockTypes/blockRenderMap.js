import React from 'react';
import { Map } from 'immutable';
import * as Draft from 'draft-js';
import Figure from './Figure';
import List from './List';
import Paragraph from './Paragraph';
import Blockquote from './Blockquote';

export default Draft.DefaultDraftBlockRenderMap.merge(
    Map({
        'atomic': {
            element: Figure,
        },
        'unstyled': {
            element: Paragraph,
        },
        'unordered-list-item': {
            element: 'span',
            wrapper: <List />,
        },
        'ordered-list-item': {
            element: 'span',
            wrapper: <List numbered />,
        },
        'blockquote': {
            element: Blockquote,
        },
    })
);
