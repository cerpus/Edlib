import React from 'react';
import PropTypes from 'prop-types';
import { Paper } from '@cerpus/ui';
import { Container } from '@material-ui/core';
import cn from 'clsx';

const EditorContainer = ({
    tabs,
    sidebar,
    className,
    containerClassname = '',
    children,
}) => {
    return (
        <Container
            maxWidth="lg"
            className={cn('editorContainer', containerClassname)}
        >
            <Paper
                tabs={tabs}
                className={'editorMainPaper'}
                paperClassName={cn('editorPaper', className)}
            >
                <div className={'editorMainContainer'}>
                    {children}
                </div>
            </Paper>
            {sidebar}
        </Container>
    );
};

EditorContainer.propTypes = {
    tabs: PropTypes.array,
    sidebar: PropTypes.oneOfType([PropTypes.element, PropTypes.bool]),
    className: PropTypes.string,
    containerClassname: PropTypes.string,
    children: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
};

export default EditorContainer;
