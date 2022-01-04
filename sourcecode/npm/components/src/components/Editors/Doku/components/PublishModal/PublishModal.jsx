import React from 'react';
import {
    FromSideModal,
    FromSideModalHeader,
    FromSideModalBody,
} from '../../../../FromSideModal';
import useTranslation from '../../../../../hooks/useTranslation';
import { Box, FormControlLabel, Radio } from '@material-ui/core';
import { Button, Alert } from '@cerpus/ui';
import useConfig from '../../../../../hooks/useConfig';
import useRequestWithToken from '../../../../../hooks/useRequestWithToken';
import styled from 'styled-components';

export const licenses = {
    PRIVATE: 'EDLL',
    AS_IS: 'BY-SA',
    CAN_COPY: 'BY',
    ALL_RIGHTS: 'CC0',
};

const HeaderContent = styled.div`
    display: flex;
    flex-direction: row;
    justify-content: space-between;

    .title {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
`;

const PublishModal = ({ show, setShow, license, setLicense, unpublish }) => {
    const { t } = useTranslation();

    const isPublic =
        [licenses.AS_IS, licenses.CAN_COPY, licenses.ALL_RIGHTS].indexOf(
            license
        ) !== -1;

    return (
        <FromSideModal
            isOpen={show}
            onClose={() => setShow(false)}
            usePortal={false}
        >
            {show && (
                <>
                    <FromSideModalHeader onClose={() => setShow(false)}>
                        <HeaderContent>
                            <div className="title">
                                {t('Publiseringsinnstillinger')}
                            </div>
                            <div>
                                <i style={{ marginRight: 10 }}>
                                    {t('Denne ressursen er nå publisert')}
                                </i>
                                <Button type="danger" onClick={unpublish}>
                                    {t('Avpubliser')}
                                </Button>
                            </div>
                        </HeaderContent>
                    </FromSideModalHeader>
                    <FromSideModalBody>
                        <div>
                            <div>
                                <FormControlLabel
                                    control={
                                        <Radio
                                            color="default"
                                            checked={
                                                licenses.PRIVATE === license
                                            }
                                        />
                                    }
                                    label={t('Privat')}
                                    onClick={() => setLicense(licenses.PRIVATE)}
                                />
                            </div>
                            <Box color="text.disabled">
                                {t(
                                    'Bare du kan sette inn denne ressursen og bare du kan forhåndsvise den i EdLib'
                                )}
                            </Box>
                        </div>
                        <div>
                            <div>
                                <FormControlLabel
                                    control={
                                        <Radio
                                            color="default"
                                            checked={isPublic}
                                        />
                                    }
                                    label={t('Delt/offentlig')}
                                    onClick={() =>
                                        !isPublic && setLicense(licenses.AS_IS)
                                    }
                                />
                            </div>
                            <Box color="text.disabled">
                                {t('Alle kans se denne Doku')}
                            </Box>
                        </div>
                        {isPublic && (
                            <Box ml={5} mt={2}>
                                <div>
                                    {t(
                                        'Hvordan skal andre forfattere kunne bruke denne Doku?'
                                    )}
                                </div>
                                <div>
                                    <FormControlLabel
                                        control={
                                            <Radio
                                                color="default"
                                                checked={
                                                    licenses.AS_IS === license
                                                }
                                            />
                                        }
                                        onClick={() =>
                                            setLicense(licenses.AS_IS)
                                        }
                                        label={t('Kan bruke den som den er')}
                                    />
                                </div>
                                <div>
                                    <FormControlLabel
                                        control={
                                            <Radio
                                                color="default"
                                                checked={
                                                    licenses.CAN_COPY ===
                                                    license
                                                }
                                            />
                                        }
                                        onClick={() =>
                                            setLicense(licenses.CAN_COPY)
                                        }
                                        label={t(
                                            'Kan bruke og/eller kopiere og endre den'
                                        )}
                                    />
                                </div>
                                <div>
                                    <FormControlLabel
                                        control={
                                            <Radio
                                                color="default"
                                                checked={
                                                    licenses.ALL_RIGHTS ===
                                                    license
                                                }
                                            />
                                        }
                                        onClick={() =>
                                            setLicense(licenses.ALL_RIGHTS)
                                        }
                                        label={t(
                                            'Kan bruke, kopiere og/eller få rettigheter til å dele eller videreselge denne doku'
                                        )}
                                    />
                                </div>
                            </Box>
                        )}
                        {false && (
                            <Box mt={2}>
                                <Alert color="danger">
                                    {t('S.ERROR_PUBLISHING')}
                                </Alert>
                            </Box>
                        )}
                    </FromSideModalBody>
                </>
            )}
        </FromSideModal>
    );
};

export default PublishModal;
