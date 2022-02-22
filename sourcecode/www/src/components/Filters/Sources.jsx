import React from 'react';
import { FormGroup, Spinner } from '@cerpus/ui';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import useConfig from '../../hooks/useConfig';
import useTranslation from '../../hooks/useTranslation';
import Checkbox from './components/Checkbox';

const Sources = ({ sources }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();
    const { loading, response } = useFetchWithToken(
        edlib(`/resources/v1/filters/sources`)
    );

    if (!response || loading) {
        return <Spinner />;
    }

    return (
        <>
            {[
                { title: t('source.h5p'), value: 'H5P' },
                {
                    title: t('source.article'),
                    value: 'Article',
                },
                {
                    title: t('source.questionSet'),
                    value: 'QuestionSet',
                },
                {
                    title: t('source.game'),
                    value: 'Game',
                },
                ...response.map((item) => ({
                    title: item.name,
                    value: item.uri,
                })),
            ]
                .sort((a, b) =>
                    a.title < b.title ? -1 : a.title > b.title ? 1 : 0
                )
                .map(({ title, value }) => (
                    <FormGroup key={value}>
                        <Checkbox
                            title={title}
                            onToggle={() => sources.toggle(value)}
                            checked={sources.has(value)}
                        />
                    </FormGroup>
                ))}
        </>
    );
};

export default Sources;
