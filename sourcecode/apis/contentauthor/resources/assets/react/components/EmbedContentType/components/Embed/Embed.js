import React from 'react';

const Embed = ({embed}) => {
    return (
        <div className="embedWrapper">
            <div dangerouslySetInnerHTML={{__html: embed.html}}/>
        </div>
    );
};

export default Embed;