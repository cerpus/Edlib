import React from 'react';
import { addDecorator } from '@storybook/react';
import { MemoryRouter } from 'react-router-dom';
import { INITIAL_VIEWPORTS } from '@storybook/addon-viewport';

export const parameters = {
    viewport: {
        viewports: INITIAL_VIEWPORTS,
    },
};
addDecorator((story) => (
    <MemoryRouter initialEntries={['/']}>{story()}</MemoryRouter>
));

addDecorator((storyFn) => <div>{storyFn()}</div>);

addDecorator((storyFn) => <div style={{ padding: 20 }}>{storyFn()}</div>);
