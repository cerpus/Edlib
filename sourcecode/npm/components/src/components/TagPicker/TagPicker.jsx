import React from 'react';
import { SearchableDropdown } from '@cerpus/ui';
import styled from 'styled-components';
import useConfig from '../../hooks/useConfig';
import { Close } from '@material-ui/icons';
import useRequestWithToken from '../../hooks/useRequestWithToken';

const Tag = styled.div`
    display: inline-flex;
    padding: 4px 8px;
    background-color: ${(props) => props.theme.colors.primary};
    border-radius: 20px;
    color: white;
    margin-top: 5px;
    margin-right: 5px;

    > div:first-child {
        margin-right: 5px;
    }

    > div:nth-child(2) {
        cursor: pointer;

        > * {
            height: 20px;
        }
    }

    > div {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
`;

const TagPicker = ({ tags }) => {
    const { edlib } = useConfig();

    const request = useRequestWithToken();

    return (
        <div>
            <div>
                <SearchableDropdown
                    value={{
                        label: '',
                        value: '',
                    }}
                    onSetValue={(value) => {
                        tags.push(value);
                    }}
                    items={async (text) => {
                        let info = {
                            url: edlib(
                                `/resources/v1/filters/tags?count=10${
                                    text.length !== 0 ? `&q=${text}` : ''
                                }`
                            ),
                        };

                        const response = await request(info.url, 'GET');

                        return response.map((label) => ({
                            label: label.name,
                            value: label.id,
                        }));
                    }}
                />
            </div>
            <div>
                {tags.value.map((tag, index) => (
                    <Tag key={index}>
                        <div>{tag.label}</div>
                        <div>
                            <Close onClick={() => tags.removeIndex(index)} />
                        </div>
                    </Tag>
                ))}
            </div>
        </div>
    );
};

export default TagPicker;
