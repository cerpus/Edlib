import React from 'react';
import { Col, Container, Row } from 'reactstrap';
import SystemStatus from '../components/SystemStatus';

const statuses = [
    {
        name: 'EdLibApi - Auth',
        endpoint: '/auth/v1/system-status',
    },
    {
        name: 'EdLibApi - Lti',
        endpoint: '/lti/v1/system-status',
    },
    {
        name: 'EdLibApi - Resources',
        endpoint: '/resources/v1/system-status',
    },
    {
        name: 'EdLibApi - Recommendations',
        endpoint: '/recommendations/v1/system-status',
    },
    {
        name: 'DokuAPI',
        endpoint: '/dokus/dokuapi-system-status',
    },
];

const SystemStatuses = () => {
    return (
        <Container className="pt-3">
            <Row>
                <Col>
                    {statuses.map((statusInfo, index) => (
                        <SystemStatus
                            key={index}
                            name={statusInfo.name}
                            endpoint={statusInfo.endpoint}
                        />
                    ))}
                </Col>
            </Row>
        </Container>
    );
};

export default SystemStatuses;
