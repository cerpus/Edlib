import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage, injectIntl } from 'react-intl';

import { withStyles } from '@material-ui/core/styles';
import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import MenuItem from '@material-ui/core/MenuItem';
import Chip from '@material-ui/core/Chip';
import Button from '@material-ui/core/Button';
import Downshift from 'downshift';


class TagsManager extends React.Component {
    static propTypes = {
        tags: PropTypes.array,
        onChange: PropTypes.func,
    };

    static defaultProps = {
        tags: [],
        onChange: null,
    };

    constructor(props) {
        super(props);

        this.state = {
            addNewOpen: false,
            typeaheadValues: [],
            searchText: '',
        };

        this.handleExpandNew = this.handleExpandNew.bind(this);
        this.handleNew = this.handleNew.bind(this);
        this.handleRemove = this.handleRemove.bind(this);
        this.handleTypeahead = this.handleTypeahead.bind(this);
        this.renderTextInput = this.renderTextInput.bind(this);
        this.renderSuggestion = this.renderSuggestion.bind(this);
    }

    handleExpandNew() {
        this.setState({
            addNewOpen: !this.state.addNewOpen,
        });
    }

    handleNew(value) {
        if (this.props.onChange) {
            let newTags = [].concat(this.props.tags);
            let newValue = '';

            if (typeof (value) === 'object') {
                newValue = value.text.trim().toLocaleLowerCase();
            } else {
                newValue = value.trim().toLocaleLowerCase();
            }

            if (newTags.findIndex(tag => {
                return (tag.toLocaleLowerCase() === newValue);
            }) === -1) {
                newTags.push(newValue);
                this.props.onChange(newTags);
            }
            this.setState({
                searchText: '',
            });
        }
    }

    handleRemove(remove) {
        if (this.props.onChange) {
            this.props.onChange(
                this.props.tags.filter(tag => {
                    return (tag !== remove);
                })
            );
        }
    }

    handleTypeahead(searchText) {
        searchText = searchText.trim();

        this.setState({
            searchText: searchText,
        });

        //Return early if the searchText is empty
        if (searchText.length === 0) {
            return;
        }

        // Axios.get('/v1/questionsets/tags', {
        //     params: {
        //         search: searchText
        //     }
        // })
        //     .then((response) => {
        //         this.setState({
        //             typeaheadValues: response.data.keywords,
        //         });
        //     })
        //     .catch((error) => {
        //         console.log('(' + error.response.data.request_id + ') ' + error.response.data.message);
        //     });
    }

    renderSuggestion({ suggestion, index, itemProps, highlightedIndex }) {
        const isHighlighted = highlightedIndex === index;
        const { classes } = this.props;
        return (
            <MenuItem
                {...itemProps}
                key={suggestion}
                selected={isHighlighted}
                component="div"
                className={classes.menuItem}
            >
                {suggestion}
            </MenuItem>
        );
    }

    renderIconAddButton() {
        return (
            <Button
                onClick={this.handleExpandNew}
                className={this.props.classes.iconButtonAdd}
                style={{
                    borderRadius: (this.state.addNewOpen ? '15px 0 0 15px' : '15px'),
                }}
            >
                #
            </Button>
        );
    }

    renderTextInput(inputProps) {
        const {
            InputProps,
            classes,
            ref,
            ...other
        } = inputProps;

        return (
            <TextField
                InputProps={{
                    inputRef: ref,
                    classes: {
                        root: classes.inputRoot,
                    },
                    ...InputProps,
                }}
                {...other}
            />
        );
    }

