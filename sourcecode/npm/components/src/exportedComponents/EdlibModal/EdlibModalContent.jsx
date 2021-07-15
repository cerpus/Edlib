import React from 'react';
import Header from '../../components/Header';
import { Switch, Route } from 'react-router-dom';
import MyContent from './routes/MyContent';
import SharedContent from './routes/SharedContent';
import LinkAuthor from './routes/LinkAuthor';
import ResourceEditDone from './routes/ResourceEditDone';
import EditResource from './routes/EditResource';
import NewResource from './routes/NewResource';
import CssReset from '../../components/CSSReset';

const EdlibModalContent = ({ onClose, loading, height = '100%' }) => {
    return (
        <CssReset>
            {!loading && (
                <div
                    style={{
                        height: height,
                        maxHeight: '100%',
                        position: 'relative',
                        display: 'flex',
                        flexDirection: 'column',
                        lineHeight: '24px',
                        fontSize: 16,
                        backgroundColor: 'white',
                    }}
                >
                    <Header onClose={onClose} />
                    <div
                        style={{
                            flex: 1,
                            minHeight: 0,
                        }}
                    >
                        <Switch>
                            <Route
                                path="/resources/new/:type"
                                component={NewResource}
                            />
                            <Route
                                exact
                                path="/resources/:edlibId"
                                component={EditResource}
                            />
                            <Route
                                exact
                                path="/resources/:edlibId/edit-done"
                                component={ResourceEditDone}
                            />
                            <Route
                                exact
                                path="/resources/:edlibId/:translateToLanguage"
                                component={EditResource}
                            />
                            <Route path="/link-author" component={LinkAuthor} />
                            <Route path="/my-content" component={MyContent} />
                            <Route
                                path="/shared-content"
                                component={SharedContent}
                            />
                        </Switch>
                    </div>
                </div>
            )}
        </CssReset>
    );
};

export default EdlibModalContent;
