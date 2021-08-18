import React from 'react';
import { Switch, Route } from 'react-router-dom';
import ContentExplorer from './ContentExplorer';
import EditResource from "./EditResource.jsx";

const Index = () => (
    <Switch>
        <Route exact path="/content-explorer" component={ContentExplorer} />
        <Route exact path="/resources/edit" component={EditResource} />
    </Switch>
);

export default Index;
