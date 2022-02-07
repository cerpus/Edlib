import React from 'react';
import ExternalApplicationDetails from './ExternalApplicationDetails';
import useRequestAction from '../../../../../../hooks/useRequestAction';
import request from '../../../../../../helpers/request';
import moment from 'moment';
import DefaultHookQuery from '../../../../../../containers/DefaultHookQuery';
import ApplicationContext from '../../../../../../contexts/application';
import ConfirmDelete from './ConfirmDelete';
import useFetchWithToken from '../../../../../../hooks/useFetchWithToken.jsx';

const ExternalApplicationDetailsContainer = ({ match }) => {
    const applicationFetchData = useFetchWithToken(
        `/common/applications/${match.params.id}`,
        'GET',
        React.useMemo(() => ({}), [])
    );

    const applicationTokensFetchData = useFetchWithToken(
        `/common/applications/${match.params.id}/access_tokens`,
        'GET',
        React.useMemo(() => ({}), [])
    );

    const { status: createStatus, action: create } = useRequestAction(() =>
        request(
            `/common/applications/${match.params.id}/access_tokens`,
            'POST',
            {
                body: {
                    name: moment().format('DD.MM.YYYY HH:mm'),
                },
            }
        )
    );

    const [token, setToken] = React.useState(null);
    const [accessTokenToDelete, setAccessTokenToDelete] = React.useState(null);

    return (
        <DefaultHookQuery fetchData={applicationFetchData}>
            {({ response: application }) => (
                <ApplicationContext.Provider value={application}>
                    <ExternalApplicationDetails
                        applicationTokensFetchData={applicationTokensFetchData}
                        createStatus={createStatus}
                        onCreate={() =>
                            create(null, (response) => {
                                setToken(response.token);
                                applicationTokensFetchData.refetch();
                            })
                        }
                        token={token}
                        application={application}
                        onDeleteRequest={(accessTokenId) =>
                            setAccessTokenToDelete(accessTokenId)
                        }
                    />
                    <ConfirmDelete
                        accessTokenToDelete={accessTokenToDelete}
                        onClose={() => setAccessTokenToDelete(null)}
                        onDeleted={() => {
                            setAccessTokenToDelete(null);
                            applicationTokensFetchData.refetch();
                        }}
                    />
                </ApplicationContext.Provider>
            )}
        </DefaultHookQuery>
    );
};

export default ExternalApplicationDetailsContainer;
