import React from 'react';
import { addDecorator } from '@storybook/react';
import { MemoryRouter } from 'react-router-dom';

addDecorator((story) => (
    <MemoryRouter initialEntries={['/']}>{story()}</MemoryRouter>
));

addDecorator((storyFn) => <div>{storyFn()}</div>);

addDecorator((storyFn) => <div style={{ padding: 20 }}>{storyFn()}</div>);
