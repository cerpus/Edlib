import React from 'react';
import cn from 'classnames';
import {
    Container,
    Col,
    Row,
    Alert,
    Spinner,
    Card,
    CardBody,
    CardHeader,
} from 'reactstrap';

import styles from './login.module.scss';

const LoginCallback = ({ loading, error }) => {
    return (
        <div className={cn('pt-5', styles.login)}>
            <Container>
                <Row className="justify-content-md-center">
                    <Col md={6}>
                        <Card>
                            <CardHeader>Logg inn</CardHeader>
                            <CardBody>
                                {loading && (
                                    <div className="d-flex justify-content-center align-content-center ml-3">
                                        <Spinner />
                                    </div>
                                )}
                                {error ? (
                                    <Alert color="danger" className="mt-3">
                                        {error.message}
                                    </Alert>
                                ) : (
                                    ''
                                )}
                            </CardBody>
                        </Card>
                    </Col>
                </Row>
            </Container>
        </div>
    );
};

export default LoginCallback;
