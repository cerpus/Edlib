import React from 'react';
import styled from 'styled-components';
import { ArrowDropDown, ArrowDropUp } from '@material-ui/icons';

const StyledCollapsable = styled.div`
    padding: 5px 0;

    .toggler {
        cursor: pointer;
        display: flex;
        justify-content: space-between;
    }
`;

const Title = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
`;

const StyledContent = styled.div``;
const Collapsable = ({ title, filterCount = 0, children }) => {
    const [expanded, setExpanded] = React.useState(false);

    let titleText = `${title}`;
    if (filterCount !== 0) {
        titleText += ` (${filterCount})`;
    }

    return (
        <StyledCollapsable>
            <div className="toggler" onClick={() => setExpanded(!expanded)}>
                <Title>
                    {expanded ? <strong>{titleText}</strong> : titleText}
                </Title>
                <div>{expanded ? <ArrowDropUp /> : <ArrowDropDown />}</div>
            </div>
            {expanded && <StyledContent>{children}</StyledContent>}
        </StyledCollapsable>
    );
};

export default Collapsable;
