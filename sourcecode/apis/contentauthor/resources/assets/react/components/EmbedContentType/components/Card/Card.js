import React from 'react';

const Card = ({card}) => {
    return (
        <div className="embedCardWrapper">
            <div className="embedCard">
                {card.img && <img className="embedCardImage" src={card.img} alt={card.title}/> }
                <div className="embedCardContent">
                    <div className="embedCardTitle"><strong>{card.title}</strong></div>
                    <div className="embedCardDescription">{card.description}</div>
                    <div className="embedCardMeta">{card.provider.name} - {card.provider.url}</div>
                </div>
            </div>
        </div>
    );
};

export default Card;