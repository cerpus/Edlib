export const iso6391ToIETF = (iso639_1) => {
    switch (iso639_1) {
        case 'en':
            return 'en-gb';
        case 'nb':
            return 'nb-no';
    }
};
