import React from 'react';
import cn from 'classnames';
import useConfig from '../../hooks/useConfig';
import NdlaUrl from './NdlaUrl';
import useFetch from '../../hooks/useFetch';
import useNdlaUrl from './useNdlaUrl';
import { Spinner, Alert, Button } from '@cerpus/ui';
import styled from 'styled-components';
import useTranslation from '../../hooks/useTranslation';
import Card from './Card';
import Embedly from './Embedly';
import useFetchWithToken from '../../hooks/useFetchWithToken';

const urlTypes = {
    NDLA: 'ndla',
    EMBEDLY: 'embedly',
    CARD: 'card',
};

const Tabs = styled.div`
    display: flex;
    margin-bottom: 10px;

    & > * {
        cursor: pointer;
        padding: 10px;
        border: 1px solid ${(props) => props.theme.colors.green};

        &.selected {
            box-shadow: 0 0 6px 1px rgba(0, 0, 0, 0.18);
        }
        &:not(:first-child) {
            border-left: none;
        }
    }
`;

const UrlDisplay = ({ url, enableNDLA = true, onUse }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();
    const ndlaId = useNdlaUrl(url);
    const [selectedFormat, setSelectedFormat] = React.useState(null);

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
                    url,
                },
            }),
            [url]
        )
    );

    const displayOptions = React.useMemo(() => {
        if (!displayForms) return [];

        const options = displayForms.map((displayForm) => displayForm.type);
        if (enableNDLA && ndlaId) {
            options.push(urlTypes.NDLA);
        }

        return options;
    }, [displayForms, ndlaId, enableNDLA]);

    // Always ensure selected display format is in the list of formats
    React.useEffect(() => {
        if (displayOptions.length === 0) {
            selectedFormat !== null && setSelectedFormat(null);
            return;
        }

        if (displayOptions.indexOf(selectedFormat) === -1) {
            setSelectedFormat(displayOptions[0]);
        }
    }, [displayOptions]);

    return (
        <>
            {displayInfoLoading && <Spinner />}
            {displayInfoError && (
                <Alert color="danger">
                    {displayInfoError.response &&
                    displayInfoError.response.status === 404 &&
                    displayInfoError.response.data.message
                        ? displayInfoError.response.data.message
                        : 'Noe skjedde'}
                </Alert>
            )}
            {!displayInfoLoading && displayForms && (
                <>
                    <Tabs>
                        {displayOptions.map((displayOption) => (
                            <div
                                key={displayOption}
                                onClick={() => setSelectedFormat(displayOption)}
                                className={cn({
                                    selected: selectedFormat === displayOption,
                                })}
                            >
                                {t(`urlDisplayTypes.${displayOption}`)}
                            </div>
                        ))}
                    </Tabs>
                    {selectedFormat === urlTypes.CARD && (
                        <>
                            <Button
                                style={{ marginBottom: 10 }}
                                onClick={() =>
                                    onUse({ type: 'url', format: 'card' })
                                }
                            >
                                {t('bruk').toUpperCase()}
                            </Button>
                            <Card
                                data={displayForms.find(
                                    (df) => df.type === selectedFormat
                                )}
                            />
                        </>
                    )}
                    {selectedFormat === urlTypes.EMBEDLY && (
                        <>
                            <Button
                                style={{ marginBottom: 10 }}
                                onClick={() =>
                                    onUse({ type: 'url', format: 'embedly' })
                                }
                            >
                                {t('bruk').toUpperCase()}
                            </Button>
                            <Embedly
                                data={displayForms.find(
                                    (df) => df.type === selectedFormat
                                )}
                            />
                        </>
                    )}
                    {ndlaId && selectedFormat === urlTypes.NDLA && (
                        <NdlaUrl deprecatedNdlaResourceId={ndlaId} />
                    )}
                </>
            )}
        </>
    );
};

export default UrlDisplay;
