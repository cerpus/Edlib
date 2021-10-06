import React from 'react';

const H5P = React.forwardRef(({ visible }, ref) => (
    <div style={{ display: visible ? 'block' : 'none' }}>
        <div className="h5p-create">
            <div className="h5p-editor" ref={ref}>Loading...</div>
        </div>
    </div>
));

export default H5P;
