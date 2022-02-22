import styled from 'styled-components';

export default styled.table`
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;

    & > * > tr {
        cursor: pointer;
        margin-bottom: 10px;

        &:hover {
            background-color: #ededed;
        }

        & > td {
            padding: 10px 10px;
            border-top: 1px solid #c0c0c0;
        }

        & > th {
            padding: 15px 10px 0 10px;
            text-align: left;
        }

        &:last-child > td {
            border-bottom: 1px solid #c0c0c0;
        }
    }
`;
