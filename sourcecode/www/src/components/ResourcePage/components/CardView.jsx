import React from 'react';
import { Box, Button, Grid, Paper, Tooltip } from '@mui/material';
import { makeStyles } from 'tss-react/mui';
import { getResourceName, ResourceIcon } from '../../Resource';
import useTranslation from '../../../hooks/useTranslation.js';
import PublishedTag from '../../PublishedTag.jsx';
import { MoreVert as MoreVertIcon, Preview } from '@mui/icons-material';
import ResourceEditCog from '../../ResourceEditCog.jsx';
import ViewContainer from './ViewContainer.jsx';
import { capitalize } from 'lodash';
const useStyles = makeStyles()((theme) => ({
    gridItem: {
        display: 'flex',
    },
    paper: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'space-between',
    },
    title: {
        fontWeight: '400',
        fontSize: '1.2rem',
        wordBreak: 'break-word',
    },
    subtitle: {
        fontSize: '0.875rem',
        fontWeight: '400',
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
                            xs={12}
                            sm={6}
                            lg={4}
                            className={classes.gridItem}
                        >
                            <Paper
                                className={classes.paper}
                                sx={{
                                    padding: {
                                        xs: 1,
                                        md: 2,
                                    }
                                }}
                            >
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
                                        <Tooltip title={capitalize(t('preview'))}>
                                            <Button
                                                size="small"
                                                color="secondary"
                                                variant="contained"
                                                onClick={() => setSelectedResource(resource)}
                                                aria-label={t('preview')}
                                                sx={{
                                                    minWidth: 0,
                                                }}
                                            >
                                                <Preview />
                                            </Button>
                                        </Tooltip>
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
