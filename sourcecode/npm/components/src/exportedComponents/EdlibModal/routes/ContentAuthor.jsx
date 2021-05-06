import React from 'react';
import { useResourceCapabilities } from '../../../contexts/ResourceCapabilities';
import ContentAuthorWrapper from '../../../components/ContentAuthor';

const ContentAuthor = ({ match }) => {
    const { onInsert } = useResourceCapabilities();

    return (
        <ContentAuthorWrapper
            onResourceReturned={(resourceId) => onInsert(resourceId)}
            type={match.params.type}
        />
    );
};

export default ContentAuthor;
