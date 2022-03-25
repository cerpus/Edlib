import React from 'react';
import useConfig from '../hooks/useConfig';
import useFetchWithToken from '../hooks/useFetchWithToken';
import PostingFrame from './PostingFrame';
import FrameWithResize from './FrameWithResize';
import { Spinner } from '@cerpus/ui';
import useFetch from '../hooks/useFetch';

const LtiLaunch = ({ launchUrl, usersForLti = null }) => {
    const { edlib } = useConfig();

    const { error, loading, response: preview } = useFetch(
        edlib(`/lti/v1/lti/launch`),
        'GET',
        React.useMemo(
            () => ({
                query: {
                    launchUrl,
                    ltiUserId: usersForLti && usersForLti.ltiUserId,
                    cerpusUserId: usersForLti && usersForLti.cerpusUserId,
                },
            }),
            [launchUrl, usersForLti]
        ),
        false,
        false
    );

    if (error) {
        return <div>Noe skjedde</div>;
    }

    if (loading || !preview) {
        return <Spinner />;
    }
    return (
        <PostingFrame
            frame={FrameWithResize}
            method={preview.method}
            params={preview.params}
            url={preview.url}
        />
    );
};

export default LtiLaunch;
