import React from 'react';
import {
    Breadcrumbs,
    Box,
    Container,
    Grid,
    Typography,
    Paper,
    Table,
    TableHead,
    TableRow,
    TableCell,
    TableBody,
    Button,
    CircularProgress,
} from '@material-ui/core';
import Link from '../../../../../../components/Link.jsx';
import CreateExternalApplication from './CreateExternalApplication.jsx';

const Home = ({
    onGoToDetails,
    loading,
    applications,
    refetchApplications,
}) => {
    const [createNew, setCreateNew] = React.useState(false);

    return (
        <Container maxWidth={false}>
            <Grid component={Box} container paddingY={2}>
                <Grid item>
                    <Breadcrumbs aria-label="breadcrumb">
                        <Link to="/">Edlib admin</Link>
                        <Link to="/settings">Settings</Link>
                        <Typography color="textPrimary">
                            External applications
                        </Typography>
                    </Breadcrumbs>
                </Grid>
            </Grid>
            <Grid container component={Box} paddingBottom={2}>
                <Grid item md={12}>
                    <Typography variant="h2">External applications</Typography>
                </Grid>
            </Grid>
            <Grid container>
                <Grid item md={12}>
                    <Paper>
                        <Box padding={2}>
                            <Button
                                variant="contained"
                                color="primary"
                                onClick={() => setCreateNew(true)}
                            >
                                Create new
                            </Button>
                        </Box>
                        {loading && <CircularProgress />}
                        {!loading && (
                            <Table>
                                <TableHead>
                                    <TableRow>
                                        <TableCell>Id</TableCell>
                                        <TableCell>Name</TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {applications.map(({ id, name }) => (
                                        <TableRow
                                            key={id}
                                            hover
                                            onClick={() => onGoToDetails(id)}
                                        >
                                            <TableCell width={260}>
                                                {id}
                                            </TableCell>
                                            <TableCell>{name}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </Paper>
                </Grid>
            </Grid>
            <CreateExternalApplication
                isOpen={createNew}
                onClose={() => setCreateNew(false)}
                onAdded={() => {
                    setCreateNew(false);
                    refetchApplications();
                }}
            />
        </Container>
    );
};

export default Home;
