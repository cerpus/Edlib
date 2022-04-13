import React from 'react';
import AlignmentWrapper from '../../../../containers/AlignmentWrapper/AlignmentWrapper';
import useGetResourcePreview from '../../../../hooks/requests/useGetResourcePreview';
import useConfig from '../../../../hooks/useConfig';
import { Card, Embedly } from '../../../../components/UrlAuthor';
import { CircularProgress } from '@mui/material';
import useFetchWithToken from '../../../../hooks/useFetchWithToken';
import { BaseToolbar } from '../../../../containers/AlignmentWrapper';
import { useDokuContext } from '../../../../dokuContext';

const EdlibUrlResource = ({ data, onUpdate, block, entityKey }) => {
    const { edlibId, size, display } = data;
    const { edlib } = useConfig();
    const { loading, error, preview } = useGetResourcePreview({
        edlibId,
    });
    const { setEditEdlibResourceData } = useDokuContext();

    const {
        loading: displayInfoLoading,
        error: displayInfoError,
        response: displayForms,
    } = useFetchWithToken(
        edlib('/dokus/v1/url/display-info'),
        'GET',
        React.useMemo(
            () => ({
                query: {
                    url: preview && preview.url,
                },
            }),
            [preview]
        ),
        loading || error || !preview
    );

    const actualDisplayForm =
        !displayForms ||
        displayForms.length === 0 ||
        displayForms.some((df) => df.type === display)
            ? display
            : null;

    const notFound = displayForms && displayForms.length === 0;
    const cardData =
        displayForms &&
        displayForms.find((df) => df.type === actualDisplayForm);

    return (
        <AlignmentWrapper
            align={data.align}
            size={size}
            block={block}
            onUpdate={onUpdate}
            extraOptions={[
                {
                    key: 'display',
                    options: [
                        { label: 'Embedly', value: 'embedly' },
                        { label: 'Url card', value: 'card' },
                    ].filter(
                        (option) =>
                            displayForms &&
                            displayForms.some((df) => df.type === option.value)
                    ),
                },
            ]}
            toolbar={({ isFocused, left, ref }) => (
                <BaseToolbar
                    align={data.align}
                    data={data}
                    entityKey={entityKey}
                    isFocused={isFocused}
                    left={left}
                    onUpdate={onUpdate}
                    onEdit={setEditEdlibResourceData}
                    ref={ref}
                    extraButtons={[
                        {
                            active: data.display === 'card',
                            onToggle: () =>
                                onUpdate({
                                    display: 'card',
                                }),
                            icon: 'Card',
                        },
                        {
                            active: data.display === 'embedly',
                            onToggle: () =>
                                onUpdate({
                                    display: 'embedly',
                                }),
                            icon: 'Embedly',
                        },
                    ]}
                />
            )}
        >
            {(displayInfoLoading || displayInfoLoading) && (
                <div>
                    <CircularProgress />
                </div>
            )}
            {(error || displayInfoError) && <div>error</div>}
            {notFound && <div>ikke funnet</div>}
            {cardData && cardData.type === 'card' && <Card data={cardData} />}
            {cardData && cardData.type === 'embedly' && (
                <Embedly data={cardData} />
            )}
        </AlignmentWrapper>
    );
};

export default EdlibUrlResource;
