import React, { useMemo, useState } from 'react';
import { AlertBox, SaveBox, Publish, Lock } from './components';
import SidebarCommonComponents from './SidebarCommonComponents';
import { useForm } from '../../contexts/FormContext';
import { injectIntl } from 'react-intl';
import PropTypes from 'prop-types';
import { compare } from '../../utils/utils';
import Box from '@material-ui/core/Box';
import Paper from '@material-ui/core/Paper';
import Accordion from '@material-ui/core/Accordion';
import AccordionSummary from '@material-ui/core/AccordionSummary';
import AccordionDetails from '@material-ui/core/AccordionDetails';
import Warning from '@material-ui/icons/Warning';
import ExpandMoreRounded from '@material-ui/icons/ExpandMoreRounded';
import { makeStyles } from '@material-ui/core/styles';

const useStyle = makeStyles((theme) => ({
    accordionInfo: {
        display: 'flex',
        flex: '1 1 auto',
        justifyContent: 'end',
        opacity: 0.6,
        fontStyle: 'italic',
    },
    accordionTitle: {
        fontSize: '1.6rem',
        fontWeight: '400',
    }
}));

const Sidebar = ({
    customSetup,
    customComponents,
    onSave,
    intl,
    onSaveCallback,
    componentsOrder,
}) => {
    // eslint-disable-next-line no-undef
    const setup = customSetup || editorSetup;
    const {
        dispatch,
        state,
        initialState: { isDraft: isInitialDraft },
    } = useForm();
    const { locked, lockedProperties, pulseUrl } = setup;
    const [isLocked, setLocked] = useState(locked);
    const classes = useStyle();
    const onChange = (type, payload) => dispatch({ type: type, payload });
    const toggleLock = () => setLocked(!isLocked);

    const components = useMemo(() => {
        let commonComponents = SidebarCommonComponents(
            setup,
            onChange,
            state,
            intl
        );
        commonComponents = commonComponents
            .concat(customComponents)
            .filter((box) => typeof box !== 'undefined' && box !== null)
            .map((component) => {
                component.order = componentsOrder.indexOf(component.id);
                return component;
            })
            .sort(compare('order'));
        return commonComponents;
    }, [state]);

    return (
        <Box>
            <Paper className="sidebar">
                {isInitialDraft && (
                    <Box
                        style={{
                            backgroundColor: '#FFECB3',
                        }}
                        display="flex"
                        flexDirection="row"
                        padding={1}
                    >
                        <Box pr={1}>
                            <Warning />
                        </Box>
                        <Box>
                            <Box>
                                <strong>
                                    {intl.formatMessage({
                                        id: 'unpublished_changes',
                                    })}
                                </strong>
                            </Box>
                            <Box>
                                {intl.formatMessage({
                                    id: 'unpublished_changes_explain',
                                })}
                            </Box>
                        </Box>
                    </Box>
                )}
                <Box padding={1}>
                    {!isLocked && (
                        <SaveBox
                            onSave={onSave}
                            onSaveCallback={onSaveCallback}
                            pulseUrl={pulseUrl}
                        />
                    )}
                    {isLocked && (
                        <Lock
                            {...lockedProperties}
                            lockReleased={() => toggleLock()}
                        />
                    )}
                    <AlertBox />
                    <Publish
                        label={intl.formatMessage({
                            id: 'SHARINGCOMPONENT.ISPUBLISHED',
                        })}
                    />
                    {components.map((box, index) => (
                        <Accordion key={index}>
                            <AccordionSummary
                                expandIcon={<ExpandMoreRounded />}
                                className={classes.accordionTitle}
                            >
                                {box.title}
                                <div className={classes.accordionInfo}>
                                    {box.info}
                                </div>
                            </AccordionSummary>
                            <AccordionDetails>
                                {box.component || box}
                            </AccordionDetails>
                        </Accordion>
                    ))}
                </Box>
            </Paper>
        </Box>
    );
};

Sidebar.propTypes = {
    customSetup: PropTypes.object,
    customComponents: PropTypes.array,
    onSave: PropTypes.func,
    onSaveCallback: PropTypes.func,
    componentsOrder: PropTypes.array,
};

Sidebar.defaultProps = {
    componentsOrder: [
        'license',
        'sharing',
        'displayOptions',
        'language',
        'upgradeContent',
        'contentProperties',
        'adapterSelect',
    ],
    customComponents: [],
};

export default injectIntl(Sidebar);