    renderInput() {
        if (this.state.addNewOpen) {
            const {
                classes,
                intl
            } = this.props;
            return (
                <Downshift
                    id="downshift-simple"
                    inputValue={this.state.searchText}
                    stateReducer={(state, changes) => {
                        switch (changes.type) {
                            case Downshift.stateChangeTypes.keyDownEnter:
                            case Downshift.stateChangeTypes.clickItem:
                                this.handleNew(changes.selectedItem);
                                break;
                        }
                        return changes;
                    }}
                >
                    {({
                        getInputProps,
                        getItemProps,
                        isOpen,
                        inputValue,
                        highlightedIndex
                    }) => (
                        <div className={classes.container}>
                            {this.renderTextInput({
                                fullWidth: true,
                                classes,
                                InputProps: getInputProps({
                                    onChange: event => this.handleTypeahead(event.currentTarget.value),
                                    value: inputValue,
                                    placeholder: intl.formatMessage({ id: 'TAGSMANAGER.SEARCH_OR_CREATE_NEW_TAG' }),
                                }),
                                autoFocus: true,
                                onKeyPress: (e) => {
                                    if (e.key.toLowerCase() === 'enter') {
                                        this.handleNew(this.state.searchText);
                                    }
                                }
                            })}
                            {isOpen ? (
                                <Paper className={classes.paper} square>
                                    {this.state.typeaheadValues.map((suggestion, index) =>
                                        this.renderSuggestion({
                                            suggestion,
                                            index,
                                            itemProps: getItemProps({
                                                item: suggestion,
                                            }),
                                            highlightedIndex,
                                        }),
                                    )}
                                </Paper>
                            ) : null}
                        </div>
                    )}
                </Downshift>
            );
        }

        return null;
    }

    renderCloseButton() {
        if (this.state.addNewOpen) {
            return (
                <Button
                    onClick={this.handleExpandNew}
                    className={this.props.classes.buttonClose}
                >
                    <FormattedMessage id="TAGSMANAGER.CLOSE"/>
                </Button>
            );
        }

        return null;
    }

    renderLabelAddButton() {
        if (this.state.addNewOpen === false) {
            return (
                <Button
                    onClick={this.handleExpandNew}
                    className={this.props.classes.buttonAdd}
                >
                    <FormattedMessage id="TAGSMANAGER.ADD_TAGS"/>
                </Button>
            );
        }

        return null;
    }

    renderTags() {
        return this.props.tags.map((tag) => {
            return (
                <Chip
                    onDelete={() => this.handleRemove(tag)}
                    key={'tag-' + tag}
                    className={this.props.classes.chip}
                    label={tag}
                />
            );
        });
    }

    render() {
        return (
            <div className="tagsmanager-container">
                <div className="tagmanager-new-wrapper">
                    {this.renderIconAddButton()}
                    {this.renderInput()}
                    {this.renderCloseButton()}
                </div>
                {this.renderLabelAddButton()}
                <div className="tagsmanager-list">
                    {this.renderTags()}
                </div>
            </div>
        );
    }
}

const styles = theme => ({
    root: {
        flexGrow: 1,
        height: 250,
    },
    container: {
        flexGrow: 1,
        position: 'relative',
        display: 'inline-flex',
        padding: '0 10px',
    },
    paper: {
        position: 'absolute',
        zIndex: 1,
        marginTop: theme.spacing(1),
        left: 0,
        right: 0,
        top: '25px',
    },
    chip: {
        margin: `${theme.spacing(0.5)}px ${theme.spacing(0.25)}px`,
        fontSize: 'inherit',
    },
    inputRoot: {
        flexWrap: 'wrap',
        fontSize: 'inherit',
    },
    divider: {
        height: theme.spacing(2),
    },
    menuItem: {
        fontSize: 'inherit',
    },
    buttonAdd: {
        minWidth: '36px',
        fontFamily: "'Lato', sans-serif",
        fontSize: '1.6rem',
        textTransform: 'none',
        padding: '0 5px',
        fontWeight: '400 !important',
    },
    buttonClose: {
        color: '#272E33',
        height: '48px',
        minWidth: '48px',
        lineHeight: '48px',
        borderRadius: '0 15px 15px 0',
        padding: '0 15px',
        fontSize: '0.8em',
        borderLeft: '2px solid #5d6e79',
        fontWeight: 'bold',
    },
    iconButtonAdd: {
        color: '#272E33',
        height: '48px',
        width: '48px',
        minWidth: '48px',
        lineHeight: '48px',
        padding: '0',
        fontSize: '1.7em'
    }
});

export default withStyles(styles)(injectIntl(TagsManager));
