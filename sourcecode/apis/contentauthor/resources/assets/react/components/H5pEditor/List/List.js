import React, { useState } from 'react';
import getTextFields from './getTextFields';
import './list.scss';
import updateFromPath from './updateFromPath';
import getValue from './getValue';
import ListItem from './ListItem';
import { FormattedMessage } from 'react-intl';
import Checkbox from './Checkbox';

const shouldViewFieldBasedOnFilters = (job, filters) =>
    Object
        .entries(job.filterTypes)
        .filter(([key, value]) => value)
        .some(([key]) => filters[key]);

const List = ({ parameters, onUpdate, startupParameters, libraryCache }) => {
    const [toTranslate, setToTranslate] = useState(null);
    const [fieldsToView, setFieldsToView] = useState({
        content: true,
        header: false,
        title: false,
        alt: false,
        hover: false,
    });

    React.useEffect(() => {
        if (parameters) {
            getTextFields(parameters.parameters, parameters.library, libraryCache()).then(setToTranslate);
        }
    }, [parameters]);

    return (
        <div className="h5p-editor-list">
            <div className="info-text">
                <FormattedMessage id="H5P_EDITOR.LIST_VIEW.EXPLANATION" />
            </div>
            <div>
                <strong><FormattedMessage id="H5P_EDITOR.LIST_VIEW.VIEW_ALTERNATIVES" /></strong>
            </div>
            <div className="h5p-editor-list-filters">
                {[
                    {
                        stateKey: 'content',
                        labelId: 'H5P_EDITOR.LIST_VIEW.CONTENT_FIELDS',
                    },
                    {
                        stateKey: 'header',
                        labelId: 'H5P_EDITOR.LIST_VIEW.HEADER_FIELDS',
                    },
                    {
                        stateKey: 'title',
                        labelId: 'H5P_EDITOR.LIST_VIEW.TITLE_FIELDS',
                    },
                    {
                        stateKey: 'alt',
                        labelId: 'H5P_EDITOR.LIST_VIEW.ALT_TEXTS',
                    },
                    {
                        stateKey: 'hover',
                        labelId: 'H5P_EDITOR.LIST_VIEW.HOVER_TEXTS',
                    },
                ].map(({ stateKey, labelId }) => (
                    <Checkbox
                        key={stateKey}
                        checked={fieldsToView[stateKey]}
                        onToggle={() => setFieldsToView({
                            ...fieldsToView,
                            [stateKey]: !fieldsToView[stateKey],
                        })}
                        labelId={labelId}
                    />
                ))}
                <div>
                    <div
                        className="btn btn-success"
                        onClick={() => setFieldsToView(
                            Object
                                .keys(fieldsToView)
                                .reduce((fieldsToView, key) => {
                                    fieldsToView[key] = true;
                                    return fieldsToView;
                                }, {})
                        )}
                    ><FormattedMessage id="H5P_EDITOR.LIST_VIEW.VIEW_ALL" /></div>
                </div>
            </div>
            <div className="splitter" />
            {toTranslate &&
            toTranslate
                .filter((job) => shouldViewFieldBasedOnFilters(job, fieldsToView))
                .map((job, index, originalArray) => {
                    const isNextNewGroup = originalArray[index + 1] && originalArray[index + 1].group !== job.group;
                    const isPreviousSameGroup = index !== 0 && originalArray[index - 1] && originalArray[index - 1].group === job.group;

                    return (
                        <React.Fragment
                            key={JSON.stringify(job.path)}
                        >
                            <ListItem
                                value={getValue(parameters, job.path)}
                                startValue={getValue(startupParameters, job.path)}
                                onChange={value => onUpdate(updateFromPath(parameters, job.path, value))}
                                path={job.path}
                                type={job.type}
                                widget={job.widget}
                                shouldIndent={isPreviousSameGroup}
                                editorSemantics={job.editorSemantics}
                            />
                            {isNextNewGroup && <div className="splitter" />}
                        </React.Fragment>
                    );
                })}
        </div>
    );
};

export default List;
