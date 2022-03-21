import React from 'react';
import styled from 'styled-components';

const Element = styled.div`
    display: flex;
    max-width: 100%;
    margin-bottom: 5px;
`;

const Child = styled.span`
    max-width: 100%;
    word-wrap: break-word;
`;

const List = ({ numbered = false, children, ...props }) => {
    return (
        <div {...props}>
            {children.map((child, index) => (
                <Element key={index} numbered={numbered}>
                    <span style={{ marginRight: 10 }}>
                        {numbered ? (
                            `${index + 1}.`
                        ) : (
                            <img
                                width={10}
                                height={10}
                                style={{ marginTop: 8 }}
                                src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10'><circle cx='3' cy='5' r='3' fill='%23507aa4 ' /></svg>"
                                alt=""
                            />
                        )}{' '}
                    </span>
                    <Child>{child}</Child>
                </Element>
            ))}
        </div>
    );
};

export default List;
