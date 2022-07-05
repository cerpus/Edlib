import React from 'react';
import { Drawer, makeStyles, Toolbar } from '@material-ui/core';
import NewHeader from './NewHeader.jsx';
import Sidebar from './Sidebar.jsx';

const drawerWidth = 240;

const useStyles = makeStyles((theme) => ({
    root: {
        display: 'flex',
    },
    appBar: {
        zIndex: theme.zIndex.drawer + 1,
    },
    drawer: {
        width: drawerWidth,
        flexShrink: 0,
    },
    drawerPaper: {
        width: drawerWidth,
    },
    drawerContainer: {
        overflow: 'auto',
    },
    content: {
        flexGrow: 1,
        padding: theme.spacing(1),
        backgroundColor: '#F6F6F7',
        height: 'calc(100vh - 80px)',
    },
}));

const Page = ({ children }) => {
    const classes = useStyles();

    return (
        <div className={classes.root}>
            <NewHeader className={classes.appBar} />
            <Drawer
                className={classes.drawer}
                variant="permanent"
                classes={{
                    paper: classes.drawerPaper,
                }}
            >
                <Toolbar />
                <div className={classes.drawerContainer}>
                    <Sidebar />
                </div>
            </Drawer>
            <main className={classes.content}>
                <Toolbar />
                {children}
            </main>
        </div>
    );
};

export default Page;
