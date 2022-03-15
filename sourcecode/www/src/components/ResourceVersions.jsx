import React from 'react';
import cn from 'classnames';
import { AccountCircle } from '@mui/icons-material';
import { FromSideModal, FromSideModalHeader } from './FromSideModal';
import useFetchWithToken from '../hooks/useFetchWithToken';
import styled from 'styled-components';
import { Spinner, Alert } from '@cerpus/ui';
import ResourcePreviewContainer from '../containers/ResourcePreview';
import { useConfigurationContext } from '../contexts/Configuration.jsx';

const Content = styled.div`
    flex: 1;
    flex-basis: 0;
    overflow-y: auto;
    display: flex;
`;

const VersionList = styled.div`
    width: 250px;
    overflow-y: auto;
    border-right: 1px solid #83df66;
`;

const VersionListItem = styled.div`
    cursor: pointer;
    padding: 5px;

    &.selected,
    :hover {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .title {
        font-weight: bold;
    }
    .user {
        display: flex;
    }

    .user > * {
        display: flex;
        flex-direction: column;
        justify-content: center;
        &:not(:first-child) {
            margin-left: 5px;
        }
    }
`;

const VersionPreview = styled.div`
    flex: 1;
    overflow-y: auto;
`;

const ResourceVersion = ({ resourceId }) => {
    return (
        <ResourcePreviewContainer resource={{ edlibId: resourceId }}>
            {({ error, loading, frame }) => (
                <>
                    {loading && <Spinner />}
                    {error && <div>Noe skjedde</div>}
                    {frame}
                </>
            )}
        </ResourcePreviewContainer>
    );
};

const ResourceVersions = ({ selectedResource, onClose }) => {
    const { edlibApi } = useConfigurationContext();
    const [selectedVersionEdlibId, setSelectedVersionEdlibId] =
        React.useState(null);
    const {
        loading: loadingVersions,
        error: errorLoadingVersions,
        response: versions,
    } = useFetchWithToken(
        edlibApi(
            `/resources/v1/resources/${selectedResource.edlibId}/versions`
        ),
        'GET'
    );

    React.useEffect(() => {
        if (!versions || versions.length === 0 || !selectedResource.edlibId) {
            return setSelectedVersionEdlibId(null);
        }

        if (
            !versions.some(
                (version) => version.edlibId === selectedVersionEdlibId
            )
        ) {
            return setSelectedVersionEdlibId(versions[0].edlibId);
        }
    }, [versions, selectedResource.edlibId]);

    return (
        <FromSideModal
            isOpen={selectedResource}
            onClose={onClose}
            usePortal={false}
        >
            {selectedResource && (
                <div
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        height: '100%',
                    }}
                >
                    <FromSideModalHeader onClose={onClose}>
                        {selectedResource.name}
                    </FromSideModalHeader>
                    <Content>
                        {loadingVersions && <Spinner />}
                        {errorLoadingVersions && (
                            <Alert color="red">
                                Vi fikk problemer med å laste inn versjoner.
                                Vennligst prøv igjen senere.
                            </Alert>
                        )}
                        {!loadingVersions && versions && (
                            <>
                                <VersionList>
                                    {versions.map((version) => (
                                        <VersionListItem
                                            key={version.edlibId}
                                            onClick={() =>
                                                setSelectedVersionEdlibId(
                                                    version.edlibId
                                                )
                                            }
                                            className={cn({
                                                selected:
                                                    version.edlibId ===
                                                    selectedVersionEdlibId,
                                            })}
                                        >
                                            <div className="title">
                                                {version.name}
                                            </div>
                                            <div className="user">
                                                <div>
                                                    <AccountCircle fontSize="inherit" />
                                                </div>
                                                <div>Bruker A</div>
                                            </div>
                                        </VersionListItem>
                                    ))}
                                </VersionList>
                                <VersionPreview>
                                    {selectedVersionEdlibId && (
                                        <ResourceVersion
                                            resourceId={selectedVersionEdlibId}
                                        />
                                    )}
                                </VersionPreview>
                            </>
                        )}
                    </Content>
                </div>
            )}
        </FromSideModal>
    );
};

export default (props) => {
    if (!props.selectedResource) {
        return <></>;
    }

    return <ResourceVersions {...props} />;
};
