import React from 'react';
import {
    Modal,
    ModalBody,
    ModalSplitter,
    Spinner,
    useIsDevice,
} from '@cerpus/ui';
import { ArrowForward, Edit as EditIcon } from '@material-ui/icons';
import styled from 'styled-components';
import { useResourceCapabilities } from '../../contexts/ResourceCapabilities';
import useResourceCapabilitiesFlags from '../../hooks/useResourceCapabilities';
import ResourcePreview from '../../containers/ResourcePreview';
import License from '../License';
import ResourceEditCog from '../ResourceEditCog';
import moment from 'moment';
import useTranslation from '../../hooks/useTranslation';
import { useHistory } from 'react-router-dom';
import { resourceCapabilities } from '../../config/resource';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import { Button } from '@material-ui/core';

const Header = styled.div`
    display: flex;
    margin-top: 20px;

    & > div:first-child {
        flex: 1;
    }
`;

const ResourceType = styled.div`
    font-size: 0.7em;
`;

const Title = styled.div`
    font-weight: bold;
    font-size: 1.3em;
`;

const Footer = styled.div`
    margin: 20px 0;
    display: flex;
`;

const Meta = styled.div`
    margin-right: 20px;
    & > div {
        &:first-child {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
    }
`;

const ResourceModal = ({ isOpen, onClose, resource }) => {
    const { t } = useTranslation();
    const history = useHistory();
    const { getUserConfig } = useEdlibComponentsContext();
    const canReturnResources = getUserConfig('canReturnResources');

    const [actionStatus, setActionStatus] = React.useState({
        loading: false,
        error: false,
    });
    const [isCogOpen, setIsCogOpen] = React.useState(false);
    const { onInsert } = useResourceCapabilities();
    const isMobile = useIsDevice('<', 'md');

    const insertResource = React.useCallback(async () => {
        setActionStatus({
            loading: true,
            error: false,
        });

        await onInsert(resource.id);
    }, [onInsert, setActionStatus, resource]);

    const editResource = React.useCallback(() => {
        history.push(`/resources/${resource.id}`);
        onClose();
    }, [resource]);

    const capabilities = useResourceCapabilitiesFlags(resource);

    return (
        <Modal width={900} onClose={onClose} isOpen={isOpen}>
            <ResourcePreview resource={resource}>
                {({ loading, error, frame, license, source }) => {
                    if (loading) {
                        return (
                            <div
                                style={{
                                    display: 'flex',
                                    justifyContent: 'center',
                                    padding: '20px 0',
                                }}
                            >
                                <Spinner />
                            </div>
                        );
                    }

                    if (error) {
                        return <div>Noe skjedde</div>;
                    }

                    return (
                        <>
                            <ModalBody>
                                <Header>
                                    <div>
                                        <ResourceType>{source}</ResourceType>
                                        <Title>{resource.version.name}</Title>
                                        <p>{resource.version.description}</p>
                                    </div>
                                    <div>
                                        {isMobile && (
                                            <ResourceEditCog
                                                isOpen={isCogOpen}
                                                onClose={() =>
                                                    setIsCogOpen(false)
                                                }
                                                onOpen={() =>
                                                    setIsCogOpen(true)
                                                }
                                                onUse={insertResource}
                                                onEdit={editResource}
                                                resource={resource}
                                            />
                                        )}
                                        {!isMobile && (
                                            <>
                                                {canReturnResources && (
                                                    <div>
                                                        <Button
                                                            color="primary"
                                                            variant="contained"
                                                            onClick={
                                                                insertResource
                                                            }
                                                            endIcon={
                                                                <ArrowForward />
                                                            }
                                                            fullWidth
                                                        >
                                                            {t(
                                                                'Bruk ressurs'
                                                            ).toUpperCase()}
                                                        </Button>
                                                    </div>
                                                )}
                                                {capabilities[
                                                    resourceCapabilities.EDIT
                                                ] && (
                                                    <div
                                                        style={{ marginTop: 5 }}
                                                    >
                                                        <Button
                                                            color="primary"
                                                            variant="outlined"
                                                            onClick={
                                                                editResource
                                                            }
                                                            endIcon={
                                                                <EditIcon />
                                                            }
                                                            fullWidth
                                                        >
                                                            {t(
                                                                'Rediger ressurs'
                                                            ).toUpperCase()}
                                                        </Button>
                                                    </div>
                                                )}
                                            </>
                                        )}
                                    </div>
                                </Header>
                            </ModalBody>
                            <ModalSplitter />
                            <ModalBody>
                                <div style={{ marginTop: 20 }}>{frame}</div>
                            </ModalBody>
                            <ModalSplitter />
                            <ModalBody>
                                <Footer>
                                    <Meta>
                                        <div>Publiseringsdato</div>
                                        <div>
                                            {moment(resource.created).format(
                                                'D. MMMM YYYY'
                                            )}
                                        </div>
                                    </Meta>
                                    <Meta>
                                        <div>Lisens</div>
                                        <div>
                                            <License license={license} />
                                        </div>
                                    </Meta>
                                </Footer>
                            </ModalBody>
                        </>
                    );
                }}
            </ResourcePreview>
        </Modal>
    );
};

export default (props) => {
    if (!props.isOpen) {
        return <></>;
    }

    return <ResourceModal {...props} />;
};
