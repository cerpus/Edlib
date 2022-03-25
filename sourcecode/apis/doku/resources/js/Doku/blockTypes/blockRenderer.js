import React from 'react';
import Atomic from './Atomic';

export default (block) => {
    switch (block.getType()) {
        case 'atomic':
            return {
                component: Atomic,
                editable: false,
            };
        default:
            return null;
    }
};
