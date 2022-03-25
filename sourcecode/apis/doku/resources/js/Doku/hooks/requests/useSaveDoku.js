import React from 'react';
import { useDebounce } from 'moment-hooks';
import { convertToRaw } from 'draft-js';
import useConfig from '../useConfig';
import { licenses } from '../../Editors/Doku/components/PublishModal/PublishModal';
import useRequestWithToken from '../useRequestWithToken';

export default (
    dokuData,
    license,
    initialDoku = null,
    hasBeenModified = false,
    defaultTitle = 'Untitled doku'
) => {
    const { edlib } = useConfig();
    const request = useRequestWithToken();
    const [currentDoku, setCurrentDoku] = React.useState(initialDoku);
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState(false);
    const [mustReUpdate, setMustReUpdate] = React.useState(false);
    const [errorPublishing, setErrorPublishing] = React.useState(false);
    const [errorUnpublishing, setErrorUnpublishing] = React.useState(false);

    const debouncedDokuData = useDebounce(dokuData, 500);

    React.useEffect(() => {
        if (!hasBeenModified) {
            return;
        }
        const currentContent = debouncedDokuData.data.getCurrentContent();
        const content = convertToRaw(currentContent);
        const contentString = JSON.stringify(content);

        if (currentDoku && contentString === JSON.stringify(currentDoku.data)) {
            return;
        }

        if (loading) {
            if (!mustReUpdate) {
                setMustReUpdate(true);
            }
            return;
        }

        const hasText = currentContent.hasText();
        const actualTitle =
            debouncedDokuData.title.length === 0
                ? defaultTitle
                : debouncedDokuData.title;

        const makeRequest = () => {
            setMustReUpdate(false);
            setLoading(true);
            setError(false);

            request(
                edlib(
                    `/dokus/v1/dokus${currentDoku ? `/${currentDoku.id}` : ''}`
                ),
                'POST',
                {
                    body: {
                        title: actualTitle,
                        data: content,
                    },
                }
            )
                .then((dokuResponse) => {
                    setCurrentDoku(dokuResponse);
                })
                .catch((e) => setError(e))
                .finally(() => {
                    setLoading(false);
                    if (mustReUpdate) {
                        setMustReUpdate(false);
                    }
                });
        };

        if ((hasText && !currentDoku) || currentDoku) {
            makeRequest();
        }
    }, [debouncedDokuData, mustReUpdate, hasBeenModified]);

    React.useEffect(() => {
        if (!hasBeenModified) {
            return;
        }

        const currentDokuLicense = currentDoku && currentDoku.license;
        if (license === currentDokuLicense) {
            return;
        }

        if (loading) {
            if (!mustReUpdate) {
                setMustReUpdate(true);
            }
            return;
        }

        const makeRequest = () => {
            setMustReUpdate(false);
            setLoading(true);
            setError(false);

            request(
                edlib(
                    `/dokus/v1/dokus${currentDoku ? `/${currentDoku.id}` : ''}`
                ),
                'POST',
                {
                    body: {
                        license,
                        isPublic: license !== licenses.PRIVATE,
                    },
                }
            )
                .then((dokuResponse) => {
                    setCurrentDoku(dokuResponse);
                })
                .catch((e) => setError(e))
                .finally(() => {
                    setLoading(false);
                    if (mustReUpdate) {
                        setMustReUpdate(false);
                    }
                });
        };

        if (currentDoku) {
            makeRequest();
        }
    }, [license, hasBeenModified]);

    const publish = React.useCallback(async () => {
        if (!currentDoku) {
            throw new Error(
                'Publish cannot be called before the resource has been saved'
            );
        }
        setErrorPublishing(false);

        try {
            const doku = await request(
                edlib(`/dokus/v1/dokus/${currentDoku.id}/publish`),
                'POST'
            );

            setCurrentDoku(doku);
        } catch (error) {
            console.error(error);
            setErrorPublishing(true);
        }
    }, [currentDoku]);

    const unpublish = React.useCallback(async () => {
        if (!currentDoku) {
            throw new Error(
                'Publish cannot be called before the resource has been saved'
            );
        }
        setErrorUnpublishing(false);

        try {
            const doku = await request(
                edlib(`/dokus/v1/dokus/${currentDoku.id}/unpublish`),
                'POST'
            );

            setCurrentDoku(doku);
        } catch (error) {
            console.error(error);
            setErrorUnpublishing(true);
        }
    }, [currentDoku]);

    return {
        loading,
        error,
        savedDoku: currentDoku,
        currentId: currentDoku && currentDoku.id,
        publish,
        unpublish,
    };
};
