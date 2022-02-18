import React from 'react';
import { useHistory } from 'react-router-dom';
import { useEdlibComponentsContext } from '../../../../../../contexts/EdlibComponents';
import { useResourceCapabilities } from '../../../../../../contexts/ResourceCapabilities';
import ResourceEditor from '../../../../../../components/ResourceEditor';

const ResourceEditorRoute = ({ match }) => {
    const history = useHistory();
    const [loading, setLoading] = React.useState(false);
    const { onInsert } = useResourceCapabilities();
    const { getUserConfig } = useEdlibComponentsContext();
    const canReturnResources = getUserConfig('canReturnResources');

    return (
        <ResourceEditor
            edlibId={match.params.edlibId}
            translateToLanguage={match.params.translateToLanguage}
            onResourceReturned={({ resourceId, resourceVersionId }) => {
                setLoading(true);
                if (canReturnResources) {
                    onInsert(resourceId, resourceVersionId);
                } else {
                    history.push(`/my-content?sortBy=created`);
                }
            }}
            loading={loading}
        />
    );
};

export default ResourceEditorRoute;
