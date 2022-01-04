import React from 'react';
import { FormGroup, Spinner } from '@cerpus/ui';
import useFetchWithToken from '../../hooks/useFetchWithToken';
import useConfig from '../../hooks/useConfig';
import useTranslation from '../../hooks/useTranslation';
import Checkbox from './components/Checkbox';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';

const H5PTypes = ({ contentTypes }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();

    const { getUserConfig } = useEdlibComponentsContext();
    const approvedH5psConfig = getUserConfig('approvedH5ps') || null;

    const { loading, response } = useFetchWithToken(
        edlib(`/resources/v2/content-types/contentauthor`)
    );

    if (!response || loading) {
        return <Spinner />;
    }

    const allH5ps = response.data
        .map((item) => ({
            title: item.title,
            value: item.contentType,
        }))
        .sort((a, b) => (a.title < b.title ? -1 : a.title > b.title ? 1 : 0));

    const approvedH5ps = allH5ps.filter(
        (item) =>
            !approvedH5psConfig ||
            approvedH5psConfig.indexOf(item.value.toUpperCase()) !== -1
    );
    const notApprovedH5ps = allH5ps.filter(
        (item) =>
            approvedH5psConfig &&
            approvedH5psConfig.indexOf(item.value.toUpperCase()) === -1
    );

    const showApprovedNotApproved = notApprovedH5ps.length !== 0;
    return (
        <>
            {showApprovedNotApproved && (
                <p>
                    <strong>{t('Godkjente')}</strong>
                </p>
            )}
            {approvedH5ps.map(({ title, value }) => (
                <FormGroup key={value}>
                    <Checkbox
                        onToggle={() => contentTypes.toggle(value)}
                        checked={contentTypes.has(value)}
                        title={title}
                    />
                </FormGroup>
            ))}
            {showApprovedNotApproved && (
                <p>
                    <strong>{t('Ikke godkjente')}</strong>
                </p>
            )}
            {notApprovedH5ps.map(({ title, value }) => (
                <FormGroup key={value}>
                    <Checkbox
                        onToggle={() => contentTypes.toggle(value)}
                        checked={contentTypes.has(value)}
                        title={title}
                    />
                </FormGroup>
            ))}
        </>
    );
};

export default H5PTypes;
