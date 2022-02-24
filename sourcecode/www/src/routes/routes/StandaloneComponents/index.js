import React from 'react';
import { Route, Switch } from 'react-router-dom';
import ContentExplorer from './routes/ContentExplorer';
import EditResourceFromLtiLink from './routes/EditResourceFromLtiLink';
import LtiBrowser from './routes/LtiBrowser';
import ViewResource from './routes/ViewResource';

const Index = ({ match }) => (
    <Switch>
        <Route
            exact
            path={`${match.path}/resources/:resourceId`}
            component={ViewResource}
        />
        <Route
            exact
            path={`${match.path}/lti/browser`}
            component={LtiBrowser}
        />
        <Route
            path={`${match.path}/content-explorer`}
            component={ContentExplorer}
        />
        <Route
            path={`${match.path}/edit-from-lti-link`}
            component={EditResourceFromLtiLink}
        />
    </Switch>
);

export default Index;
