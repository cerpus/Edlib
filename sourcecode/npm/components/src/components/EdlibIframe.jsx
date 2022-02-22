import React from 'react';
import { v4 as uuidv4 } from 'uuid';
import queryString from 'query-string';

import useConfig from '../hooks/useConfig';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

const useGetExternalJwt = () => {
    const { getJwt } = useEdlibComponentsContext();
    const [status, setStatus] = React.useState({
        loading: true,
        error: false,
    });
    const [externalJwt, setExternalJwt] = React.useState(null);

    React.useEffect(() => {
        getJwt()
            .then((externalJwt) => {
                setStatus({
                    loading: false,
                    error: false,
                });
                setExternalJwt(externalJwt);
            })
            .catch((e) =>
                setStatus({
                    loading: false,
                    error: e,
                })
            );
    }, []);

    return {
        ...status,
        externalJwt,
    };
};

const EdlibIframe = ({ height = '100%', onAction, path, params }) => {
    const { edlibFrontend } = useConfig();
    const { loading: loadingToken, externalJwt } = useGetExternalJwt();
    const nonce = React.useMemo(() => uuidv4(), [uuidv4]);
    const [contentRef, setContentRef] = React.useState(null);
    const { configuration, language } = useEdlibComponentsContext();

    React.useEffect(() => {
        const onMessage = (event) => {
            if (!contentRef || contentRef.contentWindow !== event.source) {
                return;
            }

            if (
                !event.data ||
                !event.data.nonce ||
                !event.data.messageType ||
                !event.data.audience ||
                event.data.audience !== 'external' ||
                event.data.nonce !== nonce
            ) {
                return;
            }

            onAction(event.data);
        };

        window.addEventListener('message', onMessage);

        return () => window.removeEventListener('message', onMessage);
    }, [contentRef]);

    if (loadingToken) {
        return <></>;
    }

    return (
        <iframe
            ref={setContentRef}
            style={{
                width: '100%',
                height,
                border: 'none',
            }}
            src={edlibFrontend(
                `${path}?${queryString.stringify(
                    {
                        ...params,
                        jwt: externalJwt,
                        nonce,
                        configuration: JSON.stringify(configuration),
                        language,
                    },
                    { arrayFormat: 'bracket' }
                )}`
            )}
        />
    );
};

export default EdlibIframe;
