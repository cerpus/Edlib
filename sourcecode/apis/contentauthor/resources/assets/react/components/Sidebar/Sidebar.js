import React, { useMemo, useState } from 'react';
import { Paper, ExpandableBox, ExpandableBoxList } from '@cerpus/ui';
import { AlertBox, SaveBox, Publish, Lock } from './components';
import SidebarCommonComponents from './SidebarCommonComponents';
import { useForm } from '../../contexts/FormContext';
import { injectIntl, intlShape } from 'react-intl';
import { PropTypes } from 'prop-types';
import { compare } from '../../utils/utils';

const Sidebar = ({ customSetup, customComponents, onSave, intl, onSaveCallback, componentsOrder }) => {
    // eslint-disable-next-line no-undef
    const setup = customSetup || editorSetup;
    const { dispatch, state } = useForm();
    const { locked, lockedProperties, pulseUrl } = setup;
    const [isLocked, setLocked] = useState(locked);

    const onChange = (type, payload) => dispatch({ type: type, payload });
    const toggleLock = () => setLocked(!isLocked);

    const components = useMemo(() => {
        let commonComponents = SidebarCommonComponents(setup, onChange, state, intl);
        commonComponents = commonComponents
            .concat(customComponents)
            .filter(box => typeof box !== 'undefined' && box !== null)
            .map(component => {
                component.order = componentsOrder.indexOf(component.id);
                return component;
            })
            .sort(compare('order'));
        return commonComponents;
    }, [state]);

    return (
        <Paper className={'sidebar'}>
            {!isLocked && (
                <>
                    <SaveBox
                        onSave={onSave}
                        onSaveCallback={onSaveCallback}
                        pulseUrl={pulseUrl}
                    />
                </>
            )}
            {isLocked && (
                <Lock
                    {...lockedProperties}
                    lockReleased={() => toggleLock()}
                />
            )}
            <AlertBox/>
            {setup.useDraft === true && (
                <Publish
                    label={intl.formatMessage({ id: 'SHARINGCOMPONENT.ISPUBLISHED' })}
                />
            )}
            {components.length > 0 && (
                <ExpandableBoxList className="expandableList">
                    {components.map((box, index) => {
                        const {
                            title,
                            component,
                            info,
                        } = box;
                        return (
                            <ExpandableBox
                                key={index}
                                title={title}
                                info={info}
                            >
                                {component || box}
                            </ExpandableBox>
                        );
                    })}
                </ExpandableBoxList>
            )}
        </Paper>
    );
};

Sidebar.propTypes = {
    intl: intlShape,
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
