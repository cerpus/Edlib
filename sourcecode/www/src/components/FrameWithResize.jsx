import React from 'react';
import styled from 'styled-components';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import debug from 'debug';

const log = debug('edlib-components:FrameWithResize');

const IFrame = styled.iframe`
    border: 0;
    width: 100%;
`;

export default ({ onPostMessage = () => {}, ...props }) => {
    const [contentRef, setContentRef] = React.useState(null);
    const [height, setHeight] = React.useState(400);
    const { jwt } = useEdlibComponentsContext();

    React.useEffect(() => {
        const onMessage = (event) => {
            if (!contentRef || contentRef.contentWindow !== event.source) {
                return;
            }

            onPostMessage(event);

            log('New postmessage event', event);

            if (event.data.action === 'hello') {
                event.source.postMessage(
                    {
                        action: 'hello',
                        context: 'h5p',
                    },
                    event.origin
                );
            } else if (event.data.action === 'prepareResize') {
                setHeight(event.data.scrollHeight);
            } else if (event.data.action === 'resize') {
                setHeight(event.data.scrollHeight);
            } else if (
                event.data.type === 'jwtupdatemsg' &&
                event.data.msg === 'init'
            ) {
                event.source.postMessage(
                    {
                        msg: 'init',
                        type: 'jwtupdatemsg',
                        key: event.data.key,
                        inReplyTo: 'init',
                    },
                    event.origin
                );
            } else if (
                event.data.type === 'jwtupdatemsg' &&
                event.data.msg === 'update'
            ) {
                jwt.getToken()
                    .then((jwt) => {
                        log(
                            'Token request succeeded. Now sending the token back to CA',
                            jwt
                        );
                        event.source.postMessage(
                            {
                                jwt,
                                type: 'jwtupdatemsg',
                                key: event.data.key,
                            },
                            event.origin
                        );
                    })
                    .catch(() =>
                        log('Token request for Content Author failed')
                    );
            }
        };

        window.addEventListener('message', onMessage);

        return () => window.removeEventListener('message', onMessage);
    }, [contentRef, onPostMessage]);

    return <IFrame {...props} ref={setContentRef} height={height} />;
};
