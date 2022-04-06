import React from 'react';
import useFetch from '../../hooks/useFetch';
import useConfig from '../../hooks/useConfig';
import { EditorState } from 'draft-js';
import convertNdlaArticle from '../../helpers/convertNdlaArticle';
import DokuComponent from '../../Doku';
import { default as decorators } from '../../decorators/';
import { Alert } from '@mui/material';
import useTranslation from '../../hooks/useTranslation';

const NdlaEditor = ({ html }) => {
    const [editorState, setEditorState] = React.useState(() =>
        EditorState.createEmpty()
    );

    React.useEffect(
        () =>
            setEditorState(
                EditorState.createWithContent(
                    convertNdlaArticle(html),
                    decorators
                )
            ),
        [html]
    );

    return <DokuComponent editorState={editorState} />;
};

const NdlaUrl = ({ deprecatedNdlaResourceId, onUse }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();
    const { error, loading, response } = useFetch(
        edlib(`/api/v1/ndla/articles/${deprecatedNdlaResourceId}`)
    );

    return (
        <div>
            {error && <div>noe skjedde</div>}
            {loading && <div>loading</div>}
            {response && (
                <>
                    <Alert severity="warning">{t('urlAuthor.ndlaWarning')}</Alert>
                    <NdlaEditor html={response.content} />
                </>
            )}
        </div>
    );
};

export default NdlaUrl;
