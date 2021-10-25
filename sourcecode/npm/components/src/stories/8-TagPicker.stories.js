import React from 'react';
import store from 'store';
import { action } from '@storybook/addon-actions';
import TagPickerComponent from '../components/TagPicker';
import { EdlibComponentsProvider } from '..';

export default {
    title: 'TagPicker',
};

export const TagPicker = () => {
    const [tags, setTags] = React.useState([]);
    return (
        <EdlibComponentsProvider
            coreUrl="http://core:8106"
            getJwt={React.useMemo(
                () => async () => {
                    return store.get('token');
                },
                []
            )}
        >
            <TagPickerComponent tags={tags} setTags={setTags} />
        </EdlibComponentsProvider>
    );
};
