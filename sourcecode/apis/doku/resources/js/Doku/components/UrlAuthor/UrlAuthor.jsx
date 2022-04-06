import React from 'react';
import styled from 'styled-components';
import { Button, TextField } from '@mui/material';
import UrlDisplay from './UrlDisplay';
import useConfig from '../../hooks/useConfig';
import atomicTypes from '../../config/atomicTypes';
import useRequestWithToken from '../../hooks/useRequestWithToken';
import useTranslation from '../../hooks/useTranslation';

const Content = styled.div`
    max-width: 65vw;
    margin: 10px auto;
`;

const Wrapper = styled.div`
    max-height: 100%;
    overflow-y: auto;
`;

const StyledInput = styled.div`
    .input-row {
        display: flex;

        & > *:first-child {
            flex: 1 1 100%;
            display: flex;

            & > *:first-child {
                flex: 1 1 100%;
                display: flex;
                margin-right: 20px;
            }
        }
    }

    input {
        flex: 1 1 100%;
        padding: 20px 10px;
    }
`;

const UrlAuthor = ({ onUse }) => {
    const { edlib } = useConfig();
    const request = useRequestWithToken();
    const [inputValue, setInputValue] = React.useState('');
    const [urlForBody, setUrlForBody] = React.useState('');
    const [isValidUrl, setIsValidUrl] = React.useState(false);
    const urlRegEx = new RegExp(/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w.-]+)+[\w\-._~:/?#[\]@!$&'()*+,;=]+$/, 'ig');
    const validateUrl = (url) => urlRegEx.test(url);
    const { t } = useTranslation();

    const handleOnUse = React.useCallback(
        async ({ type, format }) => {
            if (type === 'url') {
                const response = await request(
                    edlib('/resources/v1/resources/lti-links'),
                    'POST',
                    {
                        body: { url: urlForBody },
                    }
                );

                onUse(atomicTypes.EDLIB_URL_RESOURCE, response.linkId, {
                    display: format,
                });
            }
        },
        [onUse, urlForBody]
    );

    return (
        <Wrapper>
            <Content>
                <StyledInput>
                    <label>{t('Søk')}</label>
                    <div className="input-row">
                        <div>
                            <TextField
                                id="url"
                                type="url"
                                value={inputValue}
                                onChange={(e) => {
                                    setInputValue(e.target.value);
                                    setIsValidUrl(validateUrl(e.target.value));
                                }}
                                placeholder="https://www.example.no"
                                error={!isValidUrl}
                            />
                        </div>
                        <Button
                            onClick={() => setUrlForBody(inputValue)}
                            color="primary"
                            variant="outlined"
                        >
                            {t('Forhåndsvis')}
                        </Button>
                    </div>
                </StyledInput>
                {urlForBody && (
                    <UrlDisplay url={urlForBody} onUse={handleOnUse} />
                )}
            </Content>
        </Wrapper>
    );
};

export default UrlAuthor;
