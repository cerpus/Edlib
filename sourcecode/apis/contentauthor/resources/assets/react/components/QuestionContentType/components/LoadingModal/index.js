import React from 'react';
import PropTypes from 'prop-types';
import { Modal, Icon } from '@material-ui/core';
import { withStyles } from '@material-ui/core/styles';

const styles = theme => ({
    icon: {
        fontSize: 100,
    },
    title: {
        fontSize: 25,
        fontWeight: 'bold',
    },
    paper: {
        position: 'absolute',
        width: theme.spacing(50),
        backgroundColor: '#465c6c',
        boxShadow: theme.shadows[5],
        padding: theme.spacing(4),
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        color: 'white',
        display: 'flex',
        alignItems: 'center',
        flexDirection: 'column',
        justifyContent: 'center',
    },
    image: {
        height: 100,
    },
});

function LoadingModal(props) {
    const {
        open,
        classes,
        contentIcon,
        contentTitle,
        contentText,
    } = props;

    return (
        <Modal
            open={open}
            classes={classes.root}
        >
            <div
                className={classes.paper}
            >
                <Icon className={classes.icon}>sync</Icon>
                <p>{contentText}</p>
                <img src={contentIcon} alt="image" className={classes.image} />
                <span className={classes.title}>
                    {contentTitle}
                </span>
            </div>
        </Modal>
    );
}

LoadingModal.propTypes = {
    open: PropTypes.bool,
    onConfirm: PropTypes.func,
    classes: PropTypes.object.isRequired,
    contentIcon: PropTypes.string,
    contentTitle: PropTypes.string,
    contentText: PropTypes.oneOfType([PropTypes.string, PropTypes.object]),
};

LoadingModal.defaultProps = {
    open: false,
};

export default withStyles(styles)(LoadingModal);
