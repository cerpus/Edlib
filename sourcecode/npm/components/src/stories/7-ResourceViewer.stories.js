import React from 'react';
import { EdlibComponentsProvider } from '../contexts/EdlibComponents';
import ResourceViewer from '../exportedComponents/ResourceViewer';

export default {
    title: 'Resource Viewer',
};

const edlibApiUrl = 'https://api.edlib.local';

export const ViewById = () => {
    return (
        <EdlibComponentsProvider edlibUrl={edlibApiUrl}>
            <ResourceViewer resourceId="0bc6619a-2343-4e0b-83dd-34a2ecc82fff" />
        </EdlibComponentsProvider>
    );
};
