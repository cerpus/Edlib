import React from 'react';
import styled from 'styled-components';
import { useResourceCapabilities } from '../../../contexts/ResourceCapabilities';
import { useHistory } from 'react-router-dom';
import { Button } from '@cerpus/ui';

const Content = styled.div`
    max-width: 800px;
    margin: 10px auto;
`;

const ResourceEditDone = ({ match }) => {
    const { onInsert } = useResourceCapabilities();
    const history = useHistory();

    return (
        <Content>
            <p>
                Du har nettopp oppdatert en ressurs. Du har nå 2 valg. Du kan
                enten velge å sette inn ressursen eller fortsette å se etter
                ressurser i EdLib.
            </p>
            <Button
                onClick={() => {
                    onInsert(match.params.edlibId);
                }}
            >
                Sett inn
            </Button>
            <Button
                outline
                style={{ marginLeft: 5 }}
                onClick={() => history.push('/my-content')}
            >
                Fortsett
            </Button>
        </Content>
    );
};

export default ResourceEditDone;
