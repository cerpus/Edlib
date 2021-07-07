import React from 'react';
import Home from './Home.jsx';
import { useHistory } from 'react-router-dom';

const HomeContainer = ({ match }) => {
    const history = useHistory();

    return <Home onGoToDetails={(id) => history.push(`${match.path}/${id}`)} />;
};

export default HomeContainer;
