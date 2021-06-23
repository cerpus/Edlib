import React from 'react';
import styled from 'styled-components';
import { useHistory } from 'react-router-dom';
import ResourceEditCog from '../ResourceEditCog';
import { useResourceCapabilities } from '../../contexts/ResourceCapabilities';
import License from '../License';
import { getResourceName, ResourceIcon } from '../Resource';
import ResourceVersions from '../ResourceVersions';
import useTranslation from '../../hooks/useTranslation';
import moment from 'moment';
import useArray from '../../hooks/useArray';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceColumns from '../../constants/resourceColumns';
import { Button, Dialog, DialogActions, DialogTitle } from '@material-ui/core';

const Row = styled.div`
    display: grid;
    grid-template-columns: [icon] 80px [title] minmax(0, 1fr) [date] 100px [author] 100px [language] 60px [license] 80px [actions] 60px;
`;

const BodyRow = styled(Row)`
    box-shadow: 0 0 5px 6px rgba(0, 0, 0, 0.04);
    background-color: white;
    margin-bottom: 20px;
    cursor: pointer;
    min-height: 72px;
    transition: box-shadow 0.01s ease-in-out;

    &:hover {
        box-shadow: 0 0 5px 6px rgba(0, 0, 0, 0.1);
    }
`;

const HeaderRow = styled(Row)`
    font-weight: bold;
    margin-bottom: 8px;
    font-size: ${(props) => props.theme.rem(0.8)};
`;

const Cell = styled.div`
    ${(props) =>
        props.name &&
        `
        grid-column-start: ${props.name};
  `}
    ${(props) =>
        props.vc &&
        `
        display: flex;
        flex-direction: column;
        justify-content: center;
  `}
    ${(props) =>
        props.secondary &&
        `
        color: #595959;
        font-size: ${props.theme.rem(0.8)};
  `}
`;

const ImageCell = styled(Cell)`
    padding: 0;

    img {
        max-width: 100%;
    }
`;

const ResourceIconCell = styled(Cell)`
    display: flex;
`;

const Title = styled.div`
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-right: 20px;
    font-size: ${(props) => props.theme.rem(1.2)};
`;

const UnderTitle = styled.div`
    font-size: ${(props) => props.theme.rem(0.8)};
`;

const CogWrapper = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    flex: 1;
`;

const ResourceTable = ({
    resources,
    onResourceClick,
    showDeleteButton = false,
}) => {
    const { t } = useTranslation();
    const { onInsert, onRemove } = useResourceCapabilities();
    const { getUserConfig } = useEdlibComponentsContext();
    const hideResourceColumns = getUserConfig('hideResourceColumns');
    const [currentEditContextId, setCurrentEditContextId] = React.useState(
        null
    );
    const [resourceVersionModal, setResourceVersionModal] = React.useState(
        null
    );
    const [
        showConfirmDeletionModal,
        setShowConfirmDeletionModal,
    ] = React.useState(false);
    const idsToHide = useArray();
    const history = useHistory();

    return (
        <>
            <HeaderRow>
                <div>{t('Innhold')}</div>
                <div />
                <div>{t('Sist endret')}</div>
                <div>{t('Forfatter')}</div>
                <div>{t('Språk')}</div>
                {hideResourceColumns.indexOf(resourceColumns.LICENSE) ===
                    -1 && <div>{t('Lisenser')}</div>}
                <div />
            </HeaderRow>
            {resources
                .filter((resource) => !idsToHide.has(resource.id))
                .map((resource) => (
                    <BodyRow
                        onClick={() => onResourceClick(resource)}
                        key={resource.id}
                    >
                        <ImageCell vc>
                            <ResourceIcon
                                resourceVersion={resource.version}
                                fontSizeRem={2}
                            />
                        </ImageCell>
                        <Cell vc>
                            <Title>{resource.version.title}</Title>
                            <UnderTitle>{getResourceName(resource)}</UnderTitle>
                        </Cell>
                        <Cell vc secondary>
                            {moment(resource.version.createdAt).format(
                                'D. MMM YY'
                            )}
                        </Cell>
                        <Cell vc secondary>
                            {resource.version.authorOverwrite}
                        </Cell>
                        <Cell vc secondary>
                            {resource.version.language}
                        </Cell>
                        {hideResourceColumns.indexOf(
                            resourceColumns.LICENSE
                        ) === -1 && (
                            <Cell vc secondary>
                                <License license={resource.version.license} />
                            </Cell>
                        )}
                        <ResourceIconCell
                            className="actions"
                            secondary
                            name="actions"
                        >
                            <CogWrapper>
                                <ResourceEditCog
                                    resource={resource}
                                    showDeleteButton={showDeleteButton}
                                    onOpen={() =>
                                        setCurrentEditContextId(resource.id)
                                    }
                                    onClose={() =>
                                        setCurrentEditContextId(null)
                                    }
                                    isOpen={
                                        resource.id === currentEditContextId
                                    }
                                    onEdit={() => {
                                        setCurrentEditContextId(null);
                                        history.push(
                                            `/resources/${resource.id}`
                                        );
                                    }}
                                    onTranslate={() => {
                                        setCurrentEditContextId(null);
                                        history.push(
                                            `/resources/${resource.id}/nno`
                                        );
                                    }}
                                    onUse={async () => {
                                        setCurrentEditContextId(null);
                                        await onInsert(
                                            resource.id,
                                            resource.version.id
                                        );
                                    }}
                                    onShowVersions={() =>
                                        setResourceVersionModal(resource)
                                    }
                                    onRemove={() =>
                                        setShowConfirmDeletionModal(resource.id)
                                    }
                                />
                            </CogWrapper>
                        </ResourceIconCell>
                    </BodyRow>
                ))}
            <ResourceVersions
                onClose={() => setResourceVersionModal(null)}
                selectedResource={resourceVersionModal}
            />
            <Dialog
                open={showConfirmDeletionModal}
                onClose={() => setShowConfirmDeletionModal(false)}
                maxWidth="sm"
                fullWidth
            >
                <DialogTitle>Er du sikker?</DialogTitle>
                <DialogActions>
                    <Button
                        color="primary"
                        variant="outlined"
                        onClick={() => setShowConfirmDeletionModal(false)}
                    >
                        Lukk
                    </Button>
                    <Button
                        color="primary"
                        variant="contained"
                        style={{ marginLeft: 5 }}
                        onClick={() => {
                            onRemove(showConfirmDeletionModal).then(() => {
                                idsToHide.push(showConfirmDeletionModal);
                                setShowConfirmDeletionModal(false);
                            });
                        }}
                    >
                        Fjern
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
};

export default ResourceTable;
