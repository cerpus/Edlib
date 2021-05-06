import React from 'react';
import { FormGroup, Spinner } from '@cerpus/ui';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import useConfig from '../../hooks/useConfig';
import useTranslation from '../../hooks/useTranslation';
import Checkbox from './components/Checkbox';

const Licenses = ({ licenses }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();
    const { loading, response } = useFetchWithToken(
        edlib(`/resources/v1/filters/licenses`)
    );

    if (!response || loading) {
        return <Spinner />;
    }

    return (
        <>
            {response
                .map((item) => {
                    const parts = item.id.split('-');
                    return {
                        title: parts
                            .map((part) => t(`licenses.${part}`))
                            .join(' - '),
                        value: item.id,
                    };
                })
                .sort((a, b) =>
                    a.title < b.title ? -1 : a.title > b.title ? 1 : 0
                )
                .map(({ title, value }) => (
                    <FormGroup key={value}>
                        <Checkbox
                            onToggle={() => licenses.toggle(value)}
                            checked={licenses.has(value)}
                            title={title}
                        />
                    </FormGroup>
                ))}
        </>
    );
};

export default Licenses;
