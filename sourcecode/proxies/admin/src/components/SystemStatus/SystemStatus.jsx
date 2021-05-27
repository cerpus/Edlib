import React from 'react';
import { Card, CardHeader, CardBody, Collapse, Table } from 'reactstrap';
import cn from 'classnames';

const SystemStatus = ({ name, loading, error, data }) => {
    const [isExpanded, setIsExpanded] = React.useState(false);

    if (loading) {
        return (
            <Card>
                <CardHeader>{name}</CardHeader>
            </Card>
        );
    }

    if (error) {
        return (
            <Card>
                <CardHeader
                    className={cn(
                        'd-flex justify-content-between text-white bg-danger'
                    )}
                    onClick={() => setIsExpanded(!isExpanded)}
                >
                    <div>{name}</div>
                    <div>
                        <i className="fa fa-caret-down" />
                    </div>
                </CardHeader>
                <Collapse isOpen={isExpanded}>
                    <CardBody>
                        Could not get service status. This might be because the
                        service is not set up properly or that you have no
                        internet connection.
                    </CardBody>
                </Collapse>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader
                className={cn(
                    'd-flex justify-content-between text-white',
                    'bg-' + data.color
                )}
                onClick={() => setIsExpanded(!isExpanded)}
            >
                <div>{name}</div>
                <div>
                    <i className="fa fa-caret-down" />
                </div>
            </CardHeader>
            <Collapse isOpen={isExpanded}>
                <CardBody>
                    <Table>
                        <thead>
                            <tr>
                                <th width={35} />
                                <th>Subservice name</th>
                                <th>Status</th>
                                <th>Parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.systems.map((s, index) => (
                                <tr key={index}>
                                    <td className={'bg-' + s.color} />
                                    <td>{s.name}</td>
                                    <td>{s.statusMessage}</td>
                                    <td>
                                        {s.parameters &&
                                            Object.entries(s.parameters).map(
                                                ([key, value]) => (
                                                    <div>
                                                        <strong>{key}: </strong>
                                                        {value}
                                                    </div>
                                                )
                                            )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </Table>
                </CardBody>
            </Collapse>
        </Card>
    );
};

export default SystemStatus;
