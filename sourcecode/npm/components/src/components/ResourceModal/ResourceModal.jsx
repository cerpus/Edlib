import React from 'react';
import { Spinner } from '@cerpus/ui';
import {
    ArrowForward,
    Edit as EditIcon,
    Close as CloseIcon,
} from '@material-ui/icons';
import styled from 'styled-components';
import { useResourceCapabilities } from '../../contexts/ResourceCapabilities';
import useResourceCapabilitiesFlags from '../../hooks/useResourceCapabilities';
import ResourcePreview from '../../containers/ResourcePreview';
import License from '../License';
import moment from 'moment';
import useTranslation from '../../hooks/useTranslation';
import { useHistory } from 'react-router-dom';
import { resourceCapabilities } from '../../config/resource';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import {
    Box,
    Button,
    DialogContent,
    DialogActions as MuiDialogActions,
    DialogTitle as MuiDialogTitle,
    IconButton,
    makeStyles,
    Typography,
    withStyles,
} from '@material-ui/core';
import { ResourceIcon } from '../Resource';
import ResetMuiDialog from '../ResetMuiDialog';
import useConfig from '../../hooks/useConfig.js';

const Footer = styled.div`
    margin-top: 30px;
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

const useStyles = makeStyles((theme) => ({
    dialogTitle: {
        margin: 0,
        padding: theme.spacing(2),
        display: 'flex',
        justifyContent: 'space-between',
    },
    closeButton: {
        color: theme.palette.grey[500],
    },
    dialog: {
        // height: '100%',
        // maxHeight: '70vh',
    },
}));

const DialogActions = withStyles((theme) => ({
    root: {
        margin: 0,
        padding: theme.spacing(1),
    },
}))(MuiDialogActions);

const ResourceModal = ({ isOpen, onClose, resource }) => {
    const classes = useStyles();
    const { t } = useTranslation();
    const history = useHistory();
    const { getUserConfig } = useEdlibComponentsContext();
    const canReturnResources = getUserConfig('canReturnResources');
    const { edlibFrontend } = useConfig();

    const [actionStatus, setActionStatus] = React.useState({
        loading: false,
        error: false,
    });
    const { onInsert } = useResourceCapabilities();

    const insertResource = React.useCallback(async () => {
        setActionStatus({
            loading: true,
            error: false,
        });

        await onInsert(resource.id, resource.version.id);
    }, [onInsert, setActionStatus, resource]);

    const editResource = React.useCallback(() => {
        history.push(`/resources/${resource.id}`);
        onClose();
    }, [resource]);

    const capabilities = useResourceCapabilitiesFlags(resource);

    return (
        <ResetMuiDialog
            maxWidth="md"
            fullWidth
            onClose={onClose}
            open={isOpen}
            classes={{
                paperScrollPaper: classes.dialog,
            }}
        >
            <MuiDialogTitle disableTypography className={classes.dialogTitle}>
                <Box display="flex">
                    <Box
                        display="flex"
                        flexDirection="column"
                        justifyContent="center"
                    >
                        <ResourceIcon
                            contentTypeInfo={resource.contentTypeInfo}
                            fontSizeRem={2}
                        />
                    </Box>
                    <Box>
                        <Box
                            display="flex"
                            flexDirection="column"
                            justifyContent="center"
                            marginLeft={1}
                        >
                            <Typography variant="h6">
                                {resource.version.title}
                            </Typography>
                        </Box>
                        <Box display="flex" marginLeft={1}>
                            <Typography>
                                <a
                                    href={edlibFrontend(
                                        `/s/resources/${resource.id}`
                                    )}
                                    target="_blank"
                                >
                                    {edlibFrontend(
                                        `/s/resources/${resource.id}`
                                    )}
                                </a>
                            </Typography>
                        </Box>
                    </Box>
                </Box>
                {onClose ? (
                    <Box
                        display="flex"
                        flexDirection="column"
                        justifyContent="center"
                    >
                        <IconButton
                            aria-label="close"
                            className={classes.closeButton}
                            onClick={onClose}
                        >
                            <CloseIcon />
                        </IconButton>
                    </Box>
                ) : null}
            </MuiDialogTitle>
            <DialogContent dividers>
                <ResourcePreview resource={resource}>
                    {({ loading, error, frame }) => {
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
                                <div>{frame}</div>
                                <Footer>
                                    <Meta>
                                        <div>Publiseringsdato</div>
                                        <div>
                                            {moment(
                                                resource.version.createdAt
                                            ).format('D. MMMM YYYY')}
                                        </div>
                                    </Meta>
                                    <Meta>
                                        <div>Lisens</div>
                                        <div>
                                            <License
                                                license={
                                                    resource.version.license
                                                }
                                            />
                                        </div>
                                    </Meta>
                                </Footer>
                            </>
                        );
                    }}
                </ResourcePreview>
            </DialogContent>
            <DialogActions>
                {capabilities[resourceCapabilities.EDIT] && (
                    <Button
                        color="primary"
                        variant="contained"
                        onClick={editResource}
                        startIcon={<EditIcon />}
                    >
                        {t('Rediger ressurs').toUpperCase()}
                    </Button>
                )}
                {canReturnResources && (
                    <Button
                        color="primary"
                        variant="contained"
                        onClick={insertResource}
                        startIcon={<ArrowForward />}
                    >
                        {t('Bruk ressurs').toUpperCase()}
                    </Button>
                )}
            </DialogActions>
        </ResetMuiDialog>
    );
};

export default (props) => {
    if (!props.isOpen) {
        return <></>;
    }

    return <ResourceModal {...props} />;
};
