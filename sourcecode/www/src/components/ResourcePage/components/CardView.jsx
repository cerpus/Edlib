import React from 'react';
import { Box, Button, Grid, Paper } from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import { getResourceName, ResourceIcon } from '../../Resource';
import useTranslation from '../../../hooks/useTranslation.js';
import PublishedTag from '../../PublishedTag.jsx';
import { MoreVert as MoreVertIcon } from '@mui/icons-material';
import ResourceEditCog from '../../ResourceEditCog.jsx';
import ViewContainer from './ViewContainer.jsx';

const useStyles = makeStyles()((theme) => ({
    gridItem: {
        display: 'flex',
    },
    paper: {
        padding: theme.spacing(2),
        flex: 1,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'space-between',
    },
    title: {
        fontWeight: '500',
        fontSize: '1.2em',
        wordBreak: 'break-word',
    },
    subtitle: {
        fontSize: '0.8em',
    },
    buttons: {
        '& > button': {
            marginLeft: theme.spacing(1),
        },
    },
}));

const CardView = ({ resources, showDeleteButton = false, refetch }) => {
    const { classes } = useStyles();
    const { t } = useTranslation();

    return (
        <ViewContainer
            showDeleteButton={showDeleteButton}
            refetch={refetch}
            resources={resources}
        >
            {({ cogProps, setSelectedResource, resources }) => (
                <Grid container spacing={1}>
                    {resources.map((resource) => (
                        <Grid
                            key={resource.id}
                            item
                            xs={6}
                            lg={4}
                            className={classes.gridItem}
                        >
                            <Paper className={classes.paper}>
                                <Box
                                    display="flex"
                                    justifyContent="space-between"
                                >
                                    <Box>
                                        <div className={classes.title}>
                                            {resource.version.title}
                                        </div>
                                        <Box
                                            className={classes.subtitle}
                                            mt={1}
                                        >
                                            {getResourceName(resource)}
                                        </Box>
                                    </Box>
                                    <Box>
                                        <ResourceIcon
                                            contentTypeInfo={
                                                resource.contentTypeInfo
                                            }
                                            resourceVersion={resource.version}
                                            fontSizeRem={2}
                                        />
                                    </Box>
                                </Box>
                                <Box
                                    mt={1}
                                    display="flex"
                                    justifyContent="space-between"
                                >
                                    <Box
                                        display="flex"
                                        flexDirection="column"
                                        justifyContent="center"
                                    >
                                        <PublishedTag
                                            isPublished={
                                                resource.version.isPublished
                                            }
                                            isDraft={resource.version.isDraft}
                                        />
                                    </Box>
                                    <Box className={classes.buttons}>
                                        <ResourceEditCog
                                            {...cogProps(resource)}
                                        >
                                            {({ ref, onOpen }) => (
                                                <Button
                                                    size="small"
                                                    color="grey"
                                                    variant="contained"
                                                    style={{ minWidth: 0 }}
                                                    onClick={onOpen}
                                                    ref={ref}
                                                >
                                                    <MoreVertIcon />
                                                </Button>
                                            )}
                                        </ResourceEditCog>
                                        <Button
                                            size="small"
                                            color="secondary"
                                            variant="contained"
                                            onClick={() =>
                                                setSelectedResource(resource)
                                            }
                                        >
                                            {t('preview')}
                                        </Button>
                                    </Box>
                                </Box>
                            </Paper>
                        </Grid>
                    ))}
                </Grid>
            )}
        </ViewContainer>
    );
};

export default CardView;
