import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Axios from '../../../../utils/axiosSetup';
import FormControl from '@material-ui/core/FormControl';
import NativeSelect from '@material-ui/core/NativeSelect';
import { withStyles } from '@material-ui/core/styles';

class AdapterSelector extends Component {
    static propTypes = {
        changeUrl: PropTypes.string,
        adapters: PropTypes.array,
        current: PropTypes.string,
        classes: PropTypes.object.isRequired,
    };

    static defaultProps = {
        changeUrl: '/h5p/adapter',
        adapters: [],
        current: '',
    };

    constructor(props) {
        super(props);

        this.handleChange = this.handleChange.bind(this);
    }

    handleChange(selectedAdapter) {
        Axios.post(this.props.changeUrl,
            {
                adapterMode: selectedAdapter,
            })
            .then(data => window.location.href = data.data.url)
            .catch(data => console.log(data));
    }

    render() {
        const { classes } = this.props;
        return (
            <div className="adapterSelector">
                <FormControl
                    className={classes.formControl}
                >
                    <NativeSelect
                        value={this.props.current}
                        onChange={event => this.handleChange(event.target.value)}
                        name="adapterMode"
                        className={classes.nativeSelect}
                    >
                        {this.props.adapters.map(data => (<option key={data.key} value={data.key}>{data.name}</option>))}
                    </NativeSelect>
                </FormControl>
            </div>
        );
    }
}

const styles = theme => ({
    root: {
        display: 'flex',
        flexWrap: 'wrap',
    },
    formControl: {
        width: '100%',
        minWidth: 120,
    },
    nativeSelect: {
        fontSize: 'initial',
    },
});

export default withStyles(styles)(AdapterSelector);
