import React from 'react';
import styled from 'styled-components';

const ImageCell = styled.td`
    width: 40px;

    img {
        max-width: 100%;
    }
`;

const ResourcePageRow = ({ resource, onClick }) => {
    return (
        <tr onClick={onClick}>
            <ImageCell>icon</ImageCell>
            <td>{resource.name}</td>
        </tr>
    );
};

export default ResourcePageRow;
