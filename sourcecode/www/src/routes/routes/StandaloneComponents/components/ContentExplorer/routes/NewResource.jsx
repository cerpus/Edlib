import React from 'react';
import { useHistory } from 'react-router-dom';

import { useResourceCapabilities } from '../../../../../../contexts/ResourceCapabilities';
import ResourceEditor from '../../../../../../components/ResourceEditor';
import { useEdlibComponentsContext } from '../../../../../../contexts/EdlibComponents';
import { useIframeStandaloneContext } from '../../../../../../contexts/IframeStandalone.jsx';

const ContentAuthor = ({ match }) => {
    const history = useHistory();
    const [loading, setLoading] = React.useState(false);
    const { onInsert } = useResourceCapabilities();
    const { getUserConfig } = useEdlibComponentsContext();
    const { getPath } = useIframeStandaloneContext();
    const canReturnResources = getUserConfig('canReturnResources');

    return (
        <ResourceEditor
            onResourceReturned={({ resourceId, resourceVersionId }) => {
                setLoading(true);
                if (canReturnResources) {
                    onInsert(resourceId, resourceVersionId);
                } else {
                    history.push(getPath(`/my-content?sortBy=created`));
                }
            }}
            type={match.params.type}
            loading={loading}
        />
    );
};

export default ContentAuthor;
