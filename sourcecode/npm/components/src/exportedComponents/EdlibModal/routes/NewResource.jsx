import React from 'react';
import { useResourceCapabilities } from '../../../contexts/ResourceCapabilities';
import ResourceEditor from '../../../components/ResourceEditor';
import { useEdlibComponentsContext } from '../../../contexts/EdlibComponents';
import { useHistory } from 'react-router-dom';

const ContentAuthor = ({ match }) => {
    const history = useHistory();
    const [loading, setLoading] = React.useState(false);
    const { onInsert } = useResourceCapabilities();
    const { getUserConfig } = useEdlibComponentsContext();
    const canReturnResources = getUserConfig('canReturnResources');

    return (
        <ResourceEditor
            onResourceReturned={({ resourceId }) => {
                setLoading(true);
                if (canReturnResources) {
                    onInsert(resourceId);
                } else {
                    history.push(`/my-content?sortBy=created`);
                }
            }}
            type={match.params.type}
            loading={loading}
        />
    );
};

export default ContentAuthor;
