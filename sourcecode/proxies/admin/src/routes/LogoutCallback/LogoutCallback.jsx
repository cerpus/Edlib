import React from 'react';
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

const LogoutCallback = ({ loading, error }) => {
    return (
        <div className="pt-5">
            <Container>
                <Row className="justify-content-md-center">
                    <Col md={6}>
                        <Card>
                            <CardHeader>Logg ut</CardHeader>
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

export default LogoutCallback;
