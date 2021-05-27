import React from 'react';
import { Container, Col, Row, Button } from 'reactstrap';
import authContext from '../contexts/auth.js';

const Login = () => {
    const { loginUrl } = React.useContext(authContext);
    return (
        <Container className="pt-3">
            <Row>
                <Col>
                    <h1>Login</h1>
                    <p>Du må logge inn for å bruke denne siden.</p>
                    <Button color="primary" tag="a" href={loginUrl}>
                        Logg inn
                    </Button>
                </Col>
            </Row>
        </Container>
    );
};

export default Login;
