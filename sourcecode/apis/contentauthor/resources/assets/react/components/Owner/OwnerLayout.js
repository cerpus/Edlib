import React from 'react';

const OwnerLayout = ({ name, email, userName, userEmail }) => {
    return (
        <div className="ownerlayout-container">
            <div>
                <label>
                    {name}
                </label>
                : {userName}
            </div>
            <div>
                <label>
                    {email}
                </label>
                : {userEmail}
            </div>
        </div>
    )
};

export default OwnerLayout;
