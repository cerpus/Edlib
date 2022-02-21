import React from 'react';
import { useHistory } from 'react-router-dom';
import ContentAuthor from '../../../components/ContentAuthor';

const ContentAuthorEdit = ({ match }) => {
    const history = useHistory();

    return (
        <ContentAuthor
            edlibId={match.params.edlibId}
            translateToLanguage={match.params.translateToLanguage}
            onResourceReturned={(resourceId) =>
                history.push(`/content-author/${resourceId}/edit-done`)
            }
        />
    );
};

export default ContentAuthorEdit;
