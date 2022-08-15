import React from 'react';
import Frame from './Frame';

let count = 0;

export default ({
    frame = Frame,
    className,
    style,
    method,
    url,
    params,
    onPostMessage,
    allowFullscreen = false,
}) => {
    const ActualFrame = frame;
    const formRef = React.useRef();
    const formName = React.useMemo(() => `posting-frame-${count++}`, []);

    React.useEffect(() => {
        formRef.current.submit();
    }, [formRef, method, url, params]);

    return (
        <>
            <ActualFrame
                className={className}
                style={style}
                name={formName}
                onPostMessage={onPostMessage}
                {...(allowFullscreen ? {allowFullScreen: "allowFullScreen"} : {})}
            />
            <form method={method} action={url} ref={formRef} target={formName}>
                {(Array.isArray(params)
                    ? params
                    : Object.entries(params).reduce(
                          (inputs, [key, value]) => [
                              ...inputs,
                              ...(Array.isArray(value) ? value : [value]).map(
                                  (value) => ({
                                      name: key,
                                      value,
                                  })
                              ),
                          ],
                          []
                      )
                ).map((param, index) => {
                    return (
                        <input
                            key={index}
                            type="hidden"
                            name={param.name}
                            value={param.value}
                        />
                    );
                })}
            </form>
        </>
    );
};
